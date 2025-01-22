<form method="post" action="">
	<!-- Общие настройки -->
	<fieldset class="border p-3 mb-4">
		<legend class="w-auto">Общие настройки</legend>
		<div class="form-group row">
			<label for="user_per_page" class="col-sm-4 col-form-label"
				>Кол-во пользователей для отображения на одной странице</label
			>
			<div class="col-sm-8">
				<input
					type="text"
					class="form-control"
					id="user_per_page"
					name="user_per_page"
					title="Кол-во пользователей для отображения на одной странице"
					value="{user_per_page}"
					size="5" />
			</div>
		</div>
		<div class="form-group row">
			<label for="title_plg" class="col-sm-4 col-form-label"
				>Title для страницы плагина</label
			>
			<div class="col-sm-8">
				<input
					type="text"
					class="form-control"
					id="title_plg"
					name="title_plg"
					title="Описание"
					value="{title_plg}"
					size="50" />
			</div>
		</div>
		<div class="form-group row">
			<label for="description" class="col-sm-4 col-form-label"
				>Описание для страницы плагина</label
			>
			<div class="col-sm-8">
				<input
					type="text"
					class="form-control"
					id="description"
					name="description"
					title="Описание"
					value="{description}"
					size="50" />
			</div>
		</div>
		<div class="form-group row">
			<label for="keywords" class="col-sm-4 col-form-label"
				>Ключевые слова для страницы плагина</label
			>
			<div class="col-sm-8">
				<input
					type="text"
					class="form-control"
					id="keywords"
					name="keywords"
					title="Ключевые слова"
					value="{keywords}"
					size="50" />
			</div>
		</div>
	</fieldset>

	<!-- Настройки отображения -->
	<fieldset class="border p-3 mb-4">
		<legend class="w-auto">Настройки отображения</legend>
		<div class="form-group row">
			<label for="localsource" class="col-sm-4 col-form-label"
				>Выберите каталог из которого плагин будет брать шаблоны для
				отображения</label
			>
			<div class="col-sm-8">
				<select class="form-control" id="localsource" name="localsource">
					{localsource}
				</select>
				<small class="form-text text-muted">
					<b>Шаблон сайта</b>
					- плагин будет пытаться взять шаблоны из общего шаблона сайта; в
					случае недоступности - шаблоны будут взяты из собственного каталога
					плагина<br />
					<b>Плагин</b>
					- шаблоны будут браться из собственного каталога плагина
				</small>
			</div>
		</div>
	</fieldset>

	<!-- Кнопка Сохранить -->
	<div class="row">
		<div class="col text-center">
			<button name="submit" type="submit" class="btn btn-primary">
				Сохранить
			</button>
		</div>
	</div>
</form>
