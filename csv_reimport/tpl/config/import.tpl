<div class="row">
    <div class="col-12">
        {% if notice %}
            <div class="alert alert-{{ notice.type == 'error' ? 'danger' : 'success' }} alert-dismissible">
                {{ notice.text|e }}
                <button type="button" class="close" data-dismiss="alert" aria-label="{{ lang['csv_reimport:close']|e('html_attr') }}">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        {% endif %}

        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fa fa-upload mr-2"></i>{{ lang['csv_reimport:upload_title'] }}</h3>
            </div>
            <div class="card-body">
                <form method="post" enctype="multipart/form-data"
                      action="admin.php?mod=extra-config&amp;plugin=csv_reimport&amp;action=upload">
                    <div class="input-group">
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="csvfile"
                                   name="csvfile" accept=".csv,text/csv" required>
                            <label class="custom-file-label" for="csvfile">{{ lang['csv_reimport:choose_file'] }}</label>
                        </div>
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-upload mr-1"></i>{{ lang['csv_reimport:upload'] }}
                            </button>
                        </div>
                    </div>
                    <small class="form-text text-muted">{{ lang['csv_reimport:upload_hint'] }}</small>
                </form>
            </div>
        </div>

        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title"><i class="fa fa-info-circle mr-2"></i>{{ lang['csv_reimport:format_title'] }}</h3>
            </div>
            <div class="card-body">
                <p>{{ lang['csv_reimport:format_description'] }}</p>
                <ol class="mb-0">
                    <li><code>2</code> — {{ lang['csv_reimport:column_title'] }}</li>
                    <li><code>3</code> — <code>gosreg</code></li>
                    <li><code>4</code> — <code>refusalfire</code></li>
                    <li><code>5</code> — <code>refusal</code></li>
                    <li><code>6</code> — <code>conformity</code></li>
                    <li><code>7</code> — <code>voluntaryfire</code></li>
                    <li><code>8</code> — <code>roomab</code></li>
                </ol>
                <small class="text-muted">{{ lang['csv_reimport:format_note'] }}</small>
            </div>
        </div>

        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fa fa-file-text-o mr-2"></i>{{ lang['csv_reimport:files_title'] }}
                    <span class="badge badge-success ml-2">{{ files|length }}</span>
                </h3>
            </div>
            <div class="card-body p-0">
                {% if files %}
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>{{ lang['csv_reimport:file_name'] }}</th>
                                    <th>{{ lang['csv_reimport:file_size'] }}</th>
                                    <th>{{ lang['csv_reimport:file_modified'] }}</th>
                                    <th>{{ lang['csv_reimport:actions'] }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {% for file in files %}
                                    <tr>
                                        <td><code>{{ file.name|e }}</code></td>
                                        <td>{{ file.size|e }} KB</td>
                                        <td>{{ file.modified|e }}</td>
                                        <td>
                                            <form method="post"
                                                  action="admin.php?mod=extra-config&amp;plugin=csv_reimport&amp;action=run">
                                                <input type="hidden" name="file" value="{{ file.name|e('html_attr') }}">
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    <i class="fa fa-refresh mr-1"></i>{{ lang['csv_reimport:run_import'] }}
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                {% endfor %}
                            </tbody>
                        </table>
                    </div>
                {% else %}
                    <div class="p-3 text-muted">
                        <i class="fa fa-info-circle mr-1"></i>{{ lang['csv_reimport:empty_files'] }}
                    </div>
                {% endif %}
            </div>
        </div>
    </div>
</div>
