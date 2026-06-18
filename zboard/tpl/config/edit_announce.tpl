<script src="{tpl_home}/plugins/zboard/upload/js/jquery-1.7.2.min.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="{tpl_home}/plugins/zboard/upload/uploadifive/uploadifive.css">
<link rel="stylesheet" href="{tpl_home}/plugins/zboard/tpl/config/capty/jquery.capty.css" type="text/css"/>
<script src="{tpl_home}/plugins/zboard/upload/uploadifive/jquery.uploadifive.min.js" type="text/javascript"></script>
<script type="text/javascript" src="{tpl_home}/plugins/zboard/tpl/config/capty/jquery.capty.min.js"></script>
{error}
<form method="post" action="" name="form" enctype="multipart/form-data">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-6 mb-3">
				<label for="announce_name">Заголовок объявления</label>
				<input type="text" class="form-control" id="announce_name" name="announce_name" value="{announce_name}"/>
			</div>
			<div class="col-md-6 mb-3">
				<label for="author">Автор</label>
				<input type="text" class="form-control" id="author" name="author" value="{author}"/>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6 mb-3">
				<label for="announce_period">Период объявления</label>
				<select class="form-control" id="announce_period" name="announce_period">{list_period}</select>
			</div>
			<div class="col-md-6 mb-3">
				<label for="cat_id">Категория</label>
				<select class="form-control" id="cat_id" name="cat_id">{options}</select>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6 mb-3">
				<label for="announce_description">Текст объявления</label>
				<textarea class="form-control" id="announce_description" name="announce_description" rows="6">{announce_description}</textarea>
			</div>
			<div class="col-md-6 mb-3">
				<label for="announce_contacts">Контакты  (телефон)</label>
				<input type="tel" class="form-control" id="announce_contacts" name="announce_contacts" value="{announce_contacts}" placeholder="+7 (___) ___-__-__"/>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6 mb-3">
				<label>Прикрепить изображения</label>
				<div class="mb-2">
					<input type="hidden" id="txtdes" name="txtdes" value="{id}"/>
					<div id="queue"></div>
					<input id="file_upload" name="file_upload" type="file" multiple="true">
				</div>
				<script type="text/javascript">
					$(document).ready(function () {
var count = 0;
$('#file_upload').uploadifive({
'auto': false,
'formData': {
'id': $("#txtdes").val()
},
'queueID': 'queue',
'uploadScript': '/engine/plugins/zboard/upload/libs/subirarchivo.php?id={id}',
'onUpload': function (filesToUpload) {
count = 0;
},
'onUploadComplete': function (file, data) {
count++;
},
'onQueueComplete': function (uploads) {}
});
$('.fix').capty({cWrapper: 'capty-tile', height: 36, opacity: .6});
// Маска телефона (админ)
var ac = document.getElementById('announce_contacts');
if (ac) {
ac.addEventListener('input', function () {
var v = ac.value;
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
			</div>
			<div class="col-md-6 mb-3">
				<label>Прикрепленные изображения</label>
				<div class="table-responsive">
					<table class="table table-bordered table-sm">
						<tbody>
							<tr>{entriesImg}</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6 mb-3">
				<div class="form-check">
					<input class="form-check-input" type="checkbox" id="announce_activeme" name="announce_activeme" {announce_activeme} value="1">
					<label class="form-check-label" for="announce_activeme">Активировать объявление?</label>
				</div>
			</div>
			<div class="col-md-6 mb-3 text-right">
				<button type="submit" name="submit" onclick="javascript:$('#file_upload').uploadifive('upload')" class="btn btn-primary mr-2">Отредактировать</button>
				<button type="submit" name="delme" class="btn btn-danger">Удалить</button>
			</div>
		</div>
	</div>
</form>
