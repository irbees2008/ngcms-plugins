<form action="{{ php_self }}?mod=extra-config&plugin=eshop&action=list_api" method="post">
<div class="table-responsive">
	<table class="table table-bordered table-striped">
		<thead class="thead-dark">
			<tr class="contHead" align="left">
				<td width="55%">Токен</td>
				<td width="45%">Действие</td>
			</tr>
            </thead>
            <tbody>
			{% for entry in entries %}
				<tr align="left">
					<td width="55%" class="contentEntry1"><a href="{{ entry.edit_link }}">{{ entry.token }}</a></td>
					<td width="45%" class="contentEntry1" style="text-align:center;vertical-align:middle"><a href="{{ entry.del_link }}"/><img src="{{ admin_url }}/plugins/eshop/tpl/img/delete.png"></a></td>
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
					<div class="col-lg-6 mb-2 mb-lg-0"></div>
					<div class="col-lg-6">
						<a href="{{ admin_url }}/admin.php?mod=extra-config&plugin=eshop&action=add_api" style="float:right;" class="btn btn-outline-success">Добавить токен</a>
					</div>
				</div>
			</div>
			
</form>