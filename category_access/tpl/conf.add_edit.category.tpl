<form method="post" action="{{ admin_url }}/admin.php?mod=extra-config&plugin=category_access&action=add_category">
	<div class="col-sm-12 mt-2">
		<div class="card">
			<div class="card-header">{{ table_title }}</div>
			<div class="card-body">
				<table class="table table-sm table-bordered table-hover">
					<thead>
						<tr>
							<td>{{ lang['category_access:label_category'] }}</td>
							<td>{{ lang['category_access:label_add'] }}</td>
						</tr>
					</thead>
					{{ entries|raw }}
				</table>
			</div>
		</div>
	</div>
	<div class="col-sm-12 text-center mt-2">
		<div class="card">
			<div class="card-body">
				<input type="submit" value="{{ lang['category_access:button_add_category'] }}" class="btn btn-outline-success"/>
			</div>
		</div>
	</div>
</form>
