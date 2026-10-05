<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fa fa-table mr-2"></i>{{ lang['csv_import:csv_mapping_heading'] }}
                    <strong>{{ filename|e }}</strong>
                </h3>
                <div class="card-tools">
                    <a href="admin.php?mod=extra-config&amp;plugin=csv_import" class="btn btn-sm btn-secondary">
                        <i class="fa fa-arrow-left mr-1"></i>{{ lang['csv_import:back'] }}
                    </a>
                </div>
            </div>

            <form method="post" action="admin.php?mod=extra-config&amp;plugin=csv_import&amp;action=run">
                <input type="hidden" name="file" value="{{ filename|e }}">
                <input type="hidden" name="delimiter" value="{{ delimiter|e }}">
                <input type="hidden" name="has_header" value="{{ hasHeader ? 1 : 0 }}">

                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label>{{ lang['csv_import:default_category'] }}</label>
                                <select name="cat_id" class="form-control">
                                    {% for id, name in cats %}
                                        <option value="{{ id }}">{{ name|e }}</option>
                                    {% endfor %}
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-0">
                                <label>{{ lang['csv_import:status'] }}</label>
                                <select name="approve" class="form-control">
                                    <option value="1" {% if settings.default_approve == '1' %}selected{% endif %}>{{ lang['csv_import:status_published'] }}</option>
                                    <option value="0" {% if settings.default_approve == '0' %}selected{% endif %}>{{ lang['csv_import:status_draft'] }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-0">
                                <label>{{ lang['csv_import:homepage'] }}</label>
                                <select name="mainpage" class="form-control">
                                    <option value="1" {% if settings.default_mainpage == '1' %}selected{% endif %}>{{ lang['csv_import:option_yes'] }}</option>
                                    <option value="0" {% if settings.default_mainpage == '0' %}selected{% endif %}>{{ lang['csv_import:option_no'] }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       name="update_mode" value="1" id="update_mode">
                                <label class="form-check-label" for="update_mode">
                                    {{ lang['csv_import:update_existing'] }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th width="40">#</th>
                                    {% if hasHeader %}
                                        <th>{{ lang['csv_import:csv_column'] }}</th>
                                    {% endif %}
                                    {% for sample in columns[0].samples|default([]) %}
                                        <th>{{ lang['csv_import:example'] }} {{ loop.index }}</th>
                                    {% endfor %}
                                    <th>{{ lang['csv_import:target_field'] }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {% for column in columns %}
                                    <tr>
                                        <td class="text-center text-muted small">{{ column.index }}</td>
                                        {% if hasHeader %}
                                            <td><strong>{{ column.header|e }}</strong></td>
                                        {% endif %}
                                        {% for sample in column.samples %}
                                            <td class="text-muted small">{{ sample|e }}</td>
                                        {% endfor %}
                                        <td>
                                            <select name="map[{{ column.index }}]" class="form-control form-control-sm">
                                                {% for value, label in newsFields %}
                                                    <option value="{{ value|e }}"
                                                        {% if column.selected == value %}selected{% endif %}>
                                                        {{ label }}
                                                    </option>
                                                {% endfor %}
                                                {% if xfFields %}
                                                    <optgroup label="── xfields ──">
                                                        {% for key, label in xfFields %}
                                                            <option value="xf_{{ key|e }}"
                                                                {% if column.selected == key %}selected{% endif %}>
                                                                {{ label|e }}
                                                            </option>
                                                        {% endfor %}
                                                    </optgroup>
                                                {% endif %}
                                            </select>
                                        </td>
                                    </tr>
                                {% endfor %}
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-play mr-1"></i>{{ lang['csv_import:run_import'] }}
                    </button>
                    <a href="admin.php?mod=extra-config&amp;plugin=csv_import" class="btn btn-secondary ml-2">
                        {{ lang['csv_import:cancel'] }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
