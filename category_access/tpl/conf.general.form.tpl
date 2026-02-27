<form method="post" action="{{ admin_url }}/admin.php?mod=extra-config&plugin=category_access&action=general_submit">
	<div class="row mt-2">
		<div class="col-sm">
			<div class="card">
				<div class="card-header">{{ lang['category_access:legend_general'] }}</div>
				<div class="card-body">
					<table class="table table-hover">
						<tr>
							<td>{{ lang['category_access:label_guest'] }}<br/>
								<small>{{ lang['category_access:desc_guest']|raw }}</small>
							</td>
							<td>{{ guest_list|raw }}</td>
						</tr>
						<tr>
							<td>{{ lang['category_access:label_coment'] }}<br/>
								<small>{{ lang['category_access:desc_coment']|raw }}</small>
							</td>
							<td>{{ coment_list|raw }}</td>
						</tr>
						<tr>
							<td>{{ lang['category_access:label_journ'] }}<br/>
								<small>{{ lang['category_access:desc_journ']|raw }}</small>
							</td>
							<td>{{ journ_list|raw }}</td>
						</tr>
						<tr>
							<td>{{ lang['category_access:label_moder'] }}<br/>
								<small>{{ lang['category_access:desc_moder']|raw }}</small>
							</td>
							<td>{{ moder_list|raw }}</td>
						</tr>
						<tr>
							<td>{{ lang['category_access:label_admin'] }}<br/>
								<small>{{ lang['category_access:desc_admin']|raw }}</small>
							</td>
							<td>{{ admin_list|raw }}</td>
						</tr>
					</table>
				</div>
			</div>
		</div>
		<div class="col-sm-6">
			<div class="card">
				<div class="card-header">{{ lang['category_access:legend_general_text'] }}</div>
				<div class="card-body">
					<textarea name="message" cols="150" rows="15">{{ message|e }}</textarea>
				</div>
			</div>
		</div>
		<div class="col-sm-12 text-center mt-2">
			<div class="card">
				<div class="card-body">
					<input type="submit" value="{{ lang['category_access:button_save'] }}" class="btn btn-outline-success"/>
				</div>
			</div>
		</div>
	</div>
</form>
