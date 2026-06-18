{% if products|length > 0 %}
	<div class="compare-page">
		<h2>Сравнение товаров</h2>
		<a href="{{ clear_link }}" class="btn btn-sm btn-outline-secondary">Очистить список</a>

		<div class="table-responsive mt-3">
			<table class="table table-bordered compare-table">
				<thead>
					<tr>
						<th>Характеристика</th>
						{% for p in products %}
							<th>
								<a href="{{ p.url }}">{{ p.title }}</a>
								<br>
								<a href="{{ remove_link }}?id={{ p.id }}" class="btn btn-sm btn-danger mt-1">Удалить</a>
							</th>
						{% endfor %}
					</tr>
				</thead>
				<tbody>
					{% for key, label in xf_keys %}
						<tr>
							<td>
								<strong>{{ label }}</strong>
							</td>
							{% for p in products %}
								<td>{{ attribute(p, 'xfields_' ~ key)|default('—') }}</td>
							{% endfor %}
						</tr>
					{% endfor %}
				</tbody>
			</table>
		</div>
	</div>
{% else %}
	<div class="compare-empty">
		<p>Список сравнения пуст. Добавьте товары для сравнения.</p>
		<a href="/" class="btn btn-primary">На главную</a>
	</div>
{% endif %}
