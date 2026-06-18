<!-- <script src="{{ admin_url }}/plugins/eshop/tpl/config/jq/jquery-1.7.2.min.js" type="text/javascript"></script> -->

<link rel="stylesheet" type="text/css" href="{{ admin_url }}/plugins/eshop/upload/uploadifive/uploadifive.css">
<script src="{{ admin_url }}/plugins/eshop/upload/uploadifive/jquery.uploadifive.min.js" type="text/javascript"></script>

<div
	class="row">
	<!-- Левый столбец -->
	<div
		class="col-md-6">
		<!-- Экспорт YML -->
		<div class="card mb-4">
			<div class="card-header bg-primary text-white">
				<i class="fa fa-download"></i>
				Экспорт YML
			</div>
			<div class="card-body">
				<p class="card-text">
					URL:
					<a href="{{ home }}{{ yml_export_link }}" target="_blank" class="btn btn-outline-primary btn-sm">
						<i class="fa fa-link"></i>
						{{ home }}{{ yml_export_link }}
					</a>
				</p>
			</div>
		</div>

		<!-- Экспорт CSV -->
		<form action="" method="post">
			<input type="hidden" name="export_csv" value="1">
			<div class="card mb-4">
				<div class="card-header bg-success text-white">
					<i class="fa fa-file-excel-o"></i>
					Экспорт CSV
				</div>
				<div class="card-body">
					<p class="card-text">
						Экспорт товаров позволяет сохранить товары в файл CSV, который вы сможете открыть в MS Excel или других программах.
												                            Затем вы сможете отредактировать данные и сделать импорт этого файла обратно в магазин, автоматизировав, например, изменение цен или других параметров.
					</p>
					<p class="card-text">
						Для того чтобы файл нормально открылся, в качестве разделителя укажите точку с запятой (<code>;</code>).
					</p>
				</div>
				<div class="card-footer text-center">
					<button type="submit" name="submit" class="btn btn-success">
						<i class="fa fa-file-excel-o"></i>
						Экспортировать!
					</button>
				</div>
			</div>
		</form>
		<!-- Массовая загрузка изображений -->
		<form action="" method="post" id="multiple_upload_images" enctype="multipart/form-data">
			<input type="hidden" name="multiple_upload_images" value="1">
			<div class="card mb-4">
				<div class="card-header bg-secondary text-white">
					<i class="fa fa-picture-o"></i>
					Массовая загрузка изображений
				</div>
				<div class="card-body">
					<div class="form-group">
						<label for="file_upload">Выберите изображения:</label>
						<div id="queue" class="mb-3"></div>
						<input id="file_upload" name="file_upload" type="file" multiple="true" class="form-control-file"/>
					</div>
					<script type="text/javascript">
						$(document).ready(function () {
var i = 0;
$('#file_upload').uploadifive({
'auto': false,
'formData': {},
'queueID': 'queue',
'uploadScript': '/engine/plugins/eshop/upload/libs/upload_product_images.php',
'onUpload': function (filesToUpload) {
i = 0;
},
'onUploadComplete': function (file, data) {
console.log(file);
console.log(data);
if (data == "1") {
var form = document.forms['multiple_upload_images'];
var el = document.createElement("input");
el.type = "hidden";
el.name = "data[images][" + i + "]";
el.value = file.name;
form.appendChild(el);
i++;
}
},
'onQueueComplete': function (uploads) {
document.getElementById('multiple_upload_images').submit();
}
});
});
					</script>
				</div>
				<div class="card-footer text-center">
					<button type="button" onclick="javascript:$('#file_upload').uploadifive('upload')" class="btn btn-secondary">
						<i class="fa fa-upload"></i>
						Загрузить!
					</button>
				</div>
			</div>
		</form>

	</div>

	<!-- Правый столбец -->
	<div
		class="col-md-6">
		<!-- Импорт YML -->
		<form action="" method="post">
			<input type="hidden" name="import" value="1">
			<div class="card mb-4">
				<div class="card-header bg-warning text-dark">
					<i class="fa fa-upload"></i>
					Импорт YML
				</div>
				<div class="card-body">
					<div class="form-group row mb-3">
						<label for="yml_url" class="col-lg-3 col-form-label">URL:</label>
						<div class="col-lg-9">
							<input type="text" id="yml_url" name="yml_url" class="form-control" placeholder="Введите URL"/>
						</div>
					</div>
					<div class="alert alert-danger" role="alert">
						<i class="fa fa-exclamation-triangle"></i>
						Внимание! Существующие данные могут быть удалены!
					</div>
				</div>
				<div class="card-footer text-center">
					<button type="submit" name="submit" class="btn btn-warning">
						<i class="fa fa-upload"></i>
						Загрузить!
					</button>
				</div>
			</div>
		</form>

		<!-- Импорт CSV -->
		<form action="" method="post" enctype="multipart/form-data">
			<input type="hidden" name="import_csv" value="1">
			<div class="card mb-4">
				<div class="card-header bg-info text-white">
					<i class="fa fa-upload"></i>
					Импорт CSV
				</div>
				<div class="card-body">
					<div class="form-group">
						<label for="filename">Выберите файл CSV:</label>
						<div class="custom-file">
							<input type="file" class="custom-file-input" id="filename" name="filename" required/>
							<label class="custom-file-label" for="filename">Выберите файл</label>
						</div>
					</div>
				</div>
				<div class="card-footer text-center">
					<button type="submit" name="submit" class="btn btn-info">
						<i class="fa fa-upload"></i>
						Импортировать!
					</button>
				</div>
			</div>
		</form>

	</div>
</div>

<!-- Новый ряд для "Массовое изменение цен" и "Обновление валют" -->
<div
	class="row mt-4">
	<!-- Массовое изменение цен -->
	<div class="col-md-6">
		<form action="" method="post">
			<input type="hidden" name="change_price" value="1">
			<div class="card mb-4">
				<div class="card-header bg-danger text-white">
					<i class="fa fa-percent"></i>
					Массовое изменение цен
				</div>
				<div class="card-body">
					<div class="form-group row mb-3">
						<label for="change_price_type" class="col-lg-3 col-form-label">Изменение цены:</label>
						<div class="col-lg-9">
							<div class="input-group">
								<select name="change_price_type" id="change_price_type" class="custom-select">
									<option value="1">Увеличить</option>
									<option value="0">Уменьшить</option>
								</select>
								<input type="text" name="change_price_qnt" class="form-control" placeholder="Введите значение"/>
								<div class="input-group-append">
									<span class="input-group-text">%</span>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="card-footer text-center">
					<button type="submit" name="submit" class="btn btn-danger">
						<i class="fa fa-percent"></i>
						Изменить!
					</button>
				</div>
			</div>
		</form>
	</div>

	<!-- Обновление валют -->
	<div class="col-md-6">
		<form action="" method="post">
			<input type="hidden" name="currency" value="1">
			<div class="card mb-4">
				<div class="card-header bg-dark text-white">
					<i class="fa fa-money"></i>
					Обновление валют
				</div>
				<div class="card-body">
					<p class="card-text">
						Источник данных:
						<a href="https://CurrencyConverterApi.com" target="_blank" class="text-primary">
							CurrencyConverterApi.com
						</a>
					</p>
				</div>
				<div class="card-footer text-center">
					<button type="submit" name="submit" class="btn btn-dark">
						<i class="fa fa-refresh"></i>
						Обновить курсы валют
					</button>
				</div>
			</div>
		</form>
	</div>
</div>
