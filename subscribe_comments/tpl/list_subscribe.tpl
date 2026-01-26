<form action="admin.php?mod=extra-config&plugin=subscribe_comments&action=modify" method="post" name="subscribe_comments">
	<div class="card">
		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-striped table-hover">
					<thead>
						<tr>
							<th style="width: 5%">ID</th>
							<th style="width: 45%">Страница</th>
							<th style="width: 40%">Email</th>
							<th style="width: 10%" class="text-center">
								<input class="form-check-input" type="checkbox" name="master_box" title="Выбрать все" onclick="javascript:check_uncheck_all(subscribe_comments)"/>
							</th>
						</tr>
					</thead>
					<tbody>
						{{ entries|raw }}
					</tbody>
				</table>
			</div>
			<div class="d-flex justify-content-between align-items-center mt-3">
				<div class="form-inline">
					<label class="mr-2">Действие:</label>
					<select name="subaction" class="form-control mr-2">
						<option value="">-- Действие --</option>
						<option value="mass_delete">Удалить подписку</option>
					</select>
					<button type="submit" class="btn btn-primary">Выполнить</button>
				</div>
				<div>{{ pagesss|raw }}</div>
			</div>
		</div>
	</div>
</form>
