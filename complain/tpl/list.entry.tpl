<tr>
	<td>
		<input type="checkbox" name="inc_{{ id }}" value="1">
	</td>
	<td>{{ date }}<br><small class="text-muted">{{ time }}</small>
	</td>
	<td>{{ error|raw }}
		{{ ccount|raw }}</td>
	<td>
		<a href="{{ link }}" target="_blank">{{ title }}</a>
	</td>
	<td>
		{% if publisher_name %}
			{{ publisher_name }}<br><small class="text-muted">{{ publisher_ip }}</small>
		{% endif %}
	</td>
	<td>{{ owner_name|raw }}</td>
	<td>{{ status }}</td>
</tr>
