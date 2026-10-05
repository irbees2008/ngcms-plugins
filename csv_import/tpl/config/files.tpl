<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fa fa-upload mr-2"></i>{{ lang['csv_import:upload_title'] }}</h3>
            </div>
            <div class="card-body">
                <form method="post" enctype="multipart/form-data"
                      action="admin.php?mod=extra-config&amp;plugin=csv_import&amp;action=upload">
                    <div class="input-group">
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="csvfile"
                                   name="csvfile" accept=".csv,.yml,.xml" required>
                            <label class="custom-file-label" for="csvfile"
                                   data-default-label="{{ lang['csv_import:choose_file_short']|e('html_attr') }}">
                                {{ lang['csv_import:choose_file'] }}
                            </label>
                        </div>
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-success">
                                <i class="fa fa-upload mr-1"></i>{{ lang['csv_import:upload'] }}
                            </button>
                        </div>
                    </div>
                    <small class="text-muted mt-1 d-block">
                        {{ lang['csv_import:supported_formats'] }}
                        <strong>.csv</strong> (UTF-8 / Windows-1251),
                        <strong>.yml / .xml</strong> ({{ lang['csv_import:yandex_market'] }})
                    </small>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fa fa-file-text-o mr-2"></i>{{ lang['csv_import:csv_files'] }}
                    <span class="badge badge-success ml-2">{{ fileList|length }}</span>
                </h3>
            </div>
            <div class="card-body p-0">
                {% if fileList %}
                    <table class="table table-striped table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ lang['csv_import:file_name'] }}</th>
                                <th width="90">{{ lang['csv_import:file_size'] }}</th>
                                <th width="130">{{ lang['csv_import:file_modified'] }}</th>
                                <th width="320">{{ lang['csv_import:actions'] }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {% for file in fileList %}
                                <tr>
                                    <td>
                                        <i class="fa fa-file-text-o text-success mr-1"></i>
                                        <strong>{{ file.name|e }}</strong>
                                    </td>
                                    <td class="text-muted">{{ file.size|e }}</td>
                                    <td class="text-muted small">{{ file.modified|e }}</td>
                                    <td>
                                        <form method="get" action="admin.php"
                                              class="d-inline-flex align-items-center flex-wrap" style="gap:4px">
                                            <input type="hidden" name="mod" value="extra-config">
                                            <input type="hidden" name="plugin" value="csv_import">
                                            <input type="hidden" name="action" value="preview">
                                            <input type="hidden" name="file" value="{{ file.name|e }}">
                                            <select name="delimiter" class="form-control form-control-sm" style="width:auto">
                                                <option value=";" {% if settings.default_delimiter == ';' %}selected{% endif %}>{{ lang['csv_import:delimiter_semicolon'] }}</option>
                                                <option value="," {% if settings.default_delimiter == ',' %}selected{% endif %}>{{ lang['csv_import:delimiter_comma'] }}</option>
                                                <option value="&#9;" {% if settings.default_delimiter == "\t" %}selected{% endif %}>{{ lang['csv_import:delimiter_tab'] }}</option>
                                            </select>
                                            <label class="mb-0 small">
                                                <input type="checkbox" name="has_header" value="1">
                                                {{ lang['csv_import:has_header'] }}
                                            </label>
                                            <button type="submit" class="btn btn-sm btn-primary">
                                                <i class="fa fa-table mr-1"></i>{{ lang['csv_import:mapping'] }}
                                            </button>
                                        </form>
                                        &nbsp;
                                        <form method="post"
                                              action="admin.php?mod=extra-config&amp;plugin=csv_import&amp;action=delete_file"
                                              class="d-inline">
                                            <input type="hidden" name="file" value="{{ file.name|e }}">
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                    data-confirm="{{ lang['csv_import:confirm_delete_file']|e('html_attr') }}"
                                                    data-file="{{ file.name|e('html_attr') }}"
                                                    onclick="return confirm(this.dataset.confirm + ' ' + this.dataset.file + '?')">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            {% endfor %}
                        </tbody>
                    </table>
                {% else %}
                    <div class="p-3 text-muted">
                        <i class="fa fa-info-circle mr-1"></i>{{ lang['csv_import:empty_csv_files'] }}
                    </div>
                {% endif %}
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card card-outline card-warning">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fa fa-code mr-2"></i>{{ lang['csv_import:yml_xml_files'] }}
                    <small class="ml-1 text-muted">({{ lang['csv_import:yandex_market'] }})</small>
                    <span class="badge badge-warning ml-2">{{ ymlFileList|length }}</span>
                </h3>
            </div>
            <div class="card-body p-0">
                {% if ymlFileList %}
                    <table class="table table-striped table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ lang['csv_import:file_name'] }}</th>
                                <th width="90">{{ lang['csv_import:file_size'] }}</th>
                                <th width="130">{{ lang['csv_import:file_modified'] }}</th>
                                <th width="200">{{ lang['csv_import:actions'] }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {% for file in ymlFileList %}
                                <tr>
                                    <td>
                                        <i class="fa fa-code text-warning mr-1"></i>
                                        <strong>{{ file.name|e }}</strong>
                                    </td>
                                    <td class="text-muted">{{ file.size|e }}</td>
                                    <td class="text-muted small">{{ file.modified|e }}</td>
                                    <td>
                                        <form method="get" action="admin.php" class="d-inline">
                                            <input type="hidden" name="mod" value="extra-config">
                                            <input type="hidden" name="plugin" value="csv_import">
                                            <input type="hidden" name="action" value="yml_preview">
                                            <input type="hidden" name="file" value="{{ file.name|e }}">
                                            <button type="submit" class="btn btn-sm btn-warning">
                                                <i class="fa fa-cog mr-1"></i>{{ lang['csv_import:configure_import'] }}
                                            </button>
                                        </form>
                                        <form method="post"
                                              action="admin.php?mod=extra-config&amp;plugin=csv_import&amp;action=delete_file"
                                              class="d-inline ml-1">
                                            <input type="hidden" name="file" value="{{ file.name|e }}">
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                    data-confirm="{{ lang['csv_import:confirm_delete_file']|e('html_attr') }}"
                                                    data-file="{{ file.name|e('html_attr') }}"
                                                    onclick="return confirm(this.dataset.confirm + ' ' + this.dataset.file + '?')">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            {% endfor %}
                        </tbody>
                    </table>
                {% else %}
                    <div class="p-3 text-muted">
                        <i class="fa fa-info-circle mr-1"></i>{{ lang['csv_import:empty_yml_files'] }}
                    </div>
                {% endif %}
            </div>
        </div>
    </div>

    {% if xfFields %}
        <div class="col-12">
            <div class="card card-outline card-secondary collapsed-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa fa-question-circle mr-2"></i>{{ lang['csv_import:available_xfields'] }}
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fa fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body" style="display:none">
                    <div class="row">
                        {% for key, label in xfFields %}
                            <div class="col-md-4 mb-1">
                                <code>xf_{{ key|e }}</code>
                                <span class="text-muted small"> — {{ label|e }}</span>
                            </div>
                        {% endfor %}
                    </div>
                </div>
            </div>
        </div>
    {% endif %}
</div>
<script>
    var csvFileInput = document.getElementById('csvfile');
    if (csvFileInput) {
        csvFileInput.addEventListener('change', function () {
            var label = this.nextElementSibling;
            label.textContent = this.files.length
                ? this.files[0].name
                : label.getAttribute('data-default-label');
        });
    }
</script>
