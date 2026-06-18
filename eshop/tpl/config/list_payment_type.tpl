<form action="{{ php_self }}?mod=extra-config&plugin=eshop&action=list_payment_type" method="post" name="catz_bar">
<div class="table-responsive">
	<table class="table table-bordered table-striped">
		<thead class="thead-dark">
			<tr class="contHead" align="left">
				<td width="5%">ID</td>
				<td width="20%">Название</td>
				<td width="2%">Позиция</td>
				<td width="25%">Статус</td>
				<td width="6%">Действие</td>
			</tr>
            </thead>
            <tbody>
			{% for entry in entries %}
				<tr align="left">
					<td width="5%" class="contentEntry1">{{ entry.id }}</td>
					<td width="20%" class="contentEntry1"><a href="{{ entry.edit_link }}">{{ entry.name }}</a></td>
					<td width="2%" class="contentEntry1" style="text-align:center;vertical-align:middle">{{ entry.position }}</td>
					<td width="25%" class="contentEntry1" style="text-align:center;vertical-align:middle">
						<img src="{{ home }}/engine/skins/default/images/{% if (entry.active == 1) %}yes.png{% else %}no.png{% endif %}" alt="">
					</td>
					<td width="6%" class="contentEntry1" style="text-align:center;vertical-align:middle"><a href="{{ entry.del_link }}"/><img src="{{ admin_url }}/plugins/eshop/tpl/img/delete.png"></a></td>
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
						<a href="{{ admin_url }}/admin.php?mod=extra-config&plugin=eshop&action=add_payment_type" style="float:right;" class="btn btn-outline-success">Добавить способ оплаты</a>
					</div>
				</div>
			</div>

</form>
