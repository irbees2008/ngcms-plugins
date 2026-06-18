<div class="table-responsive">
	<table class="table table-bordered table-striped">
		<thead class="thead-dark">
			<tr class="contHead" align="left">
				<td width="5%">ID</td>
				<td width="80%">Название системы</td>
			</tr>
            </thead>
            <tbody>
			{% for entry in entries %}
			<tr align="left">
				<td width="5%" class="contentEntry1">{{ loop.index }}</td>
				<td width="80%" class="contentEntry1"><a href="{{ entry.edit_link }}">{{ entry.name }}</a></td>
			</tr>
		{% else %}
			<tr align="left">
				<td colspan="8" class="contentEntry1">По вашему запросу ничего не найдено.</td>
			</tr>
		{% endfor %}
		</tbody>
	</table>
</div>