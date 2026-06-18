<form method="post" action="admin.php?mod=extra-config&plugin=eshop&action=edit_payment&id={{ entries.name }}">
	<div class="card-body">
		<legend class="title">Настройки Privat24</legend>
	
		<div class="form-row mb-3">
			<label class="col-lg-3 col-form-label">Merchant ID</label>
			<div class="col-lg-9">
				<input type="text" size="80" name="merchantid" value="{{ entries.options.merchantid }}" class="form-control"/>
			</div>
		</div>
		<div class="form-row mb-3">
			<label class="col-lg-3 col-form-label">Пароль</label>
			<div class="col-lg-9">
				<input type="text" size="80" name="merchantpass" value="{{ entries.options.merchantpass }}" class="form-control"/>
			</div>
		</div>
		
	</div>

			<div class="card-footer">
				<div class="text-center">
					<button type="submit" name="submit" class="btn btn-outline-warning">Сохранить..</button>
				</div>
			</div>
</form>