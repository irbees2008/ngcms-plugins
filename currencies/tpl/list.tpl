<div class="currencies-admin">
	<h2>Валюты</h2>
	<a href="{{ add_link }}" class="btn btn-success mb-2">+ Добавить валюту</a>
	<a href="{{ update_link }}" class="btn btn-info mb-2">Обновить курсы (ЦБ РФ)</a>

	<table class="table table-bordered mt-3">
		<thead>
			<tr>
				<th>Код</th>
				<th>Название</th>
				<th>Символ</th>
				<th>Курс</th>
				<th>Базовая</th>
				<th>Обновлено</th>
				<th>Действия</th>
			</tr>
		</thead>
		<tbody>
			{% for c in currencies %}
				<tr {% if c.is_base %} class="table-success" {% endif %}>
					<td>
						<strong>{{ c.code }}</strong>
					</td>
					<td>{{ c.name }}</td>
					<td>{{ c.symbol }}</td>
					<td>{{ c.rate }}</td>
					<td>
						{% if c.is_base %}✓
						{% endif %}
					</td>
					<td>
						{% if c.updated_at %}
							{{ c.updated_at|date('d.m.Y H:i') }}
						{% endif %}
					</td>
					<td>
						<a href="?action=edit&id={{ c.id }}" class="btn btn-sm btn-primary">Изм.</a>
						{% if not c.is_base %}
							<a href="?action=delete&id={{ c.id }}" class="btn btn-sm btn-danger" onclick="return confirm('Удалить?')">Удал.</a>
						{% endif %}
					</td>
				</tr>
			{% endfor %}
		</tbody>
	</table>
</div>
