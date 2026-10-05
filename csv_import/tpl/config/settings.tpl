<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fa fa-cog mr-2"></i>{{ lang['csv_import:settings_title'] }}
                </h3>
            </div>
            <form method="post" action="admin.php?mod=extra-config&amp;plugin=csv_import&amp;action=settings_save">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card card-outline card-secondary">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fa fa-file-text-o mr-1"></i>{{ lang['csv_import:settings_csv'] }}</h3>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>{{ lang['csv_import:settings_delimiter'] }}</label>
                                        <select name="default_delimiter" class="form-control">
                                            {% for option in delimiterOptions %}
                                                <option value="{{ option.value|e }}"
                                                    {% if settings.default_delimiter == option.value %}selected{% endif %}>
                                                    {{ option.label }}
                                                </option>
                                            {% endfor %}
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>{{ lang['csv_import:settings_approve'] }}</label>
                                        <select name="default_approve" class="form-control">
                                            <option value="1" {% if settings.default_approve == '1' %}selected{% endif %}>
                                                {{ lang['csv_import:status_published'] }}
                                            </option>
                                            <option value="0" {% if settings.default_approve == '0' %}selected{% endif %}>
                                                {{ lang['csv_import:status_draft'] }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label>{{ lang['csv_import:settings_mainpage'] }}</label>
                                        <select name="default_mainpage" class="form-control">
                                            <option value="1" {% if settings.default_mainpage == '1' %}selected{% endif %}>
                                                {{ lang['csv_import:option_yes'] }}
                                            </option>
                                            <option value="0" {% if settings.default_mainpage == '0' %}selected{% endif %}>
                                                {{ lang['csv_import:option_no'] }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card card-outline card-warning">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fa fa-code mr-1"></i>{{ lang['csv_import:settings_yml'] }}</h3>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>{{ lang['csv_import:settings_yml_image_field'] }}</label>
                                        <select name="yml_image_xfield" class="form-control">
                                            <option value="">{{ lang['csv_import:settings_no_image_upload'] }}</option>
                                            {% for key, label in xfImageFields %}
                                                <option value="{{ key|e }}"
                                                    {% if settings.yml_image_xfield == key %}selected{% endif %}>
                                                    {{ label|e }}
                                                </option>
                                            {% endfor %}
                                        </select>
                                        <small class="text-muted">
                                            {{ lang['csv_import:settings_image_type_only'] }}
                                            <strong>images</strong>
                                        </small>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label>{{ lang['csv_import:settings_max_images'] }}</label>
                                        <select name="yml_max_images" class="form-control">
                                            {% for value in [1, 2, 3, 5, 10] %}
                                                <option value="{{ value }}"
                                                    {% if settings.yml_max_images == value %}selected{% endif %}>
                                                    {{ value }}
                                                </option>
                                            {% endfor %}
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save mr-1"></i>{{ lang['csv_import:save_settings'] }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
