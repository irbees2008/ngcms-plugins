<div class="form-row mb-3">
	<label class="col-lg-3 col-form-label">Автоматическая генерация ключевых слов</label>
	<div class="col-lg-9">
		<div class="form-check mb-3">
			<input type="checkbox" id="autokeys_generate" name="autokeys_generate" value="1" {% if (flags.checked) %} checked="checked" {% endif %} class="form-check-input"/>
			<label class="form-check-label" for="autokeys_generate">
				Генерировать keywords?
			</label>
		</div>

		<div id="autokeysArea" style="border: #EEEEEE 1px solid; height: 30px; text-align: center; color: darkgoldenrod;" onclick="autokeysAjaxUpdate(); return false;" class="mb-3">
			.. сгенерировать сейчас..
		</div>

		<div class="d-flex justify-content-center">
			<button type="button" id="autokeysButton" class="btn btn-outline-dark" onclick="autokeysSetKeywords(); return false;">
				Перенести
			</button>
		</div>
	</div>
</div>

<script language="javascript">
	// Функция для очистки текста от HTML-тегов и лишних пробелов
var cleanText = function (text) {
if (! text || typeof text !== 'string') {
return ''; // Возвращаем пустую строку, если текст пустой или некорректный
}
return text.replace(/<[^>]*>/g, '').trim(); // Удаляем HTML-теги и лишние пробелы
};

// AJAX-функция для генерации ключевых слов
var autokeysAjaxUpdate = function () { // Показываем индикатор загрузки
ngShowLoading();

// Очищаем и собираем данные для отправки
var title = cleanText($('#newsTitle').length ? $('#newsTitle').val() : '');
var contentShort = cleanText($('#ng_news_content_short').length ? $('#ng_news_content_short').val() : '');
var contentFull = cleanText($('#ng_news_content_full').length ? $('#ng_news_content_full').val() : '');
var content = cleanText(contentShort + ' ' + contentFull + ' ' + (
$('#ng_news_content').length ? $('#ng_news_content').val() : ''
));

// Отправляем AJAX-запрос
$.post('/engine/rpc.php', {
json: 1,
methodName: 'plugin.autokeys.generate',
rndval: new Date().getTime(),
params: JSON.stringify(
{title: title, content: content}
)
}, function (data) { // Скрываем индикатор загрузки
ngHideLoading();

// Парсим ответ сервера
try {
var resTX = JSON.parse(data);
} catch (err) {
alert('Ошибка при обработке JSON: ' + data);
return;
}

// Логируем ответ сервера для отладки
console.log('Ответ сервера:', resTX);

// Обрабатываем ошибки
if (!resTX['status']) {
ngNotifySticker('Ошибка [' + resTX['errorCode'] + ']: ' + resTX['errorText'], {
className: 'stickers-danger',
sticked: 'true',
closeBTN: true
});
return;
}

// Проверяем данные на наличие undefined
if (resTX['data'] && resTX['data'].trim() !== '' && !resTX['data'].includes('undefined')) {
$("#autokeysArea").html(resTX['data']);
$("#autokeysButton").show();
} else {
$("#autokeysArea").html('Ошибка при генерации ключевых слов.');
$("#autokeysButton").hide();
}
}, "text").fail(function () { // Обработка HTTP-ошибок
ngHideLoading();
ngNotifySticker('HTTP ошибка при запросе', {
className: 'stickers-danger',
sticked: 'true',
closeBTN: true
});
});
};

// Скрываем кнопку "Перенести" по умолчанию
$("#autokeysButton").hide();

// Копирование сгенерированных ключевых слов в поле keywords
var autokeysSetKeywords = function () {
var keywords = $("#autokeysArea").html();

// Проверяем данные на наличие undefined
if (keywords && ! keywords.includes('undefined')) {
$("textarea[name='keywords']").val(keywords);
} else {
alert('Ошибка: Некорректные ключевые слова.');
}
};
</script>
