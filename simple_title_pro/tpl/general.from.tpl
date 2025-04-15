<form
	method="post" action="" class="container-fluid">
	<!-- Настройки админки -->
	<div class="card mb-4">
		<div class="card-header bg-light">
			<h5 class="mb-0">Настройки админки</h5>
		</div>
		<div class="card-body">
			<div class="row mb-3">
				<div class="col-md-4">
					<label for="num_cat" class="form-label">Количество записей в категории</label>
				</div>
				<div class="col-md-8">
					{{ num_cat.error }}
					<input name="num_cat" type="text" class="form-control" id="num_cat" value="{{ num_cat.print }}">
				</div>
			</div>
			<div class="row mb-3">
				<div class="col-md-4">
					<label for="num_news" class="form-label">Количество записей в новостях</label>
				</div>
				<div class="col-md-8">
					{{ num_news.error }}
					<input name="num_news" type="text" class="form-control" id="num_news" value="{{ num_news.print }}">
				</div>
			</div>
			<div class="row">
				<div class="col-md-4">
					<label for="num_static" class="form-label">Количество записей в статике</label>
				</div>
				<div class="col-md-8">
					{{ num_static.error }}
					<input name="num_static" type="text" class="form-control" id="num_static" value="{{ num_static.print }}">
				</div>
			</div>
		</div>
	</div>

	<!-- Настройки <title> -->
	<div class="card mb-4">
		<div class="card-header bg-light">
			<h5 class="mb-0">Настройки &lt;title&gt;&lt;/title&gt;</h5>
		</div>
		<div class="card-body">
			<div class="row mb-3">
				<div class="col-md-4">
					<label for="c_title" class="form-label">Заголовок в категории</label>
					<small class="text-muted d-block">Разрешено %cat%, %num% и %home%</small>
				</div>
				<div class="col-md-8">
					{{ c_title.error }}
					<input name="c_title" type="text" class="form-control" id="c_title" value="{{ c_title.print }}">
				</div>
			</div>

			<div class="row mb-3">
				<div class="col-md-4">
					<label for="n_title" class="form-label">Заголовок в полной новости</label>
					<small class="text-muted d-block">Разрешено %cat%, %title%, %home%, %num%</small>
				</div>
				<div class="col-md-8">
					{{ n_title.error }}
					<input name="n_title" type="text" class="form-control" id="n_title" value="{{ n_title.print }}">
				</div>
			</div>

			<div class="row mb-3">
				<div class="col-md-4">
					<label for="m_title" class="form-label">Заголовок главной страницы</label>
					<small class="text-muted d-block">Разрешено %home% %num%</small>
				</div>
				<div class="col-md-8">
					{{ m_title.error }}
					<input name="m_title" type="text" class="form-control" id="m_title" value="{{ m_title.print }}">
				</div>
			</div>

			<div class="row mb-3">
				<div class="col-md-4">
					<label for="static_title" class="form-label">Заголовок статической страницы</label>
					<small class="text-muted d-block">Разрешено %home% и %static%</small>
				</div>
				<div class="col-md-8">
					{{ static_title.error }}
					<input name="static_title" type="text" class="form-control" id="static_title" value="{{ static_title.print }}">
				</div>
			</div>

			<div class="row mb-3">
				<div class="col-md-4">
					<label for="o_title" class="form-label">Заголовок остальных страниц</label>
					<small class="text-muted d-block">Разрешено %home%, %other%, %html% и %num%</small>
				</div>
				<div class="col-md-8">
					{{ o_title.error }}
					<input name="o_title" type="text" class="form-control" id="o_title" value="{{ o_title.print }}">
				</div>
			</div>

			<div class="row mb-3">
				<div class="col-md-4">
					<label for="html_secure" class="form-label">Дополнительная информация для страницы</label>
					<small class="text-muted d-block">Передаётся в переменную %html%</small>
				</div>
				<div class="col-md-8">
					{{ html_secure.error }}
					<input name="html_secure" type="text" class="form-control" id="html_secure" value="{{ html_secure.print }}">
				</div>
			</div>

			<div class="row mb-3">
				<div class="col-md-4">
					<label for="e_title" class="form-label">Страница ошибки 404</label>
				</div>
				<div class="col-md-8">
					{{ e_title.error }}
					<input name="e_title" type="text" class="form-control" id="e_title" value="{{ e_title.print }}">
				</div>
			</div>

			<div class="row mb-3">
				<div class="col-md-4">
					<label for="p_title" class="form-label">Плагины исключения</label>
					<small class="text-muted d-block">Список плагинов через запятую</small>
				</div>
				<div class="col-md-8">
					{{ p_title.error }}
					<input name="p_title" type="text" class="form-control" id="p_title" value="{{ p_title.print }}">
				</div>
			</div>

			<div class="row">
				<div class="col-md-4">
					<label for="num_title" class="form-label">Номер страницы</label>
					<small class="text-muted d-block">Используйте %count% для номера страницы</small>
				</div>
				<div class="col-md-8">
					{{ num_title.error }}
					<input name="num_title" type="text" class="form-control" id="num_title" value="{{ num_title.print }}">
				</div>
			</div>
		</div>
	</div>

	<!-- Справка по ключам -->
	<div class="card mb-4">
		<div class="card-header bg-light">
			<h5 class="mb-0">Ключи для заголовков</h5>
		</div>
		<div class="card-body">
			<ul class="list-unstyled">
				<li>
					<strong>%cat%</strong>
					- имя категории</li>
				<li>
					<strong>%title%</strong>
					- имя новости</li>
				<li>
					<strong>%home%</strong>
					- заголовок сайта</li>
				<li>
					<strong>%static%</strong>
					- заголовок статической страницы</li>
				<li>
					<strong>%other%</strong>
					- заголовок любой другой страницы</li>
			</ul>
		</div>
	</div>

	<!-- Настройка кэша -->
	<div class="card mb-4">
		<div class="card-header bg-light">
			<h5 class="mb-0">Настройка кэша</h5>
		</div>
		<div class="card-body">
			<div class="row">
				<div class="col-md-4">
					<label for="cache" class="form-label">Время жизни кэша</label>
					<small class="text-muted d-block">Указывать в днях</small>
				</div>
				<div class="col-md-8">
					{{ cache.error }}
					<input name="cache" type="text" class="form-control" id="cache" value="{{ cache.print }}">
				</div>
			</div>
		</div>
	</div>

	<!-- Кнопка отправки -->
	<div class="text-center mb-4">
		<button name="submit" type="submit" class="btn btn-primary px-4">Сохранить</button>
	</div>
</form>
