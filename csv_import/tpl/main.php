<?php if (!defined('NGCMS')) die('HAL'); ?>
<div class="row">

    <?php /* ─── Загрузка файла ─── */ ?>
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fa fa-upload mr-2"></i>Загрузить файл</h3>
            </div>
            <div class="card-body">
                <form method="post" enctype="multipart/form-data"
                      action="admin.php?mod=extra-config&plugin=csv_import&action=upload">
                    <div class="input-group">
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="csvfile"
                                   name="csvfile" accept=".csv,.yml,.xml" required>
                            <label class="custom-file-label" for="csvfile">
                                Выберите файл CSV, YML или XML…
                            </label>
                        </div>
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-success">
                                <i class="fa fa-upload mr-1"></i>Загрузить
                            </button>
                        </div>
                    </div>
                    <small class="text-muted mt-1 d-block">
                        Поддерживаемые форматы: <strong>.csv</strong> (UTF-8 / Windows-1251),
                        <strong>.yml / .xml</strong> (Яндекс.Маркет)
                    </small>
                </form>
            </div>
        </div>
    </div>

    <?php /* ─── CSV файлы ─── */ ?>
    <div class="col-12">
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fa fa-file-text-o mr-2"></i>CSV файлы
                    <span class="badge badge-success ml-2"><?= count($fileList) ?></span>
                </h3>
            </div>
            <div class="card-body p-0">
                <?php if ($fileList): ?>
                    <table class="table table-striped table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Файл</th>
                                <th width="90">Размер</th>
                                <th width="130">Изменён</th>
                                <th width="320">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($fileList as $f): ?>
                                <tr>
                                    <td>
                                        <i class="fa fa-file-text-o text-success mr-1"></i>
                                        <strong><?= htmlspecialchars($f['name']) ?></strong>
                                    </td>
                                    <td class="text-muted"><?= htmlspecialchars($f['size']) ?></td>
                                    <td class="text-muted small"><?= htmlspecialchars($f['modified']) ?></td>
                                    <td>
                                        <form method="get"
                                              action="admin.php"
                                              class="d-inline-flex align-items-center flex-wrap" style="gap:4px">
                                            <input type="hidden" name="mod" value="extra-config">
                                            <input type="hidden" name="plugin" value="csv_import">
                                            <input type="hidden" name="action" value="preview">
                                            <input type="hidden" name="file"
                                                   value="<?= htmlspecialchars($f['name']) ?>">
                                            <select name="delimiter" class="form-control form-control-sm"
                                                    style="width:auto">
                                                <option value=";">; точка с запятой</option>
                                                <option value=",">, запятая</option>
                                                <option value="&#9;">Tab</option>
                                            </select>
                                            <label class="mb-0 small">
                                                <input type="checkbox" name="has_header" value="1">
                                                Заголовки
                                            </label>
                                            <button type="submit" class="btn btn-sm btn-primary">
                                                <i class="fa fa-table mr-1"></i>Маппинг
                                            </button>
                                        </form>
                                        &nbsp;
                                        <form method="post"
                                              action="admin.php?mod=extra-config&plugin=csv_import&action=delete_file"
                                              class="d-inline">
                                            <input type="hidden" name="file"
                                                   value="<?= htmlspecialchars($f['name']) ?>">
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Удалить файл <?= htmlspecialchars($f['name'], ENT_QUOTES) ?>?')">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="p-3 text-muted">
                        <i class="fa fa-info-circle mr-1"></i>CSV файлы не загружены
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php /* ─── YML / XML файлы ─── */ ?>
    <div class="col-12">
        <div class="card card-outline card-warning">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fa fa-code mr-2"></i>YML / XML файлы
                    <small class="ml-1 text-muted">(Яндекс.Маркет)</small>
                    <span class="badge badge-warning ml-2"><?= count($ymlFileList) ?></span>
                </h3>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($ymlFileList)): ?>
                    <table class="table table-striped table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Файл</th>
                                <th width="90">Размер</th>
                                <th width="130">Изменён</th>
                                <th width="200">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ymlFileList as $f): ?>
                                <tr>
                                    <td>
                                        <i class="fa fa-code text-warning mr-1"></i>
                                        <strong><?= htmlspecialchars($f['name']) ?></strong>
                                    </td>
                                    <td class="text-muted"><?= htmlspecialchars($f['size']) ?></td>
                                    <td class="text-muted small"><?= htmlspecialchars($f['modified']) ?></td>
                                    <td>
                                        <form method="get"
                                              action="admin.php"
                                              class="d-inline">
                                            <input type="hidden" name="mod" value="extra-config">
                                            <input type="hidden" name="plugin" value="csv_import">
                                            <input type="hidden" name="action" value="yml_preview">
                                            <input type="hidden" name="file"
                                                   value="<?= htmlspecialchars($f['name']) ?>">
                                            <button type="submit" class="btn btn-sm btn-warning">
                                                <i class="fa fa-cog mr-1"></i>Настроить импорт
                                            </button>
                                        </form>
                                        <form method="post"
                                              action="admin.php?mod=extra-config&plugin=csv_import&action=delete_file"
                                              class="d-inline ml-1">
                                            <input type="hidden" name="file"
                                                   value="<?= htmlspecialchars($f['name']) ?>">
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Удалить файл <?= htmlspecialchars($f['name'], ENT_QUOTES) ?>?')">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="p-3 text-muted">
                        <i class="fa fa-info-circle mr-1"></i>YML/XML файлы не загружены
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php /* ─── Справка ─── */ ?>
    <?php if (!empty($xfFields)): ?>
    <div class="col-12">
        <div class="card card-outline card-secondary collapsed-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fa fa-question-circle mr-2"></i>Доступные поля xfields
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fa fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body" style="display:none">
                <div class="row">
                    <?php foreach ($xfFields as $key => $label): ?>
                        <div class="col-md-4 mb-1">
                            <code>xf_<?= htmlspecialchars($key) ?></code>
                            <span class="text-muted small"> — <?= htmlspecialchars($label) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>
<script>
    // CustomFile label update
    document.getElementById('csvfile') && document.getElementById('csvfile').addEventListener('change', function(){
        var label = this.nextElementSibling;
        label.textContent = this.files.length ? this.files[0].name : 'Выберите файл…';
    });
</script>
