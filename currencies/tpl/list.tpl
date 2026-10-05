<div class="currencies-admin">
	<h2>{{ lang['currencies:list_title'] }}</h2>
	<a href="{{ add_link }}" class="btn btn-success mb-2">+ {{ lang['currencies:add_currency'] }}</a>
	{% if can_update_rates %}
		<a href="{{ update_link }}" class="btn btn-info mb-2">{{ lang['currencies:update_rates'] }}</a>
	{% endif %}

	<table class="table table-bordered mt-3">
		<thead>
			<tr>
				<th>{{ lang['currencies:column_code'] }}</th>
				<th>{{ lang['currencies:column_name'] }}</th>
				<th>{{ lang['currencies:column_symbol'] }}</th>
				<th>{{ lang['currencies:column_rate'] }}</th>
				<th>{{ lang['currencies:column_base'] }}</th>
				<th>{{ lang['currencies:column_updated'] }}</th>
				<th>{{ lang['currencies:column_actions'] }}</th>
			</tr>
		</thead>
		<tbody>
			{% for c in currencies %}
				<tr {% if c.is_base %} class="table-success" {% endif %}>
					<td><strong>{{ c.code }}</strong></td>
					<td>{{ c.name }}</td>
					<td>{{ c.symbol }}</td>
					<td>{{ c.rate }}</td>
					<td>{% if c.is_base %}✓{% endif %}</td>
					<td>{% if c.updated_at %}{{ c.updated_at|date('d.m.Y H:i') }}{% endif %}</td>
					<td>
						<a href="?action=edit&id={{ c.id }}" class="btn btn-sm btn-primary">{{ lang['currencies:edit_short'] }}</a>
						{% if not c.is_base %}
							<a href="?action=delete&id={{ c.id }}" class="btn btn-sm btn-danger"
							   data-confirm="{{ lang['currencies:delete_confirm']|e('html_attr') }}"
							   onclick="return confirm(this.dataset.confirm)">{{ lang['currencies:delete_short'] }}</a>
						{% endif %}
					</td>
				</tr>
			{% else %}
				<tr><td colspan="7" class="text-muted">{{ lang['currencies:empty_list'] }}</td></tr>
			{% endfor %}
		</tbody>
	</table>
</div>
