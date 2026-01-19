<script src="/engine/plugins/zboard/upload/js/jquery-1.7.2.min.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="/engine/plugins/zboard/upload/uploadifive/uploadifive.css">
<link rel="stylesheet" href="/engine/plugins/zboard/tpl/config/capty/jquery.capty.css" type="text/css"/>
<script src="/engine/plugins/zboard/upload/uploadifive/jquery.uploadifive.min.js" type="text/javascript"></script>
<script type="text/javascript" src="/engine/plugins/zboard/tpl/config/capty/jquery.capty.min.js"></script>
{% if (error) %}
	<div class="feed-me">
		{{error}}
	</div>
{% endif %}
<script language="javascript" type="text/javascript">
	var currentInputAreaID = 'content_description';
</script>
<div class="comment">
	<h3>
		<span>Добавить объявление</span>
	</h3>
	<form method="post" action="" class="comment-form" name="form" enctype="multipart/form-data">
		<input type="hidden" name="submit" value="1"/>
		<ul class="comment-author">
			<li class="item clearfix">
				<input type="text" class="form-control" name="announce_name" value="{{announce_name}}" tabindex="1">
				<label>Заголовок объявления
					<i>(*)</i>
				</label>
			</li>
			<li class="item clearfix">
				<input type="text" class="form-control" name="author" value="{{author}}" tabindex="1">
				<label>Автор
					<i>(*)</i>
				</label>
			</li>
			{% if not(global.flags.isLogged) %}
				<li class="item clearfix">
					<input type="text" class="form-control" name="author_email" value="{{author_email}}" tabindex="1">
					<label>Email
						<i>(*)</i>
					</label>
				</li>
			{% endif %}
			<li class="item clearfix">
				<select name="announce_period">
					{{list_period}}
				</select>
				<label>Период объявления
					<i>(*)</i>
				</label>
			</li>
			<li class="item clearfix">
				<select name="cat_id">
					{{options}}
				</select>
				<label>Категория
					<i>(*)</i>
				</label>
			</li>
		</ul>
		<span class="textarea">
			<label>Описание объявления
				<i>(*)</i>
			</label><br/><br/>
			<textarea type="text" id="content_description" name="announce_description" tabindex="4">{{announce_description}}</textarea>
		</span>
		<span class="textarea">
			<label>Контакты
				<i>(телефон)</i>
			</label>
			<input type="tel" class="form-control" id="announce_contacts" name="announce_contacts" value="{{announce_contacts}}" placeholder="+7 (___) ___-__-__"/>
		</span><br/><br/>
		<ul class="comment-author">
			<li class="item clearfix">
				<script type="text/javascript">
					$(document).ready(function () {
var queuedCount = 0;
var completedCount = 0;
var form = document.querySelector('form.comment-form');
if ($.fn && typeof $.fn.uploadifive === 'function') {
$('#file_upload').uploadifive({
auto: false,
fileObjName: 'Filedata',
formData: {
id: '{{ id }}'
},
queueID: 'queue',
uploadScript: '/engine/plugins/zboard/upload/libs/subirarchivo.php',
onUpload: function (filesToUpload) {},
onUploadComplete: function (file, data) {
completedCount++;
// Добавляем превью, если сервер вернул имя файла
try {
var json = {};
try {
json = JSON.parse(data);
} catch (e) {}
if (json && json.filepath) {
var ul = document.getElementById('zboard-preview-list');
if (ul) {
var li = document.createElement('li');
li.setAttribute('data-pid', json.pid || '');
li.setAttribute('data-filepath', json.filepath);
li.style.position = 'relative';
li.innerHTML = '<img src="/uploads/zboard/thumb/' + json.filepath.replace(/"/g, '') + '" alt="" />' + '<button type="button" class="zb-del" title="Удалить">×</button>';
ul.appendChild(li);
}
}
} catch (e) {}
// ничего не сабмитим, пользователь сам решает когда сохранять
},
onQueueComplete: function (uploads) {}
});
}
// Кнопка сохранения: просто отправляет форму
var submitBtn = document.getElementById('zboard-submit-btn');
if (submitBtn) {
submitBtn.addEventListener('click', function (e) {
e.preventDefault();
if (form) {
HTMLFormElement.prototype.submit.call(form);
}
});
}
// Кнопка загрузки: запускает очередь загрузки без отправки формы
var uploadBtn = document.getElementById('zboard-upload-btn');
if (uploadBtn) {
uploadBtn.addEventListener('click', function (e) {
e.preventDefault();
if ($.fn && typeof $.fn.uploadifive === 'function') {
queuedCount = document.querySelectorAll('#queue .uploadifive-queue-item').length;
completedCount = 0;
if (queuedCount > 0) {
$('#file_upload').uploadifive('upload');
}
}
});
}
$('.fix').capty({cWrapper: 'capty-tile', height: 36, opacity: .6});
// Простейшая маска для телефона
var ac = document.getElementById('announce_contacts');
if (ac) {
ac.addEventListener('input', function () {
var v = ac.value;
// Разрешаем только + и цифры, форматируем к виду +X (XXX) XXX-XX-XX
v = v.replace(/[^\d\+]/g, '');
if (v[0] !== '+')
v = '+' + v.replace(/\+/g, '');
var d = v.replace(/\D/g, '');
var res = '+';
if (d.length > 0)
res += d.substring(0, 1);
if (d.length > 1)
res += ' (' + d.substring(1, 4);
if (d.length >= 4)
res += ') ';
if (d.length > 4)
res += d.substring(4, 7);
if (d.length >= 7)
res += '-';
if (d.length > 7)
res += d.substring(7, 9);
if (d.length >= 9)
res += '-';
if (d.length > 9)
res += d.substring(9, 11);
ac.value = res;
});
}
});
				</script>
				<label>Прикрепить изображения</label><br/><br/>
				<input type="hidden" id="txtdes" name="txtdes" value="{{id}}"/>
				<div id="queue"></div>
				<input id="file_upload" name="file_upload" type="file" multiple="true">
				<div id="zboard-preview" style="margin-top:10px;">
					<ul id="zboard-preview-list" style="list-style:none; padding:0; display:flex; gap:8px; flex-wrap:wrap;">
						{% if entriesImg %}
							{% for entry in entriesImg %}
								<li data-pid="{{entry.pid}}" data-filepath="{{entry.filepath}}" style="position:relative;">
									<img src="{{entry.home}}/uploads/zboard/thumb/{{entry.filepath}}" alt=""/>
									<button type="button" class="zb-del" title="Удалить">×</button>
								</li>
							{% endfor %}
						{% endif %}
					</ul>
				</div>
				<script>
					(function () {
var list = document.getElementById('zboard-preview-list');
if (! list)
return;
list.addEventListener('click', function (e) {
if (! e.target.classList.contains('zb-del'))
return;
var li = e.target.closest('li');
var pid = li.getAttribute('data-pid');
var filepath = li.getAttribute('data-filepath');
var id = '{{ id }}';
fetch('/engine/plugins/zboard/upload/libs/delete_image.php?id=' + encodeURIComponent(id) + (pid ? '&pid=' + encodeURIComponent(pid) : '&filepath=' + encodeURIComponent(filepath))).then(function (r) {
return r.text();
}).then(function (txt) {
if (txt.indexOf('OK') === 0) {
li.remove();
} else {
alert('Не удалось удалить: ' + txt);
}
}).catch(function (err) {
alert('Ошибка запроса: ' + err);
});
});
})();
				</script>
			</li>
			{% if (use_recaptcha) %}
				<li class="item clearfix">
					<label>Капча
						<i>(*)</i>
					</label><br/><br/>
					{{captcha}}
				</li>
			{% endif %}
		</ul>
		<div class="submit" style="display:flex; gap:10px;">
			<button id="zboard-upload-btn" type="button" tabindex="5">Загрузить изображения</button>
			<button id="zboard-submit-btn" name="submit" type="button" tabindex="5">Сохранить объявление</button>
		</div>
		<span class="submit">
			<button tabindex="5" type="reset">Сброс</button>
		</span>
	</form>
</div>
