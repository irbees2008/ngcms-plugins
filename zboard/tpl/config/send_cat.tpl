{error}
<form method="post" action="">
	<div class="card mb-4">
		<div class="card-header bg-info text-white font-weight-bold">{l_zboard:admin_add_category}</div>
		<div class="card-body">
			<div class="form-group">
				<label for="cat_name">{l_zboard:admin_name}</label>
				<input type="text" class="form-control" id="cat_name" name="cat_name" value="{cat_name}"/>
			</div>
			<div class="form-group">
				<label for="description">{l_zboard:admin_description}</label>
				<input type="text" class="form-control" id="description" name="description" value="{description}"/>
			</div>
			<div class="form-group">
				<label for="keywords">{l_zboard:admin_keywords}</label>
				<input type="text" class="form-control" id="keywords" name="keywords" value="{keywords}"/>
			</div>
			<div class="form-group">
				<label for="parent">{l_zboard:admin_parent_category}</label>
				<select class="form-control" id="parent" name="parent">
					<option value="0">{l_zboard:admin_choose_category}</option>
					{catz}
				</select>
			</div>
			<div class="text-center">
				<button type="submit" name="submit" class="btn btn-success">{l_zboard:admin_add_category}</button>
			</div>
		</div>
	</div>
</form>
