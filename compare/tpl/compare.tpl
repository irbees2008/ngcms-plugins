{% if products|length > 0 %}
	<div class="compare-page">
		<h2>{{ lang['title'] }}</h2>
		<a href="{{ clear_link }}" class="btn btn-sm btn-outline-secondary">{{ lang['clear'] }}</a>

		<div class="table-responsive mt-3">
			<table class="table table-bordered compare-table">
				<thead>
					<tr>
						<th>{{ lang['feature'] }}</th>
						{% for p in products %}
							<th>
								<a href="{{ p.url }}">{{ p.title }}</a>
								<br>
								<a href="{{ remove_link }}?id={{ p.id }}" class="btn btn-sm btn-danger mt-1">{{ lang['remove'] }}</a>
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
		<p>{{ lang['empty'] }} {{ lang['empty_hint'] }}</p>
		<a href="/" class="btn btn-primary">{{ lang['home'] }}</a>
	</div>
{% endif %}
