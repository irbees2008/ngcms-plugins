<div class="row">
    <div class="col-12">
        <div class="card card-warning card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fa fa-code mr-2"></i>{{ lang['csv_import:yml_import_heading'] }}
                    <strong>{{ filename|e }}</strong>
                    {% if data.shop_name %}
                        <small class="text-muted ml-2">
                            {{ lang['csv_import:shop'] }} {{ data.shop_name|e }}
                        </small>
                    {% endif %}
                    <span class="badge badge-info ml-2">
                        {{ data.offers|length }} {{ lang['csv_import:products'] }}
                    </span>
                </h3>
                <div class="card-tools">
                    <a href="admin.php?mod=extra-config&amp;plugin=csv_import" class="btn btn-sm btn-secondary">
                        <i class="fa fa-arrow-left mr-1"></i>{{ lang['csv_import:back'] }}
                    </a>
                </div>
            </div>

            <form method="post" action="admin.php?mod=extra-config&amp;plugin=csv_import&amp;action=yml_run">
                <input type="hidden" name="file" value="{{ filename|e }}">
                <div class="card-body">
                    {% if standardFields %}
                        <div class="card card-outline card-secondary mb-3">
                            <div class="card-header py-2">
                                <h3 class="card-title">
                                    <span class="badge badge-secondary mr-2">1</span>
                                    {{ lang['csv_import:yml_standard_mapping'] }}
                                </h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                        <i class="fa fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm table-striped table-hover mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="180">{{ lang['csv_import:yml_source_field'] }}</th>
                                            <th>{{ lang['csv_import:example'] }}</th>
                                            <th width="260">{{ lang['csv_import:ngcms_field'] }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {% for field in standardFields %}
                                            <tr>
                                                <td>
                                                    <code>{{ field.name|e }}</code>
                                                    <br><small class="text-muted">{{ field.label|e }}</small>
                                                </td>
                                                <td class="text-muted small">{{ field.example|e }}</td>
                                                <td>
                                                    <select name="field_map[{{ field.name|e }}]" class="form-control form-control-sm">
                                                        {% for value, label in newsTargets %}
                                                            <option value="{{ value|e }}"
                                                                {% if field.selected == value %}selected{% endif %}>
                                                                {{ label }}
                                                            </option>
                                                        {% endfor %}
                                                        {% if xfFields %}
                                                            <optgroup label="── xfields ──">
                                                                {% for key, label in xfFields %}
                                                                    <option value="xf_{{ key|e }}"
                                                                        {% if field.selected == 'xf_' ~ key %}selected{% endif %}>
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
                    {% endif %}

                    {% if paramRows %}
                        <div class="card card-outline card-secondary mb-3">
                            <div class="card-header py-2">
                                <h3 class="card-title">
                                    <span class="badge badge-secondary mr-2">2</span>
                                    {{ lang['csv_import:yml_param_mapping'] }}
                                </h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                        <i class="fa fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm table-striped table-hover mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>{{ lang['csv_import:param_name'] }}</th>
                                            <th>{{ lang['csv_import:example'] }}</th>
                                            <th width="260">{{ lang['csv_import:xfields_field'] }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {% for param in paramRows %}
                                            <tr>
                                                <td><code>param:{{ param.name|e }}</code></td>
                                                <td class="text-muted small">{{ param.example|e }}</td>
                                                <td>
                                                    <select name="field_map[param:{{ param.name|e }}]" class="form-control form-control-sm">
                                                        <option value="skip">{{ lang['csv_import:skip'] }}</option>
                                                        {% if xfFields %}
                                                            <optgroup label="── xfields ──">
                                                                {% for key, label in xfFields %}
                                                                    <option value="xf_{{ key|e }}"
                                                                        {% if param.selected == 'xf_' ~ key %}selected{% endif %}>
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
                    {% endif %}

                    <div class="card card-outline card-info mb-3">
                        <div class="card-header py-2">
                            <h3 class="card-title">
                                <span class="badge badge-info mr-2">3</span>{{ lang['csv_import:yml_images'] }}
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fa fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            {% if not hasPictures %}
                                <p class="text-muted mb-0">
                                    <i class="fa fa-info-circle mr-1"></i>{{ lang['csv_import:yml_no_pictures'] }}
                                </p>
                            {% else %}
                                <div class="form-check mb-3">
                                    <input type="checkbox" class="form-check-input"
                                           id="download_images" name="download_images" value="1" checked>
                                    <label class="form-check-label" for="download_images">
                                        {{ lang['csv_import:yml_download_images'] }}
                                    </label>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{ lang['csv_import:yml_image_xfield'] }} <small>(images)</small></label>
                                            <select name="image_xf_field" class="form-control">
                                                <option value="">{{ lang['csv_import:yml_not_selected'] }}</option>
                                                {% if imageXfOptions %}
                                                    {% for key, label in imageXfOptions %}
                                                        <option value="{{ key|e }}"
                                                            {% if settings.yml_image_xfield == key %}selected{% endif %}>
                                                            {{ label|e }}
                                                        </option>
                                                    {% endfor %}
                                                {% else %}
                                                    {% for key, label in xfFields %}
                                                        <option value="{{ key|e }}"
                                                            {% if settings.yml_image_xfield == key %}selected{% endif %}>
                                                            {{ label|e }}
                                                        </option>
                                                    {% endfor %}
                                                {% endif %}
                                            </select>
                                            {% if not imageXfOptions %}
                                                <small class="text-warning">
                                                    <i class="fa fa-exclamation-triangle"></i>
                                                    {{ lang['csv_import:yml_image_fields_missing'] }}
                                                </small>
                                            {% endif %}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>{{ lang['csv_import:yml_max_images_product'] }}</label>
                                            <input type="number" name="max_images" class="form-control"
                                                   value="{{ settings.yml_max_images|e }}" min="1" max="10">
                                        </div>
                                    </div>
                                </div>
                                <p class="text-muted small mb-0">{{ lang['csv_import:yml_image_storage_note'] }}</p>
                            {% endif %}
                        </div>
                    </div>

                    {% if categoryRows %}
                        <div class="card card-outline card-secondary mb-3">
                            <div class="card-header py-2">
                                <h3 class="card-title">
                                    <span class="badge badge-secondary mr-2">4</span>
                                    {{ lang['csv_import:yml_category_mapping'] }}
                                    <small class="text-muted ml-1">
                                        ({{ categoryRows|length }} {{ lang['csv_import:yml_categories_suffix'] }})
                                    </small>
                                </h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                        <i class="fa fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm table-striped table-hover mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>{{ lang['csv_import:yml_category'] }}</th>
                                            <th>{{ lang['csv_import:ngcms_category'] }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {% for category in categoryRows %}
                                            <tr>
                                                <td>
                                                    {{ category.name|e }}
                                                    <small class="text-muted ml-1">(id: {{ category.id|e }})</small>
                                                </td>
                                                <td>
                                                    <select name="cat_map[{{ category.id|e }}]" class="form-control form-control-sm">
                                                        {% for id, name in cats %}
                                                            <option value="{{ id }}"
                                                                {% if category.matched == id %}selected{% endif %}>
                                                                {{ name|e }}
                                                            </option>
                                                        {% endfor %}
                                                    </select>
                                                </td>
                                            </tr>
                                        {% endfor %}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    {% endif %}

                    <div class="card card-outline card-primary mb-0">
                        <div class="card-header py-2">
                            <h3 class="card-title">
                                <span class="badge badge-primary mr-2">5</span>
                                {{ lang['csv_import:yml_import_parameters'] }}
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{ lang['csv_import:default_category'] }}</label>
                                        <small class="text-muted d-block">{{ lang['csv_import:category_unmapped_hint'] }}</small>
                                        <select name="cat_id" class="form-control">
                                            {% for id, name in cats %}
                                                <option value="{{ id }}">{{ name|e }}</option>
                                            {% endfor %}
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>{{ lang['csv_import:status'] }}</label>
                                        <select name="approve" class="form-control">
                                            <option value="1" {% if settings.default_approve == '1' %}selected{% endif %}>{{ lang['csv_import:status_published'] }}</option>
                                            <option value="0" {% if settings.default_approve == '0' %}selected{% endif %}>{{ lang['csv_import:status_draft'] }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>{{ lang['csv_import:homepage'] }}</label>
                                        <select name="mainpage" class="form-control">
                                            <option value="1" {% if settings.default_mainpage == '1' %}selected{% endif %}>{{ lang['csv_import:option_yes'] }}</option>
                                            <option value="0" {% if settings.default_mainpage == '0' %}selected{% endif %}>{{ lang['csv_import:option_no'] }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4 d-flex align-items-center">
                                    <div class="form-check mt-3">
                                        <input class="form-check-input" type="checkbox"
                                               name="update_mode" value="1" id="yml_update_mode">
                                        <label class="form-check-label" for="yml_update_mode">
                                            {{ lang['csv_import:update_existing'] }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fa fa-play mr-2"></i>{{ lang['csv_import:yml_run_import'] }}
                    </button>
                    <a href="admin.php?mod=extra-config&amp;plugin=csv_import" class="btn btn-secondary ml-2">
                        {{ lang['csv_import:cancel'] }}
                    </a>
                    <span class="text-muted ml-3 small">
                        {{ lang['csv_import:products_to_process'] }} <strong>{{ data.offers|length }}</strong>
                    </span>
                </div>
            </form>
        </div>
    </div>
</div>
