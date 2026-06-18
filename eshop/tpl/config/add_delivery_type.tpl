{{ entries.error }}
<form method="post" action="" enctype="multipart/form-data">
	<div class="card-body">
		<div class="form-row mb-3">
			<label class="col-lg-3 col-form-label">Название</label>
			<div class="col-lg-9">
				<input type="text" size="80" name="name" value="{{ entries.name }}" class="form-control"/>
			</div>
		</div>
		<div class="form-row mb-3">
			<label class="col-lg-3 col-form-label">Описание</label>
			<div class="col-lg-9">
				<textarea rows="10" cols="45" class="form-control" name="description">{{ entries.description }}</textarea>
			</div>
		</div>
		<div class="form-row mb-3">
			<label class="col-lg-3 col-form-label">Позиция</label>
			<div class="col-lg-9">
				<input type="text" size="1" name="position" value="{{ entries.position }}" class="form-control"/>
			</div>
		</div>
		<div class="form-row mb-3">
			<label class="col-lg-3 col-form-label">Активная?</label>
			<div class="col-lg-9">
				<input type="checkbox" name="active" {% if entries.mode == 'add' %}checked{% else %}{% if entries.active == '1' %}checked{% endif %}{% endif %} value="1">
			</div>
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
