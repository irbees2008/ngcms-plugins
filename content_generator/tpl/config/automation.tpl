<style>
	.ui-progressbar {
		position: relative;
	}
	.progress-label {
		position: absolute;
		left: 50%;
		top: 0;
		font-weight: bold;
		text-shadow: 1px 1px 0 #fff;
	}
	/* Стиль для сообщений */
	.message {
		margin-top: 10px;
		padding: 10px;
		border-radius: 4px;
		display: none; /* Сообщение скрыто по умолчанию */
	}
	.message.success {
		background-color: #d4edda;
		color: #155724;
		border: 1px solid #c3e6cb;
	}
	.message.error {
		background-color: #f8d7da;
		color: #721c24;
		border: 1px solid #f5c6cb;
	}
</style>
<div class="row mt-2">
	<div class="col-12 mb-4">
		<form method="post" class="card p-3" style="border:1px solid #ced4da;">
			<h5>Настройки генератора</h5>
			<div class="form-row" style="display:flex;gap:20px;flex-wrap:wrap;">
				<div style="min-width:200px;">
					<label>Количество новостей</label>
					<input type="number" class="form-control" name="news_count" min="1" max="100000" value="{{ news_count }}">
				</div>
				<div style="min-width:200px;">
					<label>Количество статических страниц</label>
					<input type="number" class="form-control" name="static_count" min="1" max="100000" value="{{ static_count }}">
				</div>
				<div style="min-width:200px;">
					<label>Максимум за один запуск (лимит)</label>
					<input type="number" class="form-control" name="max_allowed" min="1" max="100000" value="{{ max_allowed }}">
				</div>
			</div>
			<input type="hidden" name="save" value="1">
			<div class="mt-3">
				<button type="submit" class="btn btn-success">Сохранить настройки</button>
				<small class="text-muted ml-3">После сохранения используйте кнопки запуска ниже.</small>
			</div>
		</form>
	</div>
	<div class="col-sm">
		<form action="" method="post" name="generate_news">
			<input type="hidden" name="actionName" value="generate_news">
			<div class="card">
				<div class="card-header">Новости</div>
				<div class="card-body">
					<div class="list mb-2">
						Будет создано (новостей):
						<strong>{{ news_count }}</strong>
					</div>
					<div class="list text-muted" style="font-size:12px;">Лимит за запуск:
						{{ max_allowed }}</div>
					<div class="list">
						<div class="progressbar">
							<div class="progress-label"></div>
						</div>
					</div>
					<!-- Добавляем блок для сообщений -->
					<div class="message"></div>
				</div>
				<div class="card-footer">
					<input type="submit" name="submit" value="Начать!" class="btn btn-outline-primary">
				</div>
			</div>
		</form>
	</div>
	<div class="col-sm">
		<form action="" method="post" name="generate_static">
			<input type="hidden" name="actionName" value="generate_static">
			<div class="card">
				<div class="card-header">Статьи</div>
				<div class="card-body">
					<div class="list mb-2">
						Будет создано (статических):
						<strong>{{ static_count }}</strong>
					</div>
					<div class="list text-muted" style="font-size:12px;">Лимит за запуск:
						{{ max_allowed }}</div>
					<div class="list">
						<div class="progressbar">
							<div class="progress-label"></div>
						</div>
					</div>
					<!-- Добавляем блок для сообщений -->
					<div class="message"></div>
				</div>
				<div class="card-footer">
					<input type="submit" name="submit" value="Начать!" class="btn btn-outline-primary">
				</div>
			</div>
		</form>
	</div>
</div>
<!-- Подключение jQuery и jQuery UI -->
<script src="{{ home }}/lib/jq/jquery.min.js"></script>
<script src="{{ home }}/lib/jqueryui/core/jquery-ui.min.js"></script>
<link rel="stylesheet" href="{{ home }}/lib/jqueryui/core/jquery-ui.min.css">
<script>
	$(document).ready(function () {
let progressbar,
progressLabel,
button,
message;
$('form').on('submit', function (event) {
event.preventDefault();
const form = $(this);
const actionName = form.find('input[name="actionName"]').val();
// Читаем количество из отображаемого текста
let countText = '';
if (actionName === 'generate_news') {
countText = '{{ news_count }}';
} else if (actionName === 'generate_static') {
countText = '{{ static_count }}';
}
const count = parseInt(countText, 10) || 0;
if (count < 1) {
alert('Некорректное число в конфиге');
return;
}
message = form.find('.message');
message.hide().removeClass('success error').text('');
startAjaxProcess(form, actionName, count);
});
function startAjaxProcess(form, actionName, count) {
progressbar = form.find(".progressbar");
progressLabel = form.find(".progress-label");
button = form.find('input[type="submit"]');
button.hide();
progressbar.show().progressbar({
value: false,
complete: function () {
progressLabel.text("Готово!");
}
});
$.ajax({
method: "POST",
cache: false,
url: '/plugin/content_generator/',
data: {
actionName
},
success: function (response) {
progressbar.progressbar("value", 100);
},
error: function (xhr, status, error) {
console.error("Ошибка AJAX:", error);
showMessage('error', `Произошла ошибка: ${error}`);
},
complete: function () {
finishProcess();
showMessage('success', 'Генерация завершена!');
}
});
}
function finishProcess() {
button.show();
progressbar.hide();
}
// Функция для отображения сообщений
function showMessage(type, text) { // Используем только блок .message внутри текущей формы
message.text(text).addClass(type).fadeIn();
setTimeout(() => {
message.fadeOut();
}, 5000); // Сообщение исчезает через 5 секунд
}
});
</script>
