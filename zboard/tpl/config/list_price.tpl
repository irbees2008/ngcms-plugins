<div class="table-responsive">
	<table class="table table-bordered table-hover table-sm align-middle">
		<thead class="thead-light">
			<tr>
				<th scope="col">#</th>
				<th scope="col">{l_zboard:admin_price_time}</th>
				<th scope="col">{l_zboard:admin_price}</th>
				<th scope="col">{l_zboard:admin_action}</th>
			</tr>
		</thead>
		<tbody>
			{entries}
		</tbody>
	</table>
</div>
<div class="row mb-3">
	<div class="col text-right">
		<a href="{admin_url}/admin.php?mod=extra-config&plugin=zboard&action=send_price" class="btn btn-success">{l_zboard:admin_add_price}</a>
	</div>
</div>
