<script src="{{ admin_url }}/plugins/eshop/tpl/config/tinymce/tinymce.min.js" type="text/javascript"></script>
<script>
    tinymce.init({
        selector: 'textarea[name=html_default]',
        height: 100,
        plugins: [
            'advlist autolink lists link image charmap print preview anchor',
            'searchreplace visualblocks code fullscreen',
            'insertdatetime media table contextmenu paste code'
        ],
        toolbar: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image'
    });

</script>
{{ entries.error }}

<form method="post" action="">
	<div class="card-body">
		<div class="form-row mb-3">
			<label class="col-lg-3 col-form-label">Название</label>
			<div class="col-lg-9">
				<input type="text" size="80" name="name" value="{{ entries.name }}" class="form-control"/>
			</div>
		</div>
		<div class="form-row mb-3">
			<label class="col-lg-3 col-form-label">Категории</label>
			<div class="col-lg-9">
                <select multiple="" name="feature_categories[]" class="custom-select">
                    {{ entries.catz }}
                </select>
			</div>
		</div>
		<div class="form-row mb-3">
			<label class="col-lg-3 col-form-label">Тип поля</label>
			<div class="col-lg-9">
                <select size="5" id="xfSelectType" name="ftype" onclick="clx(this.value);" onchange="clx(this.value);" class="custom-select">
                    <option value="text" {% if (entries.ftype == 0) %}selected{% endif %}>Текстовый</option>
                    <option value="checkbox" {% if (entries.ftype == 1) %}selected{% endif %}>Флажок (checkbox)</option>
                    <option value="select" {% if (entries.ftype == 2) %}selected{% endif %}>Выбор значения</option>
                    <option value="html" {% if (entries.ftype == 3) %}selected{% endif %}>HTML</option>
                </select>
			</div>
		</div>
		
	</div>
		
    <!-- FIELD TYPE: TEXT -->
    <div id="type_text" class="card-body">
        <table border="0" cellspacing="1" cellpadding="1" class="content">
            <tr class="contRow1">
                <td width="5%" style="background-color: #EAF0F7; border-left: 1px solid #D1DFEF;">текст</td>
                <td width="45%">Значение по умолчанию:</td>
                <td><input type="text" name="text_default" value="{% if (entries.ftype == 0) %}{{ entries.fdefault }}{% endif %}" size=40></td>
            </tr>
        </table>
    </div>

    <!-- FIELD TYPE: CHECKBOX -->
    <div id="type_checkbox" class="card-body">
        <table border="0" cellspacing="1" cellpadding="1" class="content">
            <tr class="contRow1">
                <td width="5%" style="background-color: #EAF0F7; border-left: 1px solid #D1DFEF;">флаг</td>
                <td width="45%">Значение по умолчанию:</td>
                <td><input type="checkbox" name="checkbox_default" value="1" {% if (entries.fdefault == 1) and (entries.ftype == 1) %}checked{% endif %} ></td>
            </tr>
        </table>
    </div>

    <!-- FIELD TYPE: SELECT -->
    <div id="type_select" class="card-body">
        <table border="0" cellspacing="1" cellpadding="1" class="content table table-sm">
            <tr class="contRow1">
                <td width="5%" style="background-color: #EAF0F7; border-left: 1px solid #D1DFEF;">выбор</td>
                <td valign="top">Список значений:</td>
                <td>
                    <table id="xfSelectTable" width="100%" cellspacing="0" cellpadding="0" border="0" class="content" style="padding: 0px;">
                        <thead>
                        <tr class="contRow1">
                            <td>Код</td>
                            <td>Значение</td>
                            <td>&nbsp;</td>
                        </tr>
                        </thead>
                        <tbody id="xfSelectRows">
                        {% if (mode == "add") %}
                            <tr>
                                <td><input size="12" name="so_data[1][0]" type="text" value=""/></td>
                                <td><input type="text" size="55" name="so_data[1][1]" value=""/></td>
                                <td><a href="#" onclick="return false;"><img src="{{ admin_url }}/plugins/eshop/tpl/img/delete.png" alt="DEL" width="12" height="12"/></a></td>
                            </tr>
                        {% else %}
                            {{ entries.sOpts }}
                        {% endif %}
                        </tbody>
                        <tfoot>
                        <tr>
                            <td colspan="3"><input type="button" id="xfBtnAdd" class="btn btn-outline-info" value=" + Добавить строку"/></td>
                        </tr>
                        </tfoot>
                    </table>
                </td>
            </tr>
            <tr class="contRow1">
                <td width="5%" style="background-color: #EAF0F7; border-left: 1px solid #D1DFEF;">выбор</td>
                <td>Значение по умолчанию: <br/>
                    <small><i>При сохранении кодов</i>: код
                </td>
                <td><input type="text" name="select_default" value="{% if (entries.ftype == 2) %}{{ entries.fdefault }}{% endif %}" size=40></td>
            </tr>
        </table>
    </div>

    <!-- FIELD TYPE: HTML -->
    <div id="type_html" class="card-body">
        <table border="0" cellspacing="1" cellpadding="1" class="content">
            <tr class="contRow1">
                <td width="5%" style="background-color: #EAF0F7; border-left: 1px solid #D1DFEF;">HTML</td>
                <td width="45%">Значение по умолчанию:</td>
                <td>
                    <textarea name="html_default">{% if (entries.ftype == 3) %}{{ entries.fdefault }}{% endif %}</textarea>
                </td>
            </tr>
        </table>
    </div>

	<div class="card-body">
		<div class="form-row mb-3">
			<label class="col-lg-3 col-form-label">Позиция</label>
			<div class="col-lg-9">
				<input type="text" size="80" name="position" value="{{ entries.position }}" class="form-control"/>
			</div>
		</div>
		<div class="form-row mb-3">
			<label class="col-lg-3 col-form-label">В фильтре?</label>
			<div class="col-lg-9">
				<input type="checkbox" name="in_filter" {% if entries.in_filter == '1' %}checked{% endif %} value="1">
			</div>
		</div>
		
			<div class="card-footer">
				<div class="row">
					<div class="col-lg-6 mb-2 mb-lg-0"></div>
					<div class="col-lg-6">
						<input type="submit" name="submit" value="Сохранить" class="btn btn-outline-success"/>
					</div>
				</div>
			</div>
