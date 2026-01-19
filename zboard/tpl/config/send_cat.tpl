{error}
<form method="post" action="">
	<div class="card mb-4">
		<div class="card-header bg-info text-white font-weight-bold">Добавить категорию</div>
		<div class="card-body">
			<div class="form-group">
				<label for="cat_name">Имя</label>
				<input type="text" class="form-control" id="cat_name" name="cat_name" value="{cat_name}"/>
			</div>
			<div class="form-group">
				<label for="description">Описание</label>
				<input type="text" class="form-control" id="description" name="description" value="{description}"/>
			</div>
			<div class="form-group">
				<label for="keywords">Ключевые слова</label>
				<input type="text" class="form-control" id="keywords" name="keywords" value="{keywords}"/>
			</div>
			<div class="form-group">
				<label for="parent">Родительская категория</label>
				<select class="form-control" id="parent" name="parent">
					<option value="0">Выберите категорию</option>
					{catz}
				</select>
			</div>
			<div class="text-center">
				<button type="submit" name="submit" class="btn btn-success">Добавить категорию</button>
			</div>
		</div>
	</div>
</form>
