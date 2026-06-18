<form action="{{ php_self }}?mod=extra-config&plugin=eshop&action=modify_feature" method="post" name="check_feature">
   <div class="table-responsive">
       <table class="table table-bordered table-striped">
           <thead class="thead-dark">
				<tr class="contHead" align="left">
					<td width="5%">ID</td>
					<td width="20%">Название</td>
					<td width="10%">Тип поля</td>
					<td width="10%">Возможные значения</td>
					<td width="10%">По умолчанию</td>
					<td width="10%">В фильтре?</td>
					<td width="10%">Позиция</td>
					<td width="5%"><input class="check" type="checkbox" name="master_box" onclick="javascript:check_uncheck_all(check_feature)"/></td>
				</tr>
           </thead>
        <tbody>
        {% for entry in entries %}
            <tr align="left">
                <td width="5%" class="contentEntry1">{{ entry.id }}</td>
                <td width="20%" class="contentEntry1"><a href="{{ entry.edit_link }}">{{ entry.name }}</a></td>
                <td width="10%"
                    class="contentEntry1">{% if (entry.ftype == 0) %}Текстовое{% elseif (entry.ftype == 1) %}Флажок (checkbox){% elseif (entry.ftype == 2) %}Выбор значения{% endif %}</td>
                <td width="10%"
                    class="contentEntry1">{% if not (entry.foptions == '') %}{% for k,v in entry.foptions %} {{ k }} => {{ v }}
                        <br/> {% endfor %}{% endif %}</td>
                <td width="10%" class="contentEntry1">{% if (entry.fdefault == '') %}<font color="red">не
                        задано</font>{% else %}{{ entry.fdefault }}{% endif %}</td>
                <td width="10%" class="contentEntry1"><img
                            src="{{ home }}/engine/skins/default/images/{% if (entry.in_filter == 1) %}yes.png{% else %}no.png{% endif %}"
                            alt=""></td>
                <td width="10%" class="contentEntry1">{{ entry.position }}</td>
                <td width="5%" class="contentEntry1"><input name="selected_feature[]" value="{{ entry.id }}"
                                                            class="check" type="checkbox"/></td>
            </tr>
        {% else %}
            <tr align="left">
                <td colspan="8" class="contentEntry1">По вашему запросу ничего не найдено.</td>
            </tr>
        {% endfor %}
		</tbody>
        </table>
			
		<div class="card-footer">
			<div class="row">
				<div class="col-lg-6 mb-2 mb-lg-0"></div>

				<div class="col-lg-6">
				<div class="input-group">
					<select name="subaction" class="custom-select">
                        <option value="">-- Действие --</option>
                        <option value="mass_delete">Удалить</option>
                    </select>
					<div class="input-group-append">
						<input type="submit" value="Выполнить.." class="btn btn-outline-warning"/>
						<a href="{{ admin_url }}/admin.php?mod=extra-config&plugin=eshop&action=add_feature" class="btn btn-outline-success">Добавить продукт</a>
					</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</form>