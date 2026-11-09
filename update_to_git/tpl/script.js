$(function() {
    var pendingUpdates = {
        core: null,
        plugins: null
    };
    var checking = {
        core: false,
        plugins: false
    };
    var applying = false;

    function parseResponse(response) {
        return typeof response === 'string' ? $.parseJSON(response) : response;
    }

    function sendRequest(methodName, params) {
        return $.ajax({
            url: 'rpc.php',
            method: 'POST',
            dataType: 'text',
            data: {
                json: 1,
                methodName: methodName,
                rndval: new Date().getTime(),
                params: JSON.stringify(params)
            }
        });
    }

    function refreshButtons() {
        $('.updater-check').each(function() {
            var source = $(this).attr('data-source');
            $(this).prop('disabled', applying || checking[source] === true);
        });
        $('.updater-apply').each(function() {
            var source = $(this).attr('data-source');
            $(this).prop('disabled', applying || checking[source] === true || !pendingUpdates[source]);
        });
    }

    function finishApply() {
        applying = false;
        refreshButtons();
    }

    function setMessage(source, message, type) {
        $('#update-message-' + source)
            .removeClass('alert-info alert-success alert-warning alert-danger')
            .addClass('alert-' + type)
            .text(message);
    }

    function responseMessage(result, fallback) {
        var message = result && (result.errorText || result.data);
        return typeof message === 'string' && message ? message : fallback;
    }

    function renderFiles(source, files) {
        var $body = $('#updates-' + source);
        var resultCells = [];

        files.forEach(function(file) {
            var $row = $('<tr>');
            var $result = $('<td>').text('Ожидает применения');

            $row.append($('<td>').text(file.status || ''));
            $row.append($('<td>').text(file.filename || ''));
            $row.append($('<td>').text(file.additions === undefined ? '' : file.additions));
            $row.append($('<td>').text(file.deletions === undefined ? '' : file.deletions));
            $row.append($result);
            $body.append($row);
            resultCells.push($result);
        });

        return resultCells;
    }

    function finishPluginUpdate(source, update, failed) {
        if (failed) {
            setMessage(source, 'Есть ошибки. Контрольная позиция не изменена; можно повторить применение списка.', 'warning');
            finishApply();
            return;
        }

        if (source !== 'plugins') {
            pendingUpdates[source] = null;
            setMessage(source, 'Обновление завершено.', 'success');
            finishApply();
            return;
        }

        sendRequest('plugin.updater.finish', {
            token: update.token,
            source: source,
            target_sha: update.headSha || ''
        }).done(function(response) {
            var result;
            try {
                result = parseResponse(response);
            } catch (error) {
                pendingUpdates[source] = null;
                setMessage(source, 'Файлы обновлены, но ответ о сохранении контрольной позиции не разобран. Проверьте обновления повторно.', 'danger');
                finishApply();
                return;
            }

            pendingUpdates[source] = null;
            if (result && result.status === 1) {
                setMessage(source, 'Обновление завершено.', 'success');
            } else {
                setMessage(source, 'Файлы обновлены, но контрольная позиция не подтверждена. Проверьте обновления повторно: ' + responseMessage(result, 'ошибка сохранения'), 'danger');
            }
            finishApply();
        }).fail(function() {
            pendingUpdates[source] = null;
            setMessage(source, 'Файлы обновлены, но контрольная позиция не подтверждена. Проверьте обновления повторно.', 'danger');
            finishApply();
        });
    }

    $(document).off('click', '.updater-check').on('click.updateToGit', '.updater-check', function(event) {
        event.preventDefault();
        var source = $(this).attr('data-source');
        var token = $(this).attr('data-token');
        if ((source !== 'core' && source !== 'plugins') || applying || checking[source]) {
            return;
        }

        pendingUpdates[source] = null;
        checking[source] = true;
        $('#updates-' + source).empty();
        setMessage(source, 'Проверяем репозиторий…', 'info');
        refreshButtons();

        sendRequest('plugin.updater.check', {
            token: token,
            source: source
        }).done(function(response) {
            var result;
            try {
                result = parseResponse(response);
            } catch (error) {
                setMessage(source, 'Не удалось прочитать ответ сервера.', 'danger');
                return;
            }

            if (!result || result.status !== 1) {
                setMessage(source, responseMessage(result, 'Не удалось проверить обновления.'), 'danger');
                return;
            }

            var data = result.data || {};
            if (data.baseline_initialized) {
                setMessage(source, 'Начальная версия репозитория плагинов сохранена. Файлы не изменялись; список обновлений появится при следующей проверке.', 'info');
                return;
            }

            var files = Array.isArray(data.files) ? data.files : [];
            if (!files.length) {
                setMessage(source, 'Обновлений нет.', 'info');
                return;
            }

            var resultCells = renderFiles(source, files);
            pendingUpdates[source] = {
                files: files,
                headSha: data.head_sha || '',
                resultCells: resultCells,
                token: token
            };
            setMessage(source, 'Найдено файлов: ' + files.length + '. Просмотрите список и нажмите «Применить обновления», чтобы продолжить.', 'success');
        }).fail(function() {
            setMessage(source, 'Ошибка HTTP при проверке репозитория.', 'danger');
        }).always(function() {
            checking[source] = false;
            refreshButtons();
        });
    });

    $(document).off('click', '.updater-apply').on('click.updateToGit', '.updater-apply', function(event) {
        event.preventDefault();
        var source = $(this).attr('data-source');
        if ((source !== 'core' && source !== 'plugins') || applying || checking[source]) {
            return;
        }
        var update = pendingUpdates[source];
        if (!update || !update.files.length) {
            return;
        }
        if (!window.confirm('Применить ' + update.files.length + ' изменений из списка для ' + (source === 'plugins' ? 'плагинов' : 'ядра') + '?')) {
            return;
        }

        applying = true;
        refreshButtons();
        setMessage(source, 'Применяем изменения…', 'info');

        var failed = 0;
        function applyNext(index) {
            if (index >= update.files.length) {
                finishPluginUpdate(source, update, failed);
                return;
            }

            var file = update.files[index];
            var $result = update.resultCells[index];
            $result.text('Применяется…');

            sendRequest('plugin.updater.execute', {
                token: update.token,
                source: source,
                url: file.raw_url || '',
                name: file.filename || '',
                previous_name: file.previous_filename || '',
                action: file.status || ''
            }).done(function(response) {
                var result;
                try {
                    result = parseResponse(response);
                } catch (error) {
                    failed++;
                    $result.text('Ошибка ответа сервера.');
                    applyNext(index + 1);
                    return;
                }

                if (result && result.status === 1) {
                    $result.text('Готово');
                } else if (result && result.status === 2) {
                    $result.text(responseMessage(result, 'Пропущено'));
                } else {
                    failed++;
                    $result.text(responseMessage(result, 'Не удалось применить файл.'));
                }
                applyNext(index + 1);
            }).fail(function() {
                failed++;
                $result.text('Ошибка HTTP.');
                applyNext(index + 1);
            });
        }

        applyNext(0);
    });
});
