<div class="container-fluid">
	<div class="row mb-2">
		<div class="col-sm-6">
			<h1 class="m-0 text-dark" style="padding: 20px 0 0 0;">{{ lang['comments:moderation.title'] }}</h1>
		</div>
		<div class="col-sm-6">
			<ol class="breadcrumb float-sm-right">
				<li class="breadcrumb-item">
					<a href="admin.php">
						<i class="fa fa-home"></i>
					</a>
				</li>
				<li class="breadcrumb-item">
					<a href="admin.php?mod=extras">{{ lang['comments:moderation.plugins'] }}</a>
				</li>
				<li class="breadcrumb-item active" aria-current="page">{{ lang['comments:moderation.breadcrumb'] }}</li>
			</ol>
		</div>
	</div>
</div>
<div class="container-fluid">
	<form method="post" action="{{ php_self }}?plugin=comments&handler=moderation">
		<div class="card mb-5">
			<div class="table-responsive">
				<table class="table table-sm">
					<thead>
						<tr>
							<th nowrap>{{ lang['comments:moderation.author'] }}</th>
							<th nowrap>{{ lang['comments:moderation.comment'] }}</th>
							<th nowrap>{{ lang['comments:moderation.news'] }}</th>
							<th nowrap>{{ lang['comments:moderation.date'] }}</th>
							<th nowrap>
								<input type="checkbox" name="master_box" title="{{ lang['comments:moderation.select_all'] }}" onclick="javascript:check_uncheck_all(this.form, 'comments')"/>
							</th>
						</tr>
					</thead>
					<tbody>
						{% if count > 0 %}
							{% for comment in comments %}
								<tr>
									<td>{{ comment.author }}</td>
									<td>{{ comment.text_preview }}</td>
									<td><a href="{{ comment.news_link }}" target="_blank">{{ comment.news_title }}</a></td>
									<td>{{ comment.date_formatted }}</td>
									<td><input type="checkbox" name="comments[]" value="{{ comment.id }}"/></td>
								</tr>
							{% endfor %}
						{% else %}
							<tr>
								<td colspan="5" class="text-center text-muted">{{ lang['comments:moderation.no_comments'] }}</td>
							</tr>
						{% endif %}
					</tbody>
					<tfoot>
						<tr>
							<th nowrap>{{ lang['comments:moderation.author'] }}</th>
							<th nowrap>{{ lang['comments:moderation.comment'] }}</th>
							<th nowrap>{{ lang['comments:moderation.news'] }}</th>
							<th nowrap>{{ lang['comments:moderation.date'] }}</th>
							<th nowrap>
								<input type="checkbox" name="master_box" title="{{ lang['comments:moderation.select_all'] }}" onclick="javascript:check_uncheck_all(this.form, 'comments')"/>
							</th>
						</tr>
						{% if count > 0 %}
						<tr>
							<td colspan="5">
								<div class="d-flex flex-wrap gap-2 justify-content-center">
									<div class="col-md-auto">
										<div class="btn-group" role="group">
											<button type="submit" name="action" value="approve" class="btn btn-outline-success" onclick="return confirm('{{ lang['comments:moderation.approve_confirm'] }}')">{{ lang['comments:moderation.approve'] }}</button>
											<button type="submit" name="action" value="delete" class="btn btn-outline-danger" onclick="return confirm('{{ lang['comments:moderation.delete_confirm'] }}')">{{ lang['comments:moderation.delete'] }}</button>
										</div>
									</div>
								</div>
							</td>
						</tr>
						{% endif %}
					</tfoot>
				</table>
			</div>
		</div>
	</form>
	<script>
		function check_uncheck_all(form, checkboxName) {
			const checkboxes = form.elements['comments[]'];
			const isChecked = event.target.checked;
			if (checkboxes) {
				if (checkboxes.length) {
					for (let i = 0; i < checkboxes.length; i++) {
						checkboxes[i].checked = isChecked;
					}
				} else {
					checkboxes.checked = isChecked;
				}
			}
			// Синхронизация двух master-чекбоксов
			const masterBoxes = form.querySelectorAll('[name^="master_box"]');
			masterBoxes.forEach(box => {
				if (box !== event.target) {
					box.checked = isChecked;
				}
			});
		}
	</script>
</div>
