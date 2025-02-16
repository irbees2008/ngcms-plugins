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
var autokeysAjaxUpdate = function () { // Show loading indicator
ngShowLoading();

// Send AJAX request to generate keywords
$.post('/engine/rpc.php', {
json: 1,
methodName: 'plugin.autokeys.generate',
rndval: new Date().getTime(),
params: JSON.stringify(
{
title: $('#newsTitle').val(),
content: $('#ng_news_content_short').val() + ' ' + $('#ng_news_content_full').val()
}
)
}, function (data) { // Hide loading indicator
ngHideLoading();

// Parse JSON response
try {
var resTX = JSON.parse(data);
} catch (err) {
alert('Error parsing JSON output. Result: ' + data);
return;
}

// Handle errors
if (!resTX['status']) {
ngNotifySticker('Error [' + resTX['errorCode'] + ']: ' + resTX['errorText'], {
className: 'stickers-danger',
sticked: 'true',
closeBTN: true
});
return;
}

// Update keywords area and show button
$("#autokeysArea").html(resTX['data']);
$("#autokeysButton").show();
}, "text").fail(function () { // Handle HTTP errors
ngHideLoading();
ngNotifySticker('HTTP error during request', {
className: 'stickers-danger',
sticked: 'true',
closeBTN: true
});
});
};

// Hide the keyword button initially
$("#autokeysButton").hide();

// Copy generated keywords to the input field
var autokeysSetKeywords = function () { // Используем атрибут name для выбора элементов
$("textarea[name='keywords']").val($("#autokeysArea").html());

};</script>