</form>

<script language="javascript">
    function clx(mode) {
        document.getElementById('type_text').style.display = (mode == 'text') ? 'block' : 'none';
        document.getElementById('type_checkbox').style.display = (mode == 'checkbox') ? 'block' : 'none';
        document.getElementById('type_select').style.display = (mode == 'select') ? 'block' : 'none';
        document.getElementById('type_html').style.display = (mode == 'html') ? 'block' : 'none';
    }

    clx('{% if (entries.ftype == 0) %}text{% elseif(entries.ftype == 1) %}checkbox{% elseif(entries.ftype == 2) %}select{% elseif(entries.ftype == 3) %}html{% endif %}');


    var soMaxNum = $('#xfSelectTable >tbody >tr').length + 1;

    $('#xfSelectTable a').click(function () {
        if ($('#xfSelectTable >tbody >tr').length > 1) {
            $(this).parent().parent().remove();
        } else {
            $(this).parent().parent().find("input").val('');
        }
    });

    // jQuery - INIT `select` configuration
    $("#xfBtnAdd").click(function () {
        var xl = $('#xfSelectTable tbody>tr:last').clone();
        xl.find("input").val('');
        xl.find("input").eq(0).attr("name", "so_data[" + soMaxNum + "][0]");
        xl.find("input").eq(1).attr("name", "so_data[" + soMaxNum + "][1]");
        soMaxNum++;

        xl.insertAfter('#xfSelectTable tbody>tr:last');
        $('#xfSelectTable a').click(function () {
            if ($('#xfSelectTable >tbody >tr').length > 1) {
                $(this).parent().parent().remove();
            } else {
                $(this).parent().parent().find("input").val('');
            }
        });
    });

</script>