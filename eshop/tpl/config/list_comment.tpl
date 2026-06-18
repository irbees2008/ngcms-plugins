<form action="{{ php_self }}?mod=extra-config&plugin=eshop&action=modify_comment" method="post" name="check_comment">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
					<tr class="contHead" align="left">
						<td width="5%">ID</td>
						<td width="10%">Дата</td>
						<td width="20%">Имя</td>
						<td width="30%">Комментарий</td>
						<td width="25">Страница</td>
						<td width="5%">Статус</td>
						<td width="5%"><input class="check" type="checkbox" name="master_box" onclick="javascript:check_uncheck_all(check_comment)"/></td>
					</tr>
                </thead>
                <tbody>
				{% for entry in entries %}
					<tr align="left">
						<td width="5%" class="contentEntry1">{{ entry.id }}</td>
						<td width="10%" class="contentEntry1">{{ entry.date|date('d.m.Y H:i') }}</td>
						<td width="20%" class="contentEntry1">{% if (entry.reg) %}<a href="{{ entry.profile_link }}">{{ entry.author }}</a>{% else %}{{ entry.author }}{% endif %}<br/>
							<small>{{ entry.mail }}</small>&nbsp;
						</td>
						<td width="30%" class="contentEntry1">{{ entry.commenttext }}</td>
						<td width="25%" class="contentEntry1"><a href="{{ entry.product_edit_link }}">{{ entry.title }}</a><br/>
							<small><a href="{{ home }}{{ entry.view_link }}" target="_blank">{{ home }}{{ entry.view_link }}</a></small>&nbsp;
						</td>
						<td width="5%" class="contentEntry1"><img src="{{ home }}/engine/skins/default/images/{% if (entry.status == 1) %}yes.png{% else %}no.png{% endif %}" alt=""></td>
						<td width="5%" class="contentEntry1"><input name="selected_comment[]" value="{{ entry.id }}" class="check" type="checkbox"/></td>
					</tr>
				{% else %}
					<tr align="left">
						<td colspan="8" class="contentEntry1">По вашему запросу ничего не найдено.</td>
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
							</select>

							<div class="input-group-append">
								<button type="submit" class="btn btn-outline-warning">Выполнить..</button>
							</div>
						</div>
					</div>
				</div>
			</div>
			
</form>
