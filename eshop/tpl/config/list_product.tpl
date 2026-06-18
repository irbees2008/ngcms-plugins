<script type="text/javascript" src="{{ admin_url }}/plugins/eshop/tpl/js/eshop.js"></script>

<div class="card mb-4">
	<div class="card-body">
		<form action="{{ php_self }}" method="post" name="options_bar">

			<div class="row">
				<!--Block 1-->
				<div class="col-lg-4">
					<div class="form-group">
						<label>Название</label>
						<div class="input-group mb-3">
							<input name="fname" id="fname" class="form-control" type="text" value="{{ fname }}"size="53"/>
						</div>
					</div>

					<div class="form-group">
						<label>На странице</label>
						<input name="rpp" value="{{ rpp }}" type="text" class="form-control" size="3"/>
					</div>
				</div>

				<!--Block 2-->
				<div class="col-lg-4">
					<div class="form-group">
						<label>Категория</label>
						<div class="input-group mb-3">
                            <select name="fcategory" class="form-control">
                                <option value="0">Выберите категорию</option>
                                {{ filter_cats }}
                            </select>
						</div>
					</div>

					<div class="form-group">
						<label>Статус</label>
                            <select name="fstatus" class="form-control">
                                <option value="-1" {% if fstatus  == '-1' %}selected{% endif %}>- Все -</option>
                                <option value="0" {% if fstatus == '0' %}selected{% endif %}>Не акивен</option>
                                <option value="1" {% if fstatus == '1' %}selected{% endif %}>Активен</option>
                            </select>
					</div>
				</div>

				<!--Block 3-->
				<div class="col-lg-4">
					<div class="form-group">
						<label>ID</label>
						<input name="fid" id="fid" class="form-control" type="text" value="{{ fid }}" size="40"/>
					</div>

					<div class="form-group">
						<label>Код</label>
						<input name="fcode" id="fcode" class="form-control" type="text" value="{{ fcode }}"size="40"/>
					</div>
				</div>
				
				<div class="form-group">
					<button type="submit" class="btn btn-block btn-outline-primary">Показать</button>
				</div>
			</div>
		</form>
	</div>
</div>
	
<!-- Конец блока фильтрации -->

