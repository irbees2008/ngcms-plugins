<form
	action="{{ home }}/engine/admin.php?mod=extra-config&plugin=faq&action=modify" method="post" name="check_faq">
	<!-- List of news start here -->
	<div class="container mt-5">
		<table class="table table-bordered table-striped">
			<thead class="thead-dark">
				<tr>
					<th scope="col" style="width: 5%;">ID</th>
					<th scope="col" style="width: 35%;">Вопрос</th>
					<th scope="col" style="width: 35%;">Ответ</th>
					<th scope="col" style="width: 15%;">Активна?</th>
					<th scope="col" class="text-center align-middle">
						<div class="form-check">
							<input class="form-check-input" type="checkbox" name="master_box" title="Выбрать все" onclick="javascript:check_uncheck_all(check_faq)"/>
						</div>
					</th>
				</tr>
			</thead>
			<tbody>
				{% for entry in entries %}
					<tr>
						<td>
							<a href="?mod=extra-config&plugin=faq&action=edit_faq&id={{ entry.id }}" class="d-block">{{ entry.id }}</a>
						</td>
						<td>{{ entry.question }}</td>
						<td>{{ entry.answer }}</td>
						<td>
							{% if (entry.active == "1") %}Да{% else %}Нет
							{% endif %}
						</td>
						<td class="text-center ">
							<div class="form-check">
								<input name="selected_faq[]" value="{{ entry.id }}" class="form-check-input" type="checkbox"/>
							</div>
						</td>
					</tr>
				{% else %}
					<tr>
						<td colspan="5" class="text-center">Нет добавленных записей.</td>
					</tr>
				{% endfor %}
			</tbody>
		</table>

		<div class="row mt-3">
			<div class="col text-right">
				<div style="text-align: left;">
					Действие:
					<select name="subaction" class="form-control" style="font: 12px Verdana, Courier, Arial; width: 230px;">
						<option value="">-- Действие --</option>
						<option value="mass_approve">Активировать</option>
						<option value="mass_forbidden">Деактивировать</option>
						<option value="" style="background-color: #E0E0E0;" disabled="disabled">===================</option>
						<option value="mass_delete">Удалить</option>
					</select>
					<button type="submit" class="btn btn-primary mt-2">Выполнить..</button>
				</div>
			</div>
		</div>

	</form>
	<div class="row mt-3">
		<div class="col text-center">
			<p class="h5">{{ pagesss }}</p>
		</div>
	</div>
