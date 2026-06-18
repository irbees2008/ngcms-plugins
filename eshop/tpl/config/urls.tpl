<form method="post" action="">
	<div class="card-body">

		<div class="form-row mb-3">
			<label class="col-lg-3 col-form-label">Чпу включен?</label>
			<div class="col-lg-9">
				<select name="url" class="custom-select">{{ info }}</select>
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