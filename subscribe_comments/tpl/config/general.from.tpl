<form method="post" action="admin.php?mod=extra-config&plugin=subscribe_comments">
	<div class="card">
		<div class="card-body">
			<div class="mb-3 row">
				<label class="col-sm-6 col-form-label">Включить отложенную рассылку?</label>
				<div class="col-sm-6">
					<select name="delayed_send" class="form-control">
						{{ delayed_send|raw }}
					</select>
				</div>
			</div>
			<div class="mb-3 row">
				<label class="col-sm-6 col-form-label">Количество объектов на странице</label>
				<div class="col-sm-6">
					<input name="admin_count" type="number" class="form-control" value="{{ admin_count }}" min="1"/>
				</div>
			</div>
		</div>
	</div>
	<div class="mt-3">
		<button type="submit" name="submit" class="btn btn-success">Сохранить</button>
	</div>
</form>
