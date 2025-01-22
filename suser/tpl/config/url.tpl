<form method="post" action="">
	<fieldset class="border p-3">
		<legend class="w-auto">Настройки</legend>
		<div class="form-group row">
			<label for="url" class="col-sm-4 col-form-label">Чпу включен?</label>
			<div class="col-sm-8">
				<select id="url" name="url" class="form-control">
					{info}
				</select>
			</div>
		</div>
	</fieldset>

	<div class="row mt-3">
		<div class="col text-center">
			<button name="submit" type="submit" class="btn btn-primary">
				Сохранить
			</button>
		</div>
	</div>
</form>
