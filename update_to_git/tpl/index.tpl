<script src="{{ admin_url }}/plugins/update_to_git/tpl/script.js?v=0.05" charset="utf-8"></script>

<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark">Обновление ядра и плагинов</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="admin.php"><i class="fa fa-home" aria-hidden="true"></i></a></li>
                <li class="breadcrumb-item"><a href="admin.php?mod=extras">{{ lang['extras'] }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">Настройки</li>
            </ol>
        </div>
    </div>
</div>

<div class="card">
    <h5 class="card-header d-flex justify-content-between align-items-center" data-plugin-settings-header>
        <span>Проверка и применение обновлений</span>{{ documentation_button|raw }}
    </h5>
    <div class="card-body">
        <div class="alert alert-warning" role="alert">
            Сначала проверьте список изменений. Файлы будут изменены только после нажатия кнопки «Применить».
            Перед обновлением создайте резервную копию сайта и базы данных.
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h4 class="card-title">Ядро — <code>irbees2008/ngcms-core</code></h4>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <button type="button" data-source="core" data-token="{{ token }}" class="updater-action updater-check btn btn-primary">Проверить обновления</button>
                    <button type="button" data-source="core" data-token="{{ token }}" class="updater-action updater-apply btn btn-success" disabled>Применить обновления</button>
                </div>
                <div id="update-message-core" class="alert alert-info updater-message" role="status" aria-live="polite">
                    Нажмите «Проверить обновления», чтобы получить список изменений.
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover updater-table">
                        <thead>
                            <tr>
                                <th scope="col">Изменение</th>
                                <th scope="col">Файл в репозитории</th>
                                <th scope="col">Добавлено</th>
                                <th scope="col">Удалено</th>
                                <th scope="col">Результат</th>
                            </tr>
                        </thead>
                        <tbody id="updates-core"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h4 class="card-title">Плагины — <code>irbees2008/ngcms-plugins</code></h4>
                <p class="mb-0">Корень репозитория обновляется в <code>engine/plugins/</code>.</p>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <button type="button" data-source="plugins" data-token="{{ token }}" class="updater-action updater-check btn btn-primary">Проверить обновления</button>
                    <button type="button" data-source="plugins" data-token="{{ token }}" class="updater-action updater-apply btn btn-success" disabled>Применить обновления</button>
                </div>
                <div id="update-message-plugins" class="alert alert-info updater-message" role="status" aria-live="polite">
                    Нажмите «Проверить обновления», чтобы получить список изменений.
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover updater-table">
                        <thead>
                            <tr>
                                <th scope="col">Изменение</th>
                                <th scope="col">Файл в репозитории</th>
                                <th scope="col">Добавлено</th>
                                <th scope="col">Удалено</th>
                                <th scope="col">Результат</th>
                            </tr>
                        </thead>
                        <tbody id="updates-plugins"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
{{ documentation_modal|raw }}
