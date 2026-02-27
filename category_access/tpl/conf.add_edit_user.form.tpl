<form method="post" class="form-group" action="{{ admin_url }}/admin.php?mod=extra-config&plugin=category_access&action=add_user{% if not is_add %}&user={{ user|url_encode }}{% endif %}">
	<div class="row mt-2">
		<div class="col-sm-12 mt-2">
			<div class="card">
				<div class="card-header">{{ form_title }}</div>
				<div class="card-body">
					<table class="table table-hover">
						<tr>
							<td>{{ lang['category_access:label_user_name'] }}<br/>
								<small>{{ lang['category_access:desc_user'] }}</small>
							</td>
							<td>{{ user_list|raw }}</td>
						</tr>
						<tr>
							<td>{{ lang['category_access:label_category'] }}<br/>
								<small>{{ lang['category_access:desc_category'] }}</small>
							</td>
							<td>{{ category_list|raw }}</td>
						</tr>
					</table>
				</div>
			</div>
		</div>
		<div class="col-sm-12 text-center mt-2">
			<div class="card">
				<div class="card-body">
					<input type="submit" value="{{ submit_label }}" class="btn btn-outline-success"/>
				</div>
			</div>
		</div>
	</div>
</form>
