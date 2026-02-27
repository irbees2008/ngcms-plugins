<form method="post" id="commit_delete" action="{{ admin_url }}/admin.php?mod=extra-config&plugin=category_access&action=dell_user">
	<input type="hidden" name="user" value="{{ user }}"/>
	<input type="hidden" id="commit" name="commit" value="no"/>
	<div class="col-sm-12 mt-2">
		<div class="card">
			<div class="card-header">{{ lang['category_access:title_commit'] }}</div>
			<div class="card-body text-center">
				<span style="color: red; font-size: 1.5rem;">{{ commit|raw }}</span>
			</div>
		</div>
	</div>
	<div class="col-sm-12 text-center mt-2">
		<div class="card">
			<div class="card-body">
				<input type="submit" value="{{ lang['category_access:button_cancel'] }}" class="btn btn-outline-success"/>
				<input type="submit" onclick="document.getElementById('commit').value='yes'; return true;" value="{{ lang['category_access:button_dell'] }}" class="btn btn-outline-danger ml-2"/>
			</div>
		</div>
	</div>
</form>
