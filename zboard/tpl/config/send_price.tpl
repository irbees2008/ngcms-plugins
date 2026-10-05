{error}
<form method="post" action="">
	<div class="card mb-4">
		<div class="card-header bg-info text-white font-weight-bold">{l_zboard:admin_add_price}</div>
		<div class="card-body">
			<div class="form-group">
				<label for="time">{l_zboard:admin_time_days}</label>
				<input type="text" class="form-control" id="time" name="time" value="{time}"/>
			</div>
			<div class="form-group">
				<label for="price">{l_zboard:admin_price}</label>
				<input type="text" class="form-control" id="price" name="price" value="{price}"/>
			</div>
			<div class="text-center">
				<button type="submit" name="submit" class="btn btn-success">{l_zboard:admin_add}</button>
			</div>
		</div>
	</div>
</form>
