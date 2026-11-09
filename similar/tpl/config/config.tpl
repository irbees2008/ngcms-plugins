<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark" style="padding: 20px 0 0 0;">{{ plugin_id }}</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item">
                    <a href="admin.php"><i class="fa fa-home"></i></a>
                </li>
                <li class="breadcrumb-item">
                    <a href="admin.php?mod=extras">{{ extras_label }}</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">{{ plugin_id }} - {{ action }}</li>
            </ol>
        </div>
    </div>
</div>

<div class="card plugin-settings-card">
    <h5 class="card-header d-flex justify-content-between align-items-center" data-plugin-settings-header>
        <span>{{ lang.config_title }}</span>{{ documentation_button|raw }}
    </h5>
    <div class="card-body plugin-settings-content">
        <p>{{ lang.config_description }}</p>

        <form method="post" action="admin.php?mod=extra-config&amp;plugin=similar">
            <input type="hidden" name="action" value="commit">
            <input type="hidden" name="token" value="{{ token }}">
            <input type="hidden" name="operation" value="save_settings">

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="similar-enabled">{{ lang.enabled }}</label>
                <div class="col-sm-9">
                    <select class="form-control" id="similar-enabled" name="similar_enabled">
                        <option value="1"{% if settings.similar_enabled %} selected{% endif %}>{{ lang.yes_label }}</option>
                        <option value="0"{% if not settings.similar_enabled %} selected{% endif %}>{{ lang.no_label }}</option>
                    </select>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="similar-count">{{ lang.count }}</label>
                <div class="col-sm-9">
                    <input class="form-control" style="max-width: 12rem;" id="similar-count" type="number" name="count" min="1" max="20" value="{{ settings.count }}" required>
                    <small class="form-text text-muted">{{ lang.count_help }}</small>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="similar-localsource">{{ lang.template_source }}</label>
                <div class="col-sm-9">
                    <select class="form-control" id="similar-localsource" name="localsource">
                        <option value="0"{% if not settings.localsource %} selected{% endif %}>{{ lang.site_template }}</option>
                        <option value="1"{% if settings.localsource %} selected{% endif %}>{{ lang.plugin_template }}</option>
                    </select>
                    <small class="form-text text-muted">{{ lang.template_source_help }}</small>
                </div>
            </div>

            <fieldset class="border rounded p-3 mb-4">
                <legend class="w-auto px-2">{{ lang.integration }}</legend>
                <div class="form-group">
                    <div class="form-check">
                        <input class="form-check-input" id="similar-pcall" type="checkbox" name="pcall" value="1"{% if settings.pcall %} checked{% endif %}>
                        <label class="form-check-label" for="similar-pcall">{{ lang.integration_enabled }}</label>
                    </div>
                </div>
                <div class="form-group row mb-0">
                    <label class="col-sm-3 col-form-label" for="similar-pcall-mode">{{ lang.integration_mode }}</label>
                    <div class="col-sm-9">
                        <select class="form-control" id="similar-pcall-mode" name="pcall_mode">
                            <option value="0"{% if settings.pcall_mode == 0 %} selected{% endif %}>{{ lang.mode_export }}</option>
                            <option value="1"{% if settings.pcall_mode == 1 %} selected{% endif %}>{{ lang.mode_short }}</option>
                            <option value="2"{% if settings.pcall_mode == 2 %} selected{% endif %}>{{ lang.mode_full }}</option>
                        </select>
                        <small class="form-text text-muted">{{ lang.integration_help }}</small>
                    </div>
                </div>
            </fieldset>

            <button type="submit" class="btn btn-primary">{{ lang.save }}</button>
        </form>

        <hr>
        <div class="mb-2">
            <form class="d-inline-block mr-2" method="post" action="admin.php?mod=extra-config&amp;plugin=similar">
                <input type="hidden" name="action" value="commit">
                <input type="hidden" name="token" value="{{ token }}">
                <input type="hidden" name="operation" value="rebuild">
                <button type="submit" class="btn btn-warning">{{ lang.rebuild }}</button>
            </form>
            <form class="d-inline-block" method="post" action="admin.php?mod=extra-config&amp;plugin=similar">
                <input type="hidden" name="action" value="commit">
                <input type="hidden" name="token" value="{{ token }}">
                <input type="hidden" name="operation" value="clear_cache">
                <button type="submit" class="btn btn-secondary">{{ lang.clear_cache }}</button>
            </form>
        </div>
        <small class="form-text text-muted">{{ lang.rebuild_help }}</small>
    </div>
</div>

{% if toast_json and toast_json != '[]' %}
<script>
(function () {
    var toasts = {{ toast_json|raw }};
    if (typeof window.showToast !== 'function') return;
    toasts.forEach(function (toast) {
        window.showToast(toast.message, {
            type: toast.type,
            title: '{{ lang.config_title|e('js') }}'
        });
    });
})();
</script>
{% endif %}

{{ documentation_modal|raw }}
