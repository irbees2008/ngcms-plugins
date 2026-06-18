<form method="post" action="admin.php?mod=extra-config&plugin=eshop&action=edit_payment&id={{ entries.name }}">

	<div class="card-body">
		<legend class="title">Настройки LiqPay</legend>
	
		<div class="form-row mb-3">
			<label class="col-lg-3 col-form-label">Публичный ключ</label>
			<div class="col-lg-9">
				<input type="text" size="80" name="public_key" value="{{ entries.options.public_key }}" class="form-control"/>
			</div>
		</div>
		<div class="form-row mb-3">
			<label class="col-lg-3 col-form-label">Приватный ключ</label>
			<div class="col-lg-9">
				<input type="text" size="80" name="private_key" value="{{ entries.options.private_key }}" class="form-control"/>
			</div>
		</div>
		<div class="form-row mb-3">
			<label class="col-lg-3 col-form-label">Тестовый режим?</label>
			<div class="col-lg-9">
				<select name="sandbox" class="custom-select">
                    <option value="0" {% if entries.options.sandbox == 0 %}selected{% endif %}>Нет</option>
                    <option value="1" {% if entries.options.sandbox == 1 %}selected{% endif %}>Да</option>
                </select>
			</div>
		</div>
	</div>
		
	<div class="card-footer">
		<div class="text-center">
			<button type="submit" name="submit" class="btn btn-outline-warning">Сохранить..</button>
		</div>
	</div>
</form>