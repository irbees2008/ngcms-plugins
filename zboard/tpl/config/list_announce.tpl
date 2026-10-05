<form action="/engine/admin.php?mod=extra-config&plugin=zboard&action=modify" method="post" name="zboard">
	<div class="table-responsive">
		<table class="table table-bordered table-hover table-sm align-middle">
			<thead class="thead-light">
				<tr>
					<th scope="col">ID</th>
					<th scope="col">{l_zboard:admin_date}</th>
					<th scope="col">{l_zboard:admin_category}</th>
					<th scope="col">{l_zboard:admin_title_label}</th>
					<th scope="col">{l_zboard:admin_author}</th>
					<th scope="col">{l_zboard:admin_period}</th>
					<th scope="col">{l_zboard:admin_active}</th>
					<th scope="col" class="text-center" style="width:36px;">
						<input type="checkbox" name="master_box" title="{l_zboard:admin_select_all}" onclick="javascript:check_uncheck_all(zboard)" style="margin:0;"/>
					</th>
				</tr>
			</thead>
			<tbody>
				{entries}
			</tbody>
		</table>
	</div>
	<div class="row mb-3">
		<div class="col-md-6">
			<div class="form-inline">
				<label class="mr-2" for="subaction">{l_zboard:admin_action}:</label>
				<select name="subaction" id="subaction" class="form-control mr-2">
					<option value="">{l_zboard:admin_choose_action}</option>
					<option value="mass_approve">{l_zboard:admin_activate}</option>
					<option value="mass_forbidden">{l_zboard:admin_deactivate}</option>
					<option value="" disabled>===================</option>
					<option value="mass_delete">{l_zboard:admin_delete_announcement}</option>
				</select>
				<button type="submit" class="btn btn-primary ml-2">{l_zboard:admin_execute}</button>
			</div>
		</div>
		<div class="col-md-6 text-right">
			<nav aria-label="{l_zboard:admin_page_navigation}">
				<span class="pagination pagination-sm mb-0">{pagesss}</span>
			</nav>
		</div>
	</div>
</form>
