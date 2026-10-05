<form method="post" action="" enctype="multipart/form-data">
	<fieldset class="admGroup">
		<legend class="title">{{ lang.main_information }}</legend>
		<div class="table-responsive">
			<table class="table table-bordered">
				<tbody>
					<tr>
						<th scope="row" class="align-middle">{{ lang.campaign_title }}</th>
						<td><input name="title" type="text" class="form-control" placeholder="{{ lang.optional }}"/></td>
					</tr>
					<tr>
						<th scope="row" class="align-middle">{{ lang.subject }}
							<span class="text-danger">*</span>
						</th>
						<td><input name="subject" type="text" class="form-control" required/></td>
					</tr>
					<tr>
						<th scope="row" class="align-middle">
							{{ lang.html_content }}
							<br><small>{{ lang.unsubscribe_placeholder }}</small>
						</th>
						<td>
							<textarea name="body_html" class="form-control" rows="12" placeholder="{{ lang.html_email_placeholder }}"></textarea>
						</td>
					</tr>
					<tr>
						<th scope="row" class="align-middle">
							{{ lang.text_version }}
							<br><small>{{ lang.text_version_help }}</small>
						</th>
						<td>
							<textarea name="body_text" class="form-control" rows="6" placeholder="{{ lang.text_email_placeholder }}"></textarea>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</fieldset>

	<fieldset class="admGroup">
		<legend class="title">{{ lang.recipients_segment }}</legend>
		<div class="table-responsive">
			<table class="table table-bordered">
				<tbody>
					<tr>
						<th scope="row" class="align-middle">
							{{ lang.user_group_ids }}
							<br><small>{{ lang.user_group_ids_help }}</small>
						</th>
						<td><input name="groups_csv" type="text" class="form-control" placeholder="1,2"/></td>
					</tr>
					<tr>
						<th scope="row" class="align-middle">{{ lang.only_active_users }}</th>
						<td>
							<label><input type="checkbox" name="only_active" checked>
								{{ lang.yes_option }}</label>
						</td>
					</tr>
					<tr>
						<th scope="row" class="align-middle">
							{{ lang.recipient_limit }}
							<br><small>{{ lang.zero_unlimited }}</small>
						</th>
						<td><input name="limit" type="text" class="form-control" value="0"/></td>
					</tr>
				</tbody>
			</table>
		</div>
	</fieldset>

	<fieldset class="admGroup">
		<legend class="title">{{ lang.send_parameters }}</legend>
		<div class="table-responsive">
			<table class="table table-bordered">
				<tbody>
					<tr>
						<th scope="row" class="align-middle">
							{{ lang.scheduled_send }}
							<br><small>{{ lang.unix_timestamp_help }}</small>
						</th>
						<td><input name="send_at_ts" type="text" class="form-control" value="0"/></td>
					</tr>
					<tr>
						<th scope="row" class="align-middle">{{ lang.attachments }}</th>
						<td><input name="attachments[]" type="file" class="form-control" multiple/></td>
					</tr>
				</tbody>
			</table>
		</div>
	</fieldset>

	<input type="hidden" name="created_by" value="0">

	<div class="card-footer text-center">
		<button class="btn btn-success" type="submit" name="create_campaign" value="1">{{ lang.create_and_queue }}</button>
	</div>
</form>

 <script>
document.querySelector("form").addEventListener("submit", function(e){
	var csv = document.querySelector("[name=groups_csv]").value || "";
	var arr = csv.split(",").map(s=>parseInt(s.trim(),10)).filter(n=>!isNaN(n));
	arr.forEach(function(n){
		var i = document.createElement("input");
		i.type="hidden"; i.name="groups[]"; i.value=String(n);
		e.target.appendChild(i);
	});
});
</script>
