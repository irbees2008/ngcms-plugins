<div class="col-sm-12 mt-2">
	<div class="card">
		<div class="card-header">{{ table_title }}</div>
		<div class="card-body">
			<table class="table table-sm table-bordered table-hover">
				<thead>
					<tr>
						<th scope="col">{{ lang['category_access:label_category'] }}</th>
						<th scope="col">{{ lang['category_access:label_action'] }}</th>
					</tr>
				</thead>
				{{ entries|raw }}
			</table>
		</div>
	</div>
</div>
