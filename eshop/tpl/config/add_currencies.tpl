{{ entries.error }}
<form method="post" action="" enctype="multipart/form-data">

	<div class="card-body">
		<div class="form-row mb-3">
			<label class="col-lg-3 col-form-label">Название валюты</label>
			<div class="col-lg-9">
				<input type="text" size="80" name="name" value="{{ entries.name }}" class="form-control"/>
			</div>
		</div>
		<div class="form-row mb-3">
			<label class="col-lg-3 col-form-label">Знак</label>
			<div class="col-lg-9">
				<input type="text" size="80" name="sign" value="{{ entries.sign }}" class="form-control"/>
			</div>
		</div>
		<div class="form-row mb-3">
			<label class="col-lg-3 col-form-label">Код ISO</label>
			<div class="col-lg-9">
				<input type="text" size="80" name="code" value="{{ entries.code }}" class="form-control"/>
			</div>
		</div>
		<div class="form-row mb-3">
			<label class="col-lg-3 col-form-label">Конверсия</label>
			<div class="col-lg-9">
				<div class="input-group mb-3">
				<input type="text" size="6" name="rate_from" value="{{ entries.rate_from }}" class="form-control"/>
				<div class="input-group-prepend input-group-append">
					<label class="input-group-text">$ =</label>
				</div>
				<input type="text" size="6" name="rate_to" value="1.00" disabled="disabled" class="form-control"/>
			</div></div>
		</div>
		<div class="form-row mb-3">
			<label class="col-lg-3 col-form-label">Позиция</label>
			<div class="col-lg-9">
				<input type="text" size="1" name="position" value="{{ entries.position }}" class="form-control"/>
			</div>
		</div>
		<div class="form-row mb-3">
			<label class="col-lg-3 col-form-label">Позиция</label>
			<div class="col-lg-9">
				<input type="checkbox" name="enabled" {% if entries.mode == 'add' %}checked{% else %}{% if entries.enabled == '1' %}checked{% endif %}{% endif %} value="1">
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