<form action="/engine/admin.php?mod=extra-config&plugin=eshop&action=modify_product" method="post" name="check_product">

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
					<tr class="contHead" align="left">
						<td width="5%">ID</td>
						<td width="15%">Изображение</td>
						<td width="30%">Название</td>
						<td width="15%">Категория</td>
						<td width="10%">Вариант</td>
						<td width="10%">Цена</td>
						<td width="10%">Старая цена</td>
						<td width="10%">Количество</td>
						<td width="10%">Статус</td>
						<td width="5%"><input class="check" type="checkbox" name="master_box" title="Выбрать все" onclick="javascript:check_uncheck_all(check_product)"/></td>
					</tr>
                </thead>
                <tbody>
				{% for entry in entries %}
				<tr align="left">
					<td width="5%" class="contentEntry1">{{ entry.id }}</td>
					<td width="15%" class="contentEntry1">
						<a href="{{ entry.edit_link }}">
							<img src="{% if (entry.images[0].filepath) %}{{ home }}/uploads/eshop/products/{{ entry.id }}/thumb/{{ entry.images[0].filepath }}{% else %}{{ home }}/engine/plugins/eshop/tpl/img/img_none.jpg{% endif %}" width="100" height="100">
						</a>
					</td>
					<td width="30%" class="contentEntry1">
						<div style="float: left; margin: 0px;">
							<a href="{{ entry.edit_link }}">{{ entry.name }}</a><br/>
							<small>
								<a href="{{ home }}{{ entry.view_link }}" target="_blank">{{ home }}{{ entry.view_link }}</a>
							</small>&nbsp;
						</div>
					</td>
					<td width="15%" class="contentEntry1">{{ entry.category }}</td>
					<td width="10%" class="contentEntry1">{% for variant in entry.variants %}{{ variant.name }}&nbsp;<br/><br/>{% endfor %}</td>
					<td width="5%" class="contentEntry1">
						{% for variant in entry.variants %}
							<input size="3" type="text" autocomplete="off"class="price_input"value="{{ variant.price }}"data-id="{{ variant.id }}"> &nbsp;{{ system_flags.eshop.currency[0].sign }} &nbsp;
						<br/><br/>
						{% endfor %}
					</td>
					<td width="5%" class="contentEntry1">
						{% for variant in entry.variants %}
							<input size="3" type="text" autocomplete="off" class="compare_price_input" value="{{ variant.compare_price }}" data-id="{{ variant.id }}">
							 &nbsp;{{ system_flags.eshop.currency[0].sign }} &nbsp;
							<br/><br/>
						{% endfor %}
					</td>
					<td width="5%" class="contentEntry1">
					{% for variant in entry.variants %}
						<input size="3" type="text"  autocomplete="off" class="compare_amount_input" value="{{ variant.amount }}" data-id="{{ variant.id }}">
						&nbsp;
						<br/><br/>
					{% endfor %}
					</td>
					<td width="10%" class="contentEntry1">
						<img src="{{ home }}/engine/skins/default/images/{% if (entry.active == 1) %}yes.png{% else %}no.png{% endif %}" alt="">
					</td>
					<td width="5%" class="contentEntry1">
						<input name="selected_product[]" value="{{ entry.id }}" class="check" type="checkbox"/>
					</td>
				</tr>
				{% else %}
				<tr align="left">
					<td colspan="10" class="contentEntry1">По вашему запросу ничего не найдено.</td>
				</tr>
				{% endfor %}
				</tbody>
            </table>

        </div>
	
			<div class="card-footer">
				<div class="row">
					<div class="col-lg-6 mb-2 mb-lg-0">{{ pagesss }}</div>

					<div class="col-lg-6">
						<div class="input-group">
						<!-- Действие: -->
							<select name="subaction" class="custom-select">
								<option value="">-- Действие --</option>
								<option value="mass_delete">Удалить</option>
								<option value="" style="background-color: #E0E0E0;" disabled="disabled">===================</option>
								<option value="mass_active_add">Опубликовать</option>
								<option value="mass_active_remove">Запретить публикацию</option>
								<option value="" style="background-color: #E0E0E0;" disabled="disabled">===================</option>
								<option value="mass_featured_add">Добавить в рекомендованные</option>
								<option value="mass_featured_remove">Убрать из рекомендованных</option>
								<option value="" style="background-color: #E0E0E0;" disabled="disabled">===================</option>
								<option value="mass_stocked_add">Добавить в акционные</option>
								<option value="mass_stocked_remove">Убрать из акционных</option>
							</select>

							<div class="input-group-append">
								<button type="submit" class="btn btn-outline-warning">Выполнить..</button>
								<a href="{{ admin_url }}/admin.php?mod=extra-config&plugin=eshop&action=add_product" class="btn btn-outline-success">Добавить продукт</a>
							</div>
						</div>
					</div>
				</div>
			</div>
</form>

<script>
    $(document).ready(function () {
        $(document).on('change', '.price_input', function (e) {
            var id = $(this).attr("data-id");
            var mode = "price";
            var value = $(this).val();
            rpcEshopRequest('eshop_change_variant', {'id': id, 'mode': mode, 'value': value}, function (resTX) {
                eshop_indication('success', 'Товар сохранен');
            });
        });

        $(document).on('change', '.compare_price_input', function (e) {
            var id = $(this).attr("data-id");
            var mode = "compare_price";
            var value = $(this).val();
            rpcEshopRequest('eshop_change_variant', {'id': id, 'mode': mode, 'value': value}, function (resTX) {
                eshop_indication('success', 'Товар сохранен');
            });
        });

        $(document).on('change', '.compare_amount_input', function (e) {
            var id = $(this).attr("data-id");
            var mode = "amount";
            var value = $(this).val();
            rpcEshopRequest('eshop_change_variant', {'id': id, 'mode': mode, 'value': value}, function (resTX) {
                eshop_indication('success', 'Товар сохранен');
            });
        });

    });

</script>