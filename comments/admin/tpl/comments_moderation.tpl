<div class="container-fluid">
	<div class="row mb-2">
		<div class="col-sm-6">
			<h1 class="m-0 text-dark" style="padding: 20px 0 0 0;">Модерация комментариев</h1>
		</div>
		<div class="col-sm-6">
			<ol class="breadcrumb float-sm-right">
				<li class="breadcrumb-item">
					<a href="admin.php">
						<i class="fa fa-home"></i>
					</a>
				</li>
				<li class="breadcrumb-item">
					<a href="admin.php?mod=extras">{l_extras}</a>
				</li>
				<li class="breadcrumb-item active" aria-current="page">Модерация комментариев</li>
			</ol>
		</div>
	</div>
</div>
<div class="container-fluid">
	<form method="post" action="{php_self}?plugin=comments&handler=moderation">
		<div class="card mb-5">
			<div class="table-responsive">
				<table class="table table-sm">
					<thead>
						<tr>
							<th nowrap>Автор</th>
							<th nowrap>Комментарий</th>
							<th nowrap>Новость</th>
							<th nowrap>Дата</th>
							<th nowrap>
								<input type="checkbox" name="master_box" title="Выбрать все" onclick="javascript:check_uncheck_all(this.form, 'comments')"/>
							</th>
						</tr>
					</thead>
					<tbody>
						{comments}
					</tbody>
					<tfoot>
						<tr>
							<th nowrap>Автор</th>
							<th nowrap>Комментарий</th>
							<th nowrap>Новость</th>
							<th nowrap>Дата</th>
							<th nowrap>
								<input type="checkbox" name="master_box" title="Выбрать все" onclick="javascript:check_uncheck_all(this.form, 'comments')"/>
							</th>
						</tr>
						[has_comments]
						<tr>
							<td colspan="5">
								<div class="d-flex flex-wrap gap-2 justify-content-center">
									<div class="col-md-auto">
										<div class="btn-group" role="group">
											<button type="submit" name="action" value="approve" class="btn btn-outline-success" onclick="return confirm('Одобрить выбранные комментарии?')">Одобрить</button>
											<button type="submit" name="action" value="delete" class="btn btn-outline-danger" onclick="return confirm('Удалить выбранные комментарии?')">Удалить</button>
										</div>
									</div>
								</div>
							</td>
						</tr>
						[/has_comments]
					</tfoot>
				</table>
			</div>
		</div>
	</form>
	[no_comments]
	<div class="alert alert-info">
		Нет комментариев на модерации
	</div>
	[/no_comments]
	<script>
		function check_uncheck_all(form, checkboxName) {
const checkboxes = form.elements['comments[]'];
const isChecked = event.target.checked;
if (checkboxes) {
if (checkboxes.length) {
for (let i = 0; i < checkboxes.length; i++) {
checkboxes[i].checked = isChecked;
}
} else {
checkboxes.checked = isChecked;
}
}
// Синхронизация двух master-чекбоксов
const masterBoxes = form.querySelectorAll('[name^="master_box"]');
masterBoxes.forEach(box => {
if (box !== event.target) {
box.checked = isChecked;
}
});
}
	</script>
</div>
