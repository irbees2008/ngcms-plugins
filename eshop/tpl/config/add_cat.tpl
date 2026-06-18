<!-- Вывод ошибок -->
{% if entries.error %}
	<div class="alert alert-danger" role="alert">
		{{ entries.error }}
	</div>
{% endif %}

<!-- Форма -->
<form method="post" action="" enctype="multipart/form-data">
	<div class="card">
		<div
			class="card-body">
			<!-- Поле "Имя" -->
			<div class="form-group row mb-3">
				<label for="cat_name" class="col-lg-3 col-form-label">Имя</label>
				<div class="col-lg-9">
					<input type="text" id="cat_name" name="cat_name" value="{{ entries.cat_name }}" class="form-control" placeholder="Введите имя категории"/>
				</div>
			</div>

			<!-- Поле "Описание" -->
			<div class="form-group row mb-3">
				<label for="description" class="col-lg-3 col-form-label">Описание</label>
				<div class="col-lg-9">
					<input type="text" id="description" name="description" value="{{ entries.description }}" class="form-control" placeholder="Введите описание"/>
				</div>
			</div>

			<!-- Поле "URL" -->
			<div class="form-group row mb-3">
				<label for="url" class="col-lg-3 col-form-label">URL</label>
				<div class="col-lg-9">
					<input type="text" id="url" name="url" value="{{ entries.url }}" class="form-control" placeholder="Введите URL"/>
				</div>
			</div>

			<!-- Поле "Meta title" -->
			<div class="form-group row mb-3">
				<label for="meta_title" class="col-lg-3 col-form-label">Meta title</label>
				<div class="col-lg-9">
					<input type="text" id="meta_title" name="meta_title" value="{{ entries.meta_title }}" class="form-control" placeholder="Введите meta title"/>
				</div>
			</div>

			<!-- Поле "Meta keywords" -->
			<div class="form-group row mb-3">
				<label for="meta_keywords" class="col-lg-3 col-form-label">Meta keywords</label>
				<div class="col-lg-9">
					<input type="text" id="meta_keywords" name="meta_keywords" value="{{ entries.meta_keywords }}" class="form-control" placeholder="Введите meta keywords"/>
				</div>
			</div>

			<!-- Поле "Meta description" -->
			<div class="form-group row mb-3">
				<label for="meta_description" class="col-lg-3 col-form-label">Meta description</label>
				<div class="col-lg-9">
					<input type="text" id="meta_description" name="meta_description" value="{{ entries.meta_description }}" class="form-control" placeholder="Введите meta description"/>
				</div>
			</div>

			<!-- Поле "Родительская категория" -->
			<div class="form-group row mb-3">
				<label for="parent" class="col-lg-3 col-form-label">Родительская категория</label>
				<div class="col-lg-9">
					<select name="parent" id="parent" class="form-control">
						<option value="0">Выберите категорию</option>
						{{ entries.catz }}
					</select>
				</div>
			</div>

			<!-- Поле "Позиция" -->
			<div class="form-group row mb-3">
				<label for="position" class="col-lg-3 col-form-label">Позиция</label>
				<div class="col-lg-9">
					<input type="text" id="position" name="position" value="{{ entries.position }}" class="form-control" placeholder="Введите позицию"/>
				</div>
			</div>

			<!-- Поле "Изображение" -->
			<div class="form-group row mb-3">
				<label class="col-lg-3 col-form-label">Изображение</label>
				<div class="col-lg-9">
					{% if entries.image %}
						<div id="previewImage">
							<img src="{{ home }}/uploads/eshop/categories/thumb/{{ entries.image }}" alt="Preview" class="img-thumbnail" style="width: 100px; height: 100px;"/>
							<br/>
							<div class="form-check mt-2">
								<input type="checkbox" id="image_del" name="image_del" value="1" class="form-check-input"/>
								<label for="image_del" class="form-check-label">Удалить изображение</label>
							</div>
						</div>
					{% else %}
						<input type="file" id="image" name="image" class="form-control-file"/>
					{% endif %}
				</div>
			</div>
		</div>

		<!-- Подвал карточки -->
		<div class="card-footer">
			<div class="row">
				<div class="col-lg-6 mb-2 mb-lg-0"></div>
				<div class="col-lg-6 text-right">
					<button type="submit" name="submit" class="btn btn-success">
						Сохранить
					</button>
				</div>
			</div>
		</div>
	</div>
</form>
