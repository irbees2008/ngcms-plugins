<script type="text/javascript" src="{{ scriptLibrary }}/ajax.js"></script>
<script type="text/javascript" src="{{ scriptLibrary }}/admin.js"></script>
<script type="text/javascript" src="{{ scriptLibrary }}/libsuggest.js"></script>

<script
	language="javascript" type="text/javascript">
	<!--

	function addEvent(elem, type, handler) {
if (elem.addEventListener) {
elem.addEventListener(type, handler, false)
} else {
elem.attachEvent("on" + type, handler)
}
}

// DateEdit filter
function filter_attach_DateEdit(id) {
var field = document.getElementById(id);
if (! field) 
return false;

if (field.value == '') 
field.value = 'DD.MM.YYYY';

field.onfocus = function (event) {
var ev = event ? event : window.event;
var elem = ev.target ? ev.target : ev.srcElement;

if (elem.value == 'DD.MM.YYYY') 
elem.value = '';

return true;
}

field.onkeypress = function (event) {
var ev = event ? event : window.event;
var keyCode = ev.keyCode ? ev.keyCode : ev.charCode;
var elem = ev.target ? ev.target : ev.srcElement;
var elv = elem.value;

isMozilla = false;
isIE = false;
isOpera = false;
if (navigator.appName == 'Netscape') {
isMozilla = true;
} else if (navigator.appName == 'Microsoft Internet Explorer') {
isIE = true;
} else if (navigator.appName == 'Opera') {
isOpera = true;
} else { /* alert('Unknown navigator: `'+navigator.appName+'`'); */
}

// document.getElementById('debugWin').innerHTML = 'keyPress('+ev.keyCode+':'+ev.charCode+')['+(ev.shiftKey?'S':'.')+(ev.ctrlKey?'C':'.')+(ev.altKey?'A':'.')+']<br/>' + document.getElementById('debugWin').innerHTML;

// FF - onKeyPress captures functional keys. Skip anything with charCode = 0
if (isMozilla && ! ev.charCode) 
return true;

// Opera - dumb browser, don't let us to determine some keys
if (isOpera) {
var ek = '';
// for (i in event) { ek = ek + '['+i+']: '+event[i]+'<br/>\n'; }
// alert(ek);
if (ev.keyCode < 32) 
return true;

if (! ev.shiftKey && ((ev.keyCode >= 33) && (ev.keyCode<= 47))) return true;
				if (!ev.keyCode) return true;
				if (!ev.which) return true;
			}

			// Don't block CTRL / ALT keys
			if (ev.altKey || ev.ctrlKey || !keyCode)
				return true;

			// Allow to input only digits [0..9] and dot [.]
			if (((keyCode >= 48) && (keyCode <= 57)) || (keyCode == 46)) 
return true;

return false;
}

return true;
}
	-->
</script>

<div class="container-fluid">
	<div class="row mb-2">
		<div class="col-sm-6 d-none d-md-block ">
			<h1 class="m-0 text-dark">xsyslog</h1>

		</div>
		<!-- /.col -->
		<div class="col-sm-6">
			<ol class="breadcrumb float-sm-right">
				<li class="breadcrumb-item">
					<a href="admin.php">
						<i class="fa fa-home"></i>
					</a>
				</li>
				<li class="breadcrumb-item">
					<a href="admin.php?mod=extras">Управление плагинами</a>
				</li>
				<li class="breadcrumb-item active" aria-current="page">Журнал действий пользователей</li>

			</ol>
		</div>
		<!-- /.col -->
	</div>
	<!-- /.row -->
</div>

<div class="card">
	<h5 class="card-header">xsyslog</h5>
	<div class="table-responsive">

		<!-- Hidden SUGGEST div -->
		<div id="suggestWindow" class="suggestWindow">
			<table id="suggestBlock" cellspacing="0" cellpadding="0" width="100%"></table>
			<a href="#" align="right" id="suggestClose">close</a>
		</div>

		<form action="{{ php_self }}?mod=extra-config&plugin=xsyslog" method="post" name="options_bar">
			<div class="container editfilter">

				<div class="row">
					<div class="col-sm filterblock">

						<label>Дата</label>
						с :&nbsp;
						<input type="text" id="dr1" name="dr1" value="{{ fDateStart }}" class="form-control bfdate"/>
						по :&nbsp;&nbsp;
						<input type="text" id="dr2" name="dr2" value="{{ fDateEnd }}" class="form-control bfdate"/>
						<label>Пользователь : </label>
						<input name="an" id="an" class="form-control bfauthor" type="text" value="{{ an }}" autocomplete="off"/>
<br>
						<span id="suggestLoader" style="width: 20px; visibility: hidden;"></span>

					</div>
					<div class="col-sm filterblock2">

						<label class="left">Plugin : </label>&nbsp;&nbsp;
						{{ catPlugins }}
						<label class="left">Item : </label>&nbsp;&nbsp;
						{{ catItems }}

					</div>
					<div class="col-sm filterblock">

						<label>Статус : </label>
						<select name="status" class="form-control bfstatus">

							<option value="null" {% if fstatus == 'null' %} selected {% endif %}>- Все -</option>
							<option value="0" {% if fstatus == '0' %} selected {% endif %}>0</option>
							<option value="1" {% if fstatus == '1' %} selected {% endif %}>1</option>
						</select>
						<label>На странице : </label>
						<input name="rpp" value="{{ rpp }}" type="text" size="3" class="form-control bfstatus"/>

					</div>
					
				</div>

			</div>
<div class="card-footer text-center">
	<div class="btn-group">
		<input type="submit" name="clearbtn" value="Очистить" class="filterbutton btn btn-outline-success"/>

		<input type="submit" value="Показать" class="filterbutton btn btn-outline-success"/>
	</div>

</div>

		</div>

	</form>
	<!-- Конец блока фильтрации -->

	<br/>

	{{ entries }}

</div><script>
$('#dr1, #dr2').datetimepicker({format: "d.m.Y"});</script><script>
document.addEventListener('DOMContentLoaded', function () {
function systemInit() {
var aSuggest = new ngSuggest('an', {
'localPrefix': '',
'reqMethodName': 'core.users.search',
'lId': 'suggestLoader',
'hlr': 'true',
'iMinLen': 1,
'stCols': 2,
'stColsClass': [
'cleft', 'cright'
],
'stColsHLR': [true, false]
});
}

systemInit();

if (typeof filter_attach_DateEdit === 'function') {
filter_attach_DateEdit('dr1');
filter_attach_DateEdit('dr2');
} else {
console.warn('filter_attach_DateEdit is not defined');
}
});</script>
