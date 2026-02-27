<div class="container-fluid">
	<div class="row mb-2">
		<div class="col-sm-6">
			<h1 class="m-0 text-dark" style="padding: 20px 0 0 0;">re_stat</h1>
		</div>
		<div class="col-sm-6">
			<ol class="breadcrumb float-sm-right">
				<li class="breadcrumb-item">
					<a href="admin.php">
						<i class="fa fa-home"></i>
					</a>
				</li>
				<li class="breadcrumb-item">
					<a href="admin.php?mod=extras">{{ l_extras }}</a>
				</li>
				<li class="breadcrumb-item active" aria-current="page">re_stat</li>
			</ol>
		</div>
	</div>
</div>
<form action="admin.php?mod=extra-config&amp;plugin=re_stat" method="post" name="options_bar">
	<input type="hidden" name="action" value="">
	<input type="hidden" name="id" value="-1">
	<div class="container-fluid">
		<div class="row mb-2">
			<div class="col-sm-12 text-center">
				<div class="btn-group" role="group">
					<input type="submit" value="{{ lbl_list }}" class="btn btn-outline-primary" onclick="document.forms['options_bar'].action.value = '';">
					<input type="submit" value="{{ lbl_add }}" class="btn btn-outline-primary" onclick="document.forms['options_bar'].action.value = 'add';">
				</div>
			</div>
		</div>
	</div>
</form>
<div class="container-fluid">
	<div class="col-sm-12 mt-2">
		<div class="card">
			<div class="card-header">re_stat</div>
			<div class="card-body">
				<table class="table table-sm table-bordered table-hover">
					<thead>
						<tr>
							<td>{{ lbl_col_no }}</td>
							<td>{{ lbl_col_code }}</td>
							<td>{{ lbl_col_page }}</td>
							<td width="160">{{ lbl_col_action }}</td>
						</tr>
					</thead>
					<tbody>
						{% for entry in entries %}
							<tr>
								<td>{{ entry.no }}</td>
								<td>{{ entry.code }}
									{{ entry.error|raw }}</td>
								<td>{{ entry.title|raw }}</td>
								<td>
									<a href="admin.php?mod=extra-config&amp;plugin=re_stat&amp;action=edit&amp;id={{ entry.id }}" title="{{ lbl_edit }}">
										<i class="fa fa-pencil-square-o fa-lg" aria-hidden="true" style="color: green;"></i>
									</a>
									<a href="admin.php?mod=extra-config&amp;plugin=re_stat&amp;action=delete&amp;id={{ entry.id }}" title="{{ lbl_delete }}">
										<i class="fa fa-times fa-lg" aria-hidden="true" style="color: red;"></i>
									</a>
								</td>
							</tr>
						{% else %}
							<tr>
								<td colspan="4" class="text-center text-muted">—</td>
							</tr>
						{% endfor %}
					</tbody>
				</table>
			</div>
			<form action="admin.php?mod=extra-config&amp;plugin=re_stat" method="post">
				<input type="hidden" name="action" value="re_map">
				<div class="card-footer text-center">
					<input type="submit" value="{{ lbl_remap }}" class="btn btn-outline-success">
				</div>
			</form>
		</div>
	</div>
</div>
