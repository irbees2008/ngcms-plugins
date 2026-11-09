<form
	method="post" action="" class="container-fluid">
	<!-- Настройки админки -->
	<div class="card mb-4">
		<div class="card-header bg-light">
			<h5 class="mb-0">{{ lang['form.admin_settings'] }}</h5>
		</div>
		<div class="card-body">
			<div class="row mb-3">
				<div class="col-md-4">
					<label for="num_cat" class="form-label">{{ lang['form.num_cat'] }}</label>
				</div>
				<div class="col-md-8">
					{{ num_cat.error }}
					<input name="num_cat" type="text" class="form-control" id="num_cat" value="{{ num_cat.print }}">
				</div>
			</div>
			<div class="row mb-3">
				<div class="col-md-4">
					<label for="num_news" class="form-label">{{ lang['form.num_news'] }}</label>
				</div>
				<div class="col-md-8">
					{{ num_news.error }}
					<input name="num_news" type="text" class="form-control" id="num_news" value="{{ num_news.print }}">
				</div>
			</div>
			<div class="row">
				<div class="col-md-4">
					<label for="num_static" class="form-label">{{ lang['form.num_static'] }}</label>
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
			<h5 class="mb-0">{{ lang['form.title_settings'] }}</h5>
		</div>
		<div class="card-body">
			<div class="row mb-3">
				<div class="col-md-4">
					<label for="c_title" class="form-label">{{ lang['form.category_title'] }}</label>
					<small class="text-muted d-block">{{ lang['help.category_title'] }}</small>
				</div>
				<div class="col-md-8">
					{{ c_title.error }}
					<input name="c_title" type="text" class="form-control" id="c_title" value="{{ c_title.print }}">
				</div>
			</div>

			<div class="row mb-3">
				<div class="col-md-4">
					<label for="n_title" class="form-label">{{ lang['form.news_title'] }}</label>
					<small class="text-muted d-block">{{ lang['help.news_title'] }}</small>
				</div>
				<div class="col-md-8">
					{{ n_title.error }}
					<input name="n_title" type="text" class="form-control" id="n_title" value="{{ n_title.print }}">
				</div>
			</div>

			<div class="row mb-3">
				<div class="col-md-4">
					<label for="m_title" class="form-label">{{ lang['form.home_title'] }}</label>
					<small class="text-muted d-block">{{ lang['help.home_title'] }}</small>
				</div>
				<div class="col-md-8">
					{{ m_title.error }}
					<input name="m_title" type="text" class="form-control" id="m_title" value="{{ m_title.print }}">
				</div>
			</div>

			<div class="row mb-3">
				<div class="col-md-4">
					<label for="static_title" class="form-label">{{ lang['form.static_title'] }}</label>
					<small class="text-muted d-block">{{ lang['help.static_title'] }}</small>
				</div>
				<div class="col-md-8">
					{{ static_title.error }}
					<input name="static_title" type="text" class="form-control" id="static_title" value="{{ static_title.print }}">
				</div>
			</div>

			<div class="row mb-3">
				<div class="col-md-4">
					<label for="o_title" class="form-label">{{ lang['form.other_title'] }}</label>
					<small class="text-muted d-block">{{ lang['help.other_title'] }}</small>
				</div>
				<div class="col-md-8">
					{{ o_title.error }}
					<input name="o_title" type="text" class="form-control" id="o_title" value="{{ o_title.print }}">
				</div>
			</div>

			<div class="row mb-3">
				<div class="col-md-4">
					<label for="html_secure" class="form-label">{{ lang['form.additional_info'] }}</label>
					<small class="text-muted d-block">{{ lang['help.additional_info'] }}</small>
				</div>
				<div class="col-md-8">
					{{ html_secure.error }}
					<input name="html_secure" type="text" class="form-control" id="html_secure" value="{{ html_secure.print }}">
				</div>
			</div>

			<div class="row mb-3">
				<div class="col-md-4">
					<label for="e_title" class="form-label">{{ lang['form.not_found'] }}</label>
				</div>
				<div class="col-md-8">
					{{ e_title.error }}
					<input name="e_title" type="text" class="form-control" id="e_title" value="{{ e_title.print }}">
				</div>
			</div>

			<div class="row mb-3">
				<div class="col-md-4">
					<label for="p_title" class="form-label">{{ lang['form.excluded_plugins'] }}</label>
					<small class="text-muted d-block">{{ lang['help.plugins'] }}</small>
				</div>
				<div class="col-md-8">
					{{ p_title.error }}
					<input name="p_title" type="text" class="form-control" id="p_title" value="{{ p_title.print }}">
				</div>
			</div>

			<div class="row">
				<div class="col-md-4">
					<label for="num_title" class="form-label">{{ lang['form.page_number'] }}</label>
					<small class="text-muted d-block">{{ lang['help.page_number'] }}</small>
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
			<h5 class="mb-0">{{ lang['form.title_keys'] }}</h5>
		</div>
		<div class="card-body">
			<ul class="list-unstyled">
				<li>
					<strong>%cat%</strong>
					- {{ lang['help.key_category'] }}</li>
				<li>
					<strong>%title%</strong>
					- {{ lang['help.key_news'] }}</li>
				<li>
					<strong>%home%</strong>
					- {{ lang['help.key_home'] }}</li>
				<li>
					<strong>%static%</strong>
					- {{ lang['help.key_static'] }}</li>
				<li>
					<strong>%other%</strong>
					- {{ lang['help.key_other'] }}</li>
			</ul>
		</div>
	</div>

	<!-- Настройка кэша -->
	<div class="card mb-4">
		<div class="card-header bg-light">
			<h5 class="mb-0">{{ lang['form.cache_settings'] }}</h5>
		</div>
		<div class="card-body">
			<div class="row">
				<div class="col-md-4">
					<label for="cache" class="form-label">{{ lang['form.cache_lifetime'] }}</label>
					<small class="text-muted d-block">{{ lang['form.cache_days'] }}</small>
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
		<button name="submit" type="submit" class="btn btn-primary px-4">{{ lang['btn.save'] }}</button>
	</div>
</form>
