<form method="post" action="admin.php?mod=extra-config&plugin=eshop&action=edit_payment&id={{ entries.name }}">
	<div class="card-body">
		<legend class="title">Настройки Robokassa</legend>
	
		<div class="form-row mb-3">
			<label class="col-lg-3 col-form-label">Логин</label>
			<div class="col-lg-9">
				<input type="text" size="80" name="mrh_login" value="{{ entries.options.mrh_login }}" class="form-control"/>
			</div>
		</div>
		<div class="form-row mb-3">
			<label class="col-lg-3 col-form-label">Пароль1</label>
			<div class="col-lg-9">
				<input type="text" size="80" name="mrh_pass1" value="{{ entries.options.mrh_pass1 }}" class="form-control"/>
			</div>
		</div>
		<div class="form-row mb-3">
			<label class="col-lg-3 col-form-label">Пароль2</label>
			<div class="col-lg-9">
				<input type="text" size="80" name="mrh_pass2" value="{{ entries.options.mrh_pass2 }}" class="form-control"/>
			</div>
		</div>
		<div class="form-row mb-3">
			<label class="col-lg-3 col-form-label">Тестовый режим?</label>
			<div class="col-lg-9">
				<select name="test_mode" class="custom-select">
                    <option value="0" {% if entries.options.test_mode == 0 %}selected{% endif %}>Нет</option>
                    <option value="1" {% if entries.options.test_mode == 1 %}selected{% endif %}>Да</option>
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