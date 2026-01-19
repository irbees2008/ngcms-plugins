<div class="table-responsive">
	<table class="table table-bordered table-hover table-sm align-middle">
		<thead class="thead-light">
			<tr>
				<th scope="col">#</th>
				<th scope="col">Заголовок</th>
				<th scope="col">Количество объявлений</th>
				<th scope="col">Действие</th>
			</tr>
		</thead>
		<tbody>
			{entries}
		</tbody>
	</table>
</div>
<div class="row mb-3">
	<div class="col text-right">
		<a href="{admin_url}/admin.php?mod=extra-config&plugin=zboard&action=send_cat" class="btn btn-success">Добавить категорию</a>
	</div>
</div>
