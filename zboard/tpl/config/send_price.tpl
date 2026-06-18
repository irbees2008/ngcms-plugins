{error}
<form method="post" action="">
	<div class="card mb-4">
		<div class="card-header bg-info text-white font-weight-bold">Добавить прайс</div>
		<div class="card-body">
			<div class="form-group">
				<label for="time">Время (в днях)</label>
				<input type="text" class="form-control" id="time" name="time" value="{time}"/>
			</div>
			<div class="form-group">
				<label for="price">Стоимость</label>
				<input type="text" class="form-control" id="price" name="price" value="{price}"/>
			</div>
			<div class="text-center">
				<button type="submit" name="submit" class="btn btn-success">Добавить</button>
			</div>
		</div>
	</div>
</form>
