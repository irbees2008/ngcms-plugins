<form method="post" action="" enctype="multipart/form-data">
	<fieldset class="admGroup">
		<legend class="title">Основная информация</legend>
		<div class="table-responsive">
			<table class="table table-bordered">
				<tbody>
					<tr>
						<th scope="row" class="align-middle">Название кампании</th>
						<td><input name="title" type="text" class="form-control" placeholder="Необязательно"/></td>
					</tr>
					<tr>
						<th scope="row" class="align-middle">Тема письма
							<span class="text-danger">*</span>
						</th>
						<td><input name="subject" type="text" class="form-control" required/></td>
					</tr>
					<tr>
						<th scope="row" class="align-middle">
							HTML контент
							<br><small>Можно использовать {UNSUB_URL} для отписки</small>
						</th>
						<td>
							<textarea name="body_html" class="form-control" rows="12" placeholder="HTML письма"></textarea>
						</td>
					</tr>
					<tr>
						<th scope="row" class="align-middle">
							Text версия
							<br><small>Опционально, для почтовых клиентов без HTML</small>
						</th>
						<td>
							<textarea name="body_text" class="form-control" rows="6" placeholder="Текстовая версия"></textarea>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</fieldset>

	<fieldset class="admGroup">
		<legend class="title">Получатели (сегмент)</legend>
		<div class="table-responsive">
			<table class="table table-bordered">
				<tbody>
					<tr>
						<th scope="row" class="align-middle">
							ID групп пользователей
							<br><small>Через запятую, например: 1,2,5</small>
						</th>
						<td><input name="groups_csv" type="text" class="form-control" placeholder="1,2"/></td>
					</tr>
					<tr>
						<th scope="row" class="align-middle">Только активные пользователи</th>
						<td>
							<label><input type="checkbox" name="only_active" checked>
								Да</label>
						</td>
					</tr>
					<tr>
						<th scope="row" class="align-middle">
							Лимит получателей
							<br><small>0 = без лимита</small>
						</th>
						<td><input name="limit" type="text" class="form-control" value="0"/></td>
					</tr>
				</tbody>
			</table>
		</div>
	</fieldset>

	<fieldset class="admGroup">
		<legend class="title">Параметры отправки</legend>
		<div class="table-responsive">
			<table class="table table-bordered">
				<tbody>
					<tr>
						<th scope="row" class="align-middle">
							Отложенная отправка
							<br><small>UNIX timestamp, 0 = отправить сейчас</small>
						</th>
						<td><input name="send_at_ts" type="text" class="form-control" value="0"/></td>
					</tr>
					<tr>
						<th scope="row" class="align-middle">Вложения</th>
						<td><input name="attachments[]" type="file" class="form-control" multiple/></td>
					</tr>
				</tbody>
			</table>
		</div>
	</fieldset>

	<input type="hidden" name="created_by" value="0">

	<div class="card-footer text-center">
		<button class="btn btn-success" type="submit" name="create_campaign" value="1">Создать и поставить в очередь</button>
	</div>
</form>

 <script>
document.querySelector("form").addEventListener("submit", function(e){
	var csv = document.querySelector("[name=groups_csv]").value || "";
	var arr = csv.split(",").map(s=>parseInt(s.trim(),10)).filter(n=>!isNaN(n));
	arr.forEach(function(n){
		var i = document.createElement("input");
		i.type="hidden"; i.name="groups[]"; i.value=String(n);
		e.target.appendChild(i);
	});
});
</script>
