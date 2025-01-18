<!-- List of news start here -->
<table class="content table table-striped">
<thead>
	<tr>
		<td width="5%">ID</td>
		<td width="15%">Data</td>
		<td width="5%">IP</td>
		<td width="10%">Plugin</td>
		<td width="10%">Item</td>
		<td width="5%">DS</td>
		<td width="15%">Action</td>
		<!-- <td width="15%">Alist</td> -->
		<td width="10%">User</td>
		<td width="5%">Status</td>
		<td width="20%">Text</td>
	</tr>
</thead>
<tbody>

	{% for entry in entries %}
		<tr >
			<td >{{ entry.id }}</td>
			<td >{{ entry.date }}</td>
			<td >{{ entry.ip }}</td>
			<td >{{ entry.plugin }}</td>
			<td >{{ entry.item }}</td>
			<td >{{ entry.ds }}</td>
			<td >{{ entry.action }}</td>
			<!--<td width="15%">{{ entry.alist }}</td>  -->
			<td >
				<a href="admin.php?mod=users&action=editForm&id={{ entry.userid }}"/>{{ entry.username }}</a></td>
			<td >{{ entry.status }}</td>
			<td >{{ entry.stext }}</td>
		</tr>
	{% else %}
		<tr >
<td colspan="10">По вашему запросу ничего не найдено.</td>

		</tr>
	{% endfor %}
	
	<tr>
<td colspan="10">{{ pagesss }}</td>

	</tr>
</tbody>

</table>