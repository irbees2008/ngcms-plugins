<form method="post" action="admin.php?mod=extra-config&plugin=eshop&action=edit_payment&id={{ entries.name }}">
	<div class="card-body">
		<legend class="title">Настройки UnitPay</legend>
	
		<div class="form-row mb-3">
			<label class="col-lg-3 col-form-label">ID вашего проекта в системе Unitpay</label>
			<div class="col-lg-9">
				<input type="text" size="80" name="projectId" value="{{ entries.options.projectId }}" class="form-control"/>
			</div>
		</div>
		<div class="form-row mb-3">
			<label class="col-lg-3 col-form-label">Секретный ключ, доступен в настройках проекта</label>
			<div class="col-lg-9">
				<input type="text" size="80" name="secretKey" value="{{ entries.options.secretKey }}" class="form-control"/>
			</div>
		</div>
		
	</div>
		
			<div class="card-footer">
				<div class="text-center">
					<button type="submit" name="submit" class="btn btn-outline-warning">Сохранить..</button>
				</div>
			</div>
</form>