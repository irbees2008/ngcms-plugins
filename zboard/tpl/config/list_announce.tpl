<form action="/engine/admin.php?mod=extra-config&plugin=zboard&action=modify" method="post" name="zboard">
	<div class="table-responsive">
		<table class="table table-bordered table-hover table-sm align-middle">
			<thead class="thead-light">
				<tr>
					<th scope="col">ID</th>
					<th scope="col">Дата</th>
					<th scope="col">Категория</th>
					<th scope="col">Заголовок</th>
					<th scope="col">Автор</th>
					<th scope="col">Период</th>
					<th scope="col">Активен?</th>
					<th scope="col" class="text-center" style="width:36px;">
						<input type="checkbox" name="master_box" title="Выбрать все" onclick="javascript:check_uncheck_all(zboard)" style="margin:0;"/>
					</th>
				</tr>
			</thead>
			<tbody>
				{entries}
			</tbody>
		</table>
	</div>
	<div class="row mb-3">
		<div class="col-md-6">
			<div class="form-inline">
				<label class="mr-2" for="subaction">Действие:</label>
				<select name="subaction" id="subaction" class="form-control mr-2">
					<option value="">-- Действие --</option>
					<option value="mass_approve">Активировать</option>
					<option value="mass_forbidden">Деактивировать</option>
					<option value="" disabled>===================</option>
					<option value="mass_delete">Удалить объявление</option>
				</select>
				<button type="submit" class="btn btn-primary ml-2">Выполнить</button>
			</div>
		</div>
		<div class="col-md-6 text-right">
			<nav aria-label="Навигация по страницам">
				<span class="pagination pagination-sm mb-0">{pagesss}</span>
			</nav>
		</div>
	</div>
</form>
