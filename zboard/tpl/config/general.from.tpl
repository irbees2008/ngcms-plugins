<form method="post" action="admin.php?mod=extra-config&plugin=zboard">
	<div class="card mb-4">
		<div class="card-header bg-info text-white font-weight-bold">Настройки</div>
		<div class="card-body">
			<div class="form-row">
				<div class="form-group col-md-6">
					<label for="send_guest">Разрешить гостям добавлять объявления?</label>
					<select class="form-control" name="send_guest" id="send_guest">{send_guest}</select>
				</div>
				<div class="form-group col-md-6">
					<label for="count">Количество объявлений на странице</label>
					<input class="form-control" name="count" id="count" type="text" title="Количество объявлений на странице" value="{count}"/>
				</div>
			</div>
			<div class="form-row">
				<div class="form-group col-md-6">
					<label for="count_list">Количество объявлений на странице пользователя</label>
					<input class="form-control" name="count_list" id="count_list" type="text" title="Количество объявлений на странице пользователя" value="{count_list}"/>
				</div>
				<div class="form-group col-md-6">
					<label for="count_search">Количество объявлений на странице поиска</label>
					<input class="form-control" name="count_search" id="count_search" type="text" title="Количество объявлений на странице поиска" value="{count_search}"/>
				</div>
			</div>
			<div class="form-row">
				<div class="form-group col-md-6">
					<label for="description">Описание для главной объявлений</label>
					<input class="form-control" name="description" id="description" type="text" title="Описание для объявлений" value="{description}"/>
				</div>
				<div class="form-group col-md-6">
					<label for="keywords">Ключевые слова для главной объявлений</label>
					<input class="form-control" name="keywords" id="keywords" type="text" title="Ключевые слова для объявлений" value="{keywords}"/>
				</div>
			</div>
			<div class="form-group">
				<label for="info_send">Информация, выводимая после добавления</label>
				<textarea class="form-control" name="info_send" id="info_send" title="Информация, выводимая после добавления" rows="4">{info_send}</textarea>
			</div>
			<div class="form-group">
				<label for="info_edit">Информация выводимая после редактирования</label>
				<textarea class="form-control" name="info_edit" id="info_edit" title="Информация выводимая после редактирования" rows="4">{info_edit}</textarea>
			</div>
			<div class="form-row">
				<div class="form-group col-md-6">
					<label for="use_expired">Включить снятие объявления по истечении срока?</label>
					<select class="form-control" name="use_expired" id="use_expired">{use_expired}</select>
				</div>
				<div class="form-group col-md-6">
					<label for="list_period">Время жизни объявления</label>
					<input class="form-control" name="list_period" id="list_period" type="text" title="Время жизни объявления" value="{list_period}"/>
				</div>
			</div>
			<div class="form-row">
				<div class="form-group col-md-6">
					<label for="views_count">Учёт просмотра объявлений?</label>
					<select class="form-control" name="views_count" id="views_count">{views_count}</select>
				</div>
				<div class="form-group col-md-6">
					<label for="notice_mail">Уведомлять админа о новых объявлениях?</label>
					<select class="form-control" name="notice_mail" id="notice_mail">{notice_mail}</select>
				</div>
			</div>
		</div>
	</div>
	<div class="card mb-4">
		<div class="card-header bg-secondary text-white font-weight-bold">Шаблоны и уведомления</div>
		<div class="card-body">
			<div class="form-group">
				<label for="template_mail">Шаблон уведомлений по почте</label>
				<small class="form-text text-muted">Доступные теги: %announce_name%, %author%, %announce_description%, %announce_period%, %announce_contacts%, %date%</small>
				<textarea class="form-control" name="template_mail" id="template_mail" title="Шаблон уведомлений по почте" rows="4">{template_mail}</textarea>
			</div>
			<div class="form-row">
				<div class="form-group col-md-6">
					<label for="main_template">Главный шаблон для объявлений</label>
					<input class="form-control" name="main_template" id="main_template" type="text" title="Главный шаблон для объявлений" value="{main_template}"/>
					<small class="form-text text-muted">Если пусто, берётся основной шаблон
						<b>main.tpl</b>. Пример: создать template.tpl в ngcms/www/templates/default/ и добавить сюда только название
						<b>template</b>
						без расширения.</small>
				</div>
				<div class="form-group col-md-6">
					<label for="width_thumb">Ширина уменьшенной копии</label>
					<input class="form-control" name="width_thumb" id="width_thumb" type="text" title="Ширина уменьшенной копии" value="{width_thumb}"/>
				</div>
			</div>
			<div class="form-group">
				<label for="ext_image">Разрешённые расширения для изображений</label>
				<input class="form-control" name="ext_image" id="ext_image" type="text" title="Разрешённые расширения для изображений" value="{ext_image}"/>
				<small class="form-text text-muted">Формат:
					<b>*.jpg;*.jpeg;*.gif;*.png</b>
				</small>
			</div>
		</div>
	</div>
	<div class="card mb-4">
		<div class="card-header bg-warning text-dark font-weight-bold">Настройки reCaptcha</div>
		<div class="card-body">
			<div class="form-row">
				<div class="form-group col-md-4">
					<label for="use_recaptcha">Использовать reCaptcha?</label>
					<select class="form-control" name="use_recaptcha" id="use_recaptcha">{use_recaptcha}</select>
				</div>
				<div class="form-group col-md-4">
					<label for="public_key">Public Key</label>
					<input class="form-control" name="public_key" id="public_key" type="text" title="Public Key" value="{public_key}"/>
				</div>
				<div class="form-group col-md-4">
					<label for="private_key">Private Key</label>
					<input class="form-control" name="private_key" id="private_key" type="text" title="Private Key" value="{private_key}"/>
				</div>
			</div>
		</div>
	</div>
	<div class="card mb-4">
		<div class="card-header bg-success text-white font-weight-bold">Настройки админки</div>
		<div class="card-body">
			<div class="form-row">
				<div class="form-group col-md-6">
					<label for="admin_count">Количество объявлений на странице</label>
					<input class="form-control" name="admin_count" id="admin_count" type="text" title="Количество объявлений на странице" value="{admin_count}"/>
				</div>
				<div class="form-group col-md-6">
					<label for="date">Формат даты</label>
					<input class="form-control" name="date" id="date" type="text" title="Формат даты" value="{date}"/>
				</div>
			</div>
		</div>
	</div>
	<div class="card mb-4">
		<div class="card-header bg-primary text-white font-weight-bold">Настройки Pay2Pay</div>
		<div class="card-body">
			<div class="form-group">
				<label for="pay2pay_merchant_id">Идентификатор магазина в Pay2Pay</label>
				<input class="form-control" name="pay2pay_merchant_id" id="pay2pay_merchant_id" type="text" title="Идентификатор магазина в Pay2Pay" value="{pay2pay_merchant_id}"/>
			</div>
			<div class="form-group">
				<label for="pay2pay_secret_key">Секретный ключ</label>
				<input class="form-control" name="pay2pay_secret_key" id="pay2pay_secret_key" type="text" title="Секретный ключ" value="{pay2pay_secret_key}"/>
			</div>
			<div class="form-group">
				<label for="pay2pay_hidden_key">Скрытый ключ</label>
				<input class="form-control" name="pay2pay_hidden_key" id="pay2pay_hidden_key" type="text" title="Скрытый ключ" value="{pay2pay_hidden_key}"/>
			</div>
			<div class="form-group">
				<label for="pay2pay_test_mode">Тестовый режим?</label>
				<select class="form-control" name="pay2pay_test_mode" id="pay2pay_test_mode">{pay2pay_test_mode}</select>
			</div>
		</div>
	</div>
	<div class="card mb-4">
		<div class="card-header bg-primary text-white font-weight-bold">Настройки Robokassa</div>
		<div class="card-body">
			<div class="form-group">
				<label for="robokassa_login">Логин магазина (MerchantLogin)</label>
				<input class="form-control" name="robokassa_login" id="robokassa_login" type="text" value="{robokassa_login}"/>
			</div>
			<div class="form-group">
				<label for="robokassa_pass1">Пароль #1 (Pass1)</label>
				<input class="form-control" name="robokassa_pass1" id="robokassa_pass1" type="text" value="{robokassa_pass1}"/>
			</div>
			<div class="form-group">
				<label for="robokassa_pass2">Пароль #2 (Pass2)</label>
				<input class="form-control" name="robokassa_pass2" id="robokassa_pass2" type="text" value="{robokassa_pass2}"/>
			</div>
			<div class="form-group">
				<label for="robokassa_is_test">Тестовый режим?</label>
				<select class="form-control" name="robokassa_is_test" id="robokassa_is_test">{robokassa_is_test}</select>
			</div>
		</div>
	</div>
	<div class="text-center mb-4">
		<button name="submit" type="submit" class="btn btn-lg btn-primary">Сохранить</button>
	</div>
</form>
