<table class="table table-striped">
	<tbody>
		{% for image in images %}
			<tr>
				<td>{{ image.number }}</td>
				{% if image.flags.exist %}
					<td>
						<input type="text" name="xfields_{{ image.id }}_dscr[{{ image.image.id }}]" value="{{ image.description }}" placeholder="Введите описание..." class="form-control mb-2"/>
						<figure class="figure mb-0">
							<a href="{{ image.image.url }}" target="_blank">
								{% if image.flags.preview %}
									<img src="{{ image.preview.url }}" width="{{ image.preview.width }}" height="{{ image.preview.height }}" class="figure-img img-fluid rounded"/>
								{% else %}
									NO PREVIEW
								{% endif %}
							</a>
							<figcaption class="figure-caption">
								<label class="col-form-label d-block"><input type="checkbox" name="xfields_{{ image.id }}_del[{{ image.image.id }}]" value="1"/>
									удалить</label>
							</figcaption>
						</figure>
					</td>
				{% else %}
					<td>
						<input type="text" name="xfields_{{ image.id }}_adscr[]" value="{{ image.description }}" placeholder="Введите описание..." class="form-control mb-2"/>
						<div class="input-group mb-2">
							<input type="file" name="xfields_{{ image.id }}[]" class="form-control"/>
							<button type="button" class="btn btn-outline-secondary" onclick="openXFieldsSelector('{{ image.id }}', {{ image.number }})">
								<i class="fa fa-images"></i>
								Выбрать загруженное
							</button>
						</div>
						<input type="hidden" name="xfields_{{ image.id }}_existing[]" id="xfields_{{ image.id }}_existing_{{ image.number }}" value=""/>
						<div id="xfields_{{ image.id }}_existing_preview_{{ image.number }}" class="mt-2" style="display:none;">
							<img src="" class="img-thumbnail" style="max-width: 200px;"/>
							<button type="button" class="btn btn-sm btn-danger ms-2" onclick="clearXFieldsSelection('{{ image.id }}', {{ image.number }})">✕</button>
						</div>
					</td>
				{% endif %}
			</tr>
		{% endfor %}
	</tbody>
</table>

<div class="modal fade" id="xfieldsImageModal" tabindex="-1">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Выбор из загруженных изображений</h5>
				<button type="button" class="close" data-dismiss="modal">
					<span>&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="row mb-3">
					<div class="col-md-6">
						<input type="text" id="xfields_search" class="form-control" placeholder="Поиск по описанию или имени файла..."/>
					</div>
					<div class="col-md-3">
						<select id="xfields_field_filter" class="form-control">
							<option value="">Все поля</option>
						</select>
					</div>
					<div class="col-md-3">
						<button type="button" class="btn btn-primary w-100" onclick="loadXFieldsImages()">Поиск</button>
					</div>
				</div>
				<div id="xfields_images_container" class="row" style="max-height: 500px; overflow-y: auto;">
					<div class="col-12 text-center p-4">
						<p>Загрузка...</p>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
			</div>
		</div>
	</div>
</div>

 <script>
var xfieldsCurrentField = null;
var xfieldsCurrentNumber = null;

function openXFieldsSelector(fieldId, number) {
	xfieldsCurrentField = fieldId;
	xfieldsCurrentNumber = number;
	$('#xfieldsImageModal').modal('show');
	loadXFieldsImages();
}

function loadXFieldsImages() {
	var search = $('#xfields_search').val();
	var fieldFilter = $('#xfields_field_filter').val();

	$.ajax({
		url: '{{ php_self }}',
		type: 'GET',
		data: {
			mod: 'extra-functions',
			action: 'plugin',
			plugin: 'xfields',
			handler: 'get_xfields_images',
			search: search,
			field: fieldFilter
		},
		dataType: 'json',
		success: function(response) {
			if (response.status === 'ok') {
				renderXFieldsImages(response.images);
				if (response.fields) {
					renderXFieldsFilters(response.fields);
				}
			} else {
				$('#xfields_images_container').html('<div class="col-12 text-center"><p class="text-danger">Ошибка: ' + (response.error || 'Неизвестная ошибка') + '</p></div>');
			}
		},
		error: function() {
			$('#xfields_images_container').html('<div class="col-12 text-center"><p class="text-danger">Ошибка соединения</p></div>');
		}
	});
}

function renderXFieldsFilters(fields) {
	var select = $('#xfields_field_filter');
	var currentVal = select.val();
	select.find('option:not(:first)').remove();

	$.each(fields, function(id, title) {
		select.append('<option value="' + id + '">' + title + '</option>');
	});

	select.val(currentVal);
}

function renderXFieldsImages(images) {
	var container = $('#xfields_images_container');
	container.empty();

	if (!images || images.length === 0) {
		container.html('<div class="col-12 text-center p-4"><p>Изображения не найдены</p></div>');
		return;
	}

	$.each(images, function(i, img) {
		var thumbUrl = img.preview ? img.thumb_url : img.url;
		var card = $('<div class="col-lg-3 col-md-4 col-sm-6 mb-3"></div>');
		var cardInner = $('<div class="card h-100 shadow-sm" style="cursor:pointer;"></div>');
		var imgElem = $('<img class="card-img-top" style="object-fit:cover; height:180px;" src="' + thumbUrl + '" alt="' + img.name + '" />');
		var body = $('<div class="card-body p-2"></div>');

		var info = '<p class="card-text small mb-1"><strong>' + (img.description || img.orig_name) + '</strong></p>';
		info += '<p class="card-text small text-muted mb-1">' + img.width + '×' + img.height + '</p>';
		if (img.news_title) {
			info += '<p class="card-text small text-muted mb-0"><i class="fa fa-newspaper-o"></i> ' + img.news_title + '</p>';
		}

		body.append(info);
		cardInner.append(imgElem).append(body);
		card.append(cardInner);

		cardInner.click(function() {
			selectXFieldsImage(img);
		});

		container.append(card);
	});
}

function selectXFieldsImage(img) {
	if (!xfieldsCurrentField || !xfieldsCurrentNumber) return;

	var hiddenInput = $('#xfields_' + xfieldsCurrentField + '_existing_' + xfieldsCurrentNumber);
	var previewDiv = $('#xfields_' + xfieldsCurrentField + '_existing_preview_' + xfieldsCurrentNumber);

	hiddenInput.val(img.id);
	previewDiv.find('img').attr('src', img.preview ? img.thumb_url : img.url);
	previewDiv.show();

	$('#xfieldsImageModal').modal('hide');
}

function clearXFieldsSelection(fieldId, number) {
	$('#xfields_' + fieldId + '_existing_' + number).val('');
	$('#xfields_' + fieldId + '_existing_preview_' + number).hide();
}

$(document).ready(function() {
	if ($('#xfieldsImageModal').length) {
		loadXFieldsImages();
	}
});
</script>
