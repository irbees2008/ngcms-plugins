<style>
	/* Стили для табов */
	.parser-tabs {
		display: flex;
		border-bottom: 2px solid #dee2e6;
		margin-bottom: 20px;
		flex-wrap: wrap;
	}
	.parser-tab {
		padding: 12px 24px;
		cursor: pointer;
		border: none;
		background: none;
		font-size: 15px;
		font-weight: 500;
		color: #6c757d;
		border-bottom: 3px solid transparent;
		transition: all 0.3s;
		white-space: nowrap;
	}
	.parser-tab:hover {
		color: #007bff;
		background: #f8f9fa;
	}
	.parser-tab.active {
		color: #007bff;
		border-bottom-color: #007bff;
		background: #f8f9fa;
	}
	.parser-tab-content {
		display: none;
	}
	.parser-tab-content.active {
		display: block;
		animation: fadeIn 0.3s;
	}
	@keyframes fadeIn {
		from { opacity: 0; transform: translateY(-10px); }
		to { opacity: 1; transform: translateY(0); }
	}

	.ui-progressbar {
		position: relative;
	}
	.progress-label {
		position: absolute;
		left: 50%;
		top: 0;
		font-weight: bold;
		text-shadow: 1px 1px 0 #fff;
	}
	/* Стиль для сообщений */
	.message {
		margin-top: 10px;
		padding: 10px;
		border-radius: 4px;
		display: none; /* Сообщение скрыто по умолчанию */
	}
	.message.success {
		background-color: #d4edda;
		color: #155724;
		border: 1px solid #c3e6cb;
	}
	.message.error {
		background-color: #f8d7da;
		color: #721c24;
		border: 1px solid #f5c6cb;
	}

	/* Модальное окно Telegram авторизации */
	#telegram-auth-modal {
		position: fixed;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		background: rgba(0, 0, 0, 0.5);
		z-index: 9999;
		display: flex;
		align-items: center;
		justify-content: center;
	}
	#telegram-auth-modal .modal-dialog {
		max-width: 500px;
		width: 90%;
	}
	#telegram-auth-modal .modal-content {
		background: white;
		border-radius: 8px;
		box-shadow: 0 5px 15px rgba(0,0,0,0.3);
	}
	#telegram-auth-modal .modal-header {
		padding: 15px;
		border-bottom: 1px solid #dee2e6;
		display: flex;
		justify-content: space-between;
		align-items: center;
	}
	#telegram-auth-modal .modal-title {
		margin: 0;
		font-size: 1.25rem;
	}
	#telegram-auth-modal .close {
		background: none;
		border: none;
		font-size: 1.5rem;
		cursor: pointer;
		opacity: 0.5;
	}
	#telegram-auth-modal .close:hover {
		opacity: 1;
	}
	#telegram-auth-modal .modal-body {
		padding: 20px;
	}
	#telegram-auth-modal .form-group {
		margin-bottom: 15px;
	}
	#telegram-auth-modal .form-group label {
		display: block;
		margin-bottom: 5px;
		font-weight: 500;
	}
	#telegram-auth-modal .form-control {
		width: 100%;
		padding: 8px 12px;
		border: 1px solid #ced4da;
		border-radius: 4px;
		font-size: 14px;
	}
	#telegram-auth-modal .btn {
		padding: 8px 16px;
		margin-right: 10px;
		border: none;
		border-radius: 4px;
		cursor: pointer;
		font-size: 14px;
	}
	#telegram-auth-modal .btn-primary {
		background: #007bff;
		color: white;
	}
	#telegram-auth-modal .btn-primary:hover {
		background: #0056b3;
	}
	#telegram-auth-modal .btn-secondary {
		background: #6c757d;
		color: white;
	}
	#telegram-auth-modal .btn-secondary:hover {
		background: #5a6268;
	}
	#telegram-auth-modal .alert {
		padding: 12px;
		border-radius: 4px;
		margin-bottom: 15px;
	}
	#telegram-auth-modal .alert-info {
		background: #d1ecf1;
		border: 1px solid #bee5eb;
		color: #0c5460;
	}
	#telegram-auth-modal .alert-success {
		background: #d4edda;
		border: 1px solid #c3e6cb;
		color: #155724;
	}
	#telegram-auth-modal .alert-warning {
		background: #fff3cd;
		border: 1px solid #ffeaa7;
		color: #856404;
	}
	#telegram-auth-modal .alert-danger {
		background: #f8d7da;
		border: 1px solid #f5c6cb;
		color: #721c24;
	}
</style>

<!-- Навигация по табам -->
<div class="parser-tabs">
	<button class="parser-tab active" data-tab="rss">
		<i class="fa fa-rss"></i> RSS
	</button>
	<button class="parser-tab" data-tab="telegram">
		<i class="fa fa-telegram"></i> Telegram
	</button>
	<button class="parser-tab" data-tab="vk">
		<i class="fa fa-vk"></i> VK
	</button>
	<button class="parser-tab" data-tab="site">
		<i class="fa fa-globe"></i> Сайты
	</button>
	<button class="parser-tab" data-tab="settings">
		<i class="fa fa-cog"></i> Настройки
	</button>
</div>

<!-- Контент RSS -->
<div id="tab-rss" class="parser-tab-content active">
<div class="row mt-2">
	<div class="col-sm">
		<form action="" method="post" name="parse_rss_news">
			<input type="hidden" name="source" value="rss">
			<input type="hidden" name="actionName" value="generate_news">
			<div class="card">
				<div class="card-header">Новости из RSS</div>
				<div class="card-body">
					<div class="list">
						URL RSS-канала:
						<input type="text" class="form-control" name="rss_url" value="{{ rss_url }}" required>
					</div>
					<div class="list">
						Сохранённые каналы:
						<div class="input-group">
							<select class="form-control" name="rss_channel_select">
								<option value="" selected>— выберите канал —</option>
								{% for url in rss_channels %}
									<option value="{{ url }}">{{ url }}</option>
								{% endfor %}
							</select>
							<button type="button" class="btn btn-outline-secondary" id="useSelected">Вставить</button>
						</div>
					</div>
					<div class="list">
						Количество новостей:
						<input type="number" class="form-control" name="count" value="{{ rss_limit }}" min="1" max="1000">
					</div>
					<div class="list">
						Категория для публикации:
						<select class="form-control" name="category">
							<option value="0">— Без категории —</option>
							{% for c in categories %}
								<option value="{{ c.id }}">{{ c.name }}</option>
							{% endfor %}
						</select>
					</div>
					<div class="list">
						<div class="progressbar">
							<div class="progress-label"></div>
						</div>
					</div>
					<!-- Добавляем блок для сообщений -->
					<div class="message"></div>
				</div>
				<div class="card-footer">
					<input type="submit" name="submit" value="Парсить RSS!" class="btn btn-outline-primary">
				</div>
			</div>
		</form>
	</div>
</div>
<div class="row mt-2">
	<div class="col-sm">
		<form action="" method="post" name="manage_rss_channels_add">
			<div class="card">
				<div class="card-header">Сохранение RSS-канала</div>
				<div class="card-body">
					<div class="list">
						Новый RSS-канал:
						<input type="text" class="form-control" name="new_rss_url" placeholder="https://example.com/feed" value="">
					</div>
				</div>
				<div class="card-footer">
					<input type="submit" name="submit" value="Добавить в список" class="btn btn-outline-secondary">
				</div>
			</div>
		</form>
	</div>
</div>
<div class="row mt-2">
	<div class="col-sm">
		<div class="card">
			<div class="card-header">Сохранённые RSS-каналы</div>
			<div class="card-body">
				{% if rss_channels|length == 0 %}
					<p>Список пуст.</p>
				{% else %}
					<ul class="list-group">
						{% for url in rss_channels %}
							<li class="list-group-item d-flex justify-content-between align-items-center">
								<span>{{ url }}</span>
								<form action="" method="post" name="manage_rss_channels_delete_{{ loop.index }}">
									<input type="hidden" name="delete_rss_url" value="{{ url }}">
									<button type="submit" class="btn btn-sm btn-outline-danger">Удалить</button>
								</form>
							</li>
						{% endfor %}
					</ul>
				{% endif %}
			</div>
		</div>
	</div>
</div>
</div><!-- Конец tab-rss -->


<!-- Контент Парсера сайтов -->
<div id="tab-site" class="parser-tab-content">
<div class="row mt-2">
	<div class="col-sm">
		<form action="" method="post" name="parse_site_news">
			<input type="hidden" name="source" value="site">
			<input type="hidden" name="actionName" value="generate_news">
			<div class="card">
				<div class="card-header">Новости с сайта</div>
				<div class="card-body">
					<div class="list">
						Адрес страницы со списком новостей:
						<input type="text" class="form-control" name="site_url" placeholder="https://example.com/news" required>
					</div>
					<div class="list">
						id или class блока новости:
						<input type="text" class="form-control" name="site_selector" placeholder="#news-item или .news-card" required>
						<small class="form-text text-muted">
							Укажите <code>#id</code>, если у каждого блока новости свой id, или <code>.class</code>, если блоки повторяются с одинаковым классом (например, карточки новостей в ленте). Префикс можно не указывать — тогда поиск идёт по class.
						</small>
					</div>
					<div class="list">
						Сохранённые источники:
						<div class="input-group">
							<select class="form-control" name="site_source_select">
								<option value="" selected>— выберите источник —</option>
								{% for s in site_sources %}
									<option value="{{ s.url }}" data-selector="{{ s.selector }}">{{ s.url }} ({{ s.selector }})</option>
								{% endfor %}
							</select>
							<button type="button" class="btn btn-outline-secondary" id="useSelectedSite">Вставить</button>
						</div>
					</div>
					<div class="list">
						Количество новостей:
						<input type="number" class="form-control" name="count" value="{{ rss_limit }}" min="1" max="200">
					</div>
					<div class="list">
						Категория для публикации:
						<select class="form-control" name="category">
							<option value="0">— Без категории —</option>
							{% for c in categories %}
								<option value="{{ c.id }}">{{ c.name }}</option>
							{% endfor %}
						</select>
					</div>
					<div class="list">
						<div class="progressbar">
							<div class="progress-label"></div>
						</div>
					</div>
					<div class="message"></div>
				</div>
				<div class="card-footer">
					<input type="submit" name="submit" value="Парсить сайт!" class="btn btn-outline-primary">
				</div>
			</div>
		</form>
	</div>
</div>
<div class="row mt-2">
	<div class="col-sm">
		<form action="" method="post" name="manage_site_sources_add">
			<div class="card">
				<div class="card-header">Сохранение источника сайта</div>
				<div class="card-body">
					<div class="list">
						Адрес страницы:
						<input type="text" class="form-control" name="new_site_url" placeholder="https://example.com/news" value="">
					</div>
					<div class="list">
						id или class блока:
						<input type="text" class="form-control" name="new_site_selector" placeholder="#news-item или .news-card" value="">
					</div>
				</div>
				<div class="card-footer">
					<input type="submit" name="submit" value="Добавить в список" class="btn btn-outline-secondary">
				</div>
			</div>
		</form>
	</div>
</div>
<div class="row mt-2">
	<div class="col-sm">
		<div class="card">
			<div class="card-header">Сохранённые источники сайтов</div>
			<div class="card-body">
				{% if site_sources|length == 0 %}
					<p>Список пуст.</p>
				{% else %}
					<ul class="list-group">
						{% for s in site_sources %}
							<li class="list-group-item d-flex justify-content-between align-items-center">
								<span>{{ s.url }} <code>{{ s.selector }}</code></span>
								<form action="" method="post" name="manage_site_sources_delete_{{ loop.index }}">
									<input type="hidden" name="delete_site_index" value="{{ loop.index0 }}">
									<button type="submit" class="btn btn-sm btn-outline-danger">Удалить</button>
								</form>
							</li>
						{% endfor %}
					</ul>
				{% endif %}
			</div>
		</div>
	</div>
</div>
</div><!-- Конец tab-site -->


<!-- Контент VK -->
<div id="tab-vk" class="parser-tab-content">
<div class="row mt-2">
	<div class="col-sm">
		<form action="" method="post" name="parse_vk_posts">
			<input type="hidden" name="source" value="vk">
			<input type="hidden" name="actionName" value="generate_news">
			<div class="card">
				<div class="card-header">Посты из VK</div>
				<div class="card-body">
					<div class="list">
						Группа VK (имя или URL):
						<input type="text" class="form-control" name="vk_group" placeholder="club123456 или https://vk.com/public123" required>
					</div>
					<div class="list">
						Сохранённые группы:
						<div class="input-group">
							<select class="form-control" name="vk_group_select">
								<option value="" selected>— выберите группу —</option>
								{% for g in vk_groups %}
									<option value="{{ g }}">{{ g }}</option>
								{% endfor %}
							</select>
							<button type="button" class="btn btn-outline-secondary" id="useSelectedVk">Вставить</button>
						</div>
					</div>
					<div class="list">
						Количество постов:
						<input type="number" class="form-control" name="count" value="{{ rss_limit }}" min="1" max="50">
					</div>
					<div class="list">
						Категория для публикации:
						<select class="form-control" name="category">
							<option value="0">— Без категории —</option>
							{% for c in categories %}
								<option value="{{ c.id }}">{{ c.name }}</option>
							{% endfor %}
						</select>
					</div>
					<div class="list">
						<div class="progressbar">
							<div class="progress-label"></div>
						</div>
					</div>
					<div class="message"></div>
				</div>
				<div class="card-footer">
					<input type="submit" name="submit" value="Парсить VK!" class="btn btn-outline-primary">
				</div>
			</div>
		</form>
	</div>
</div>

<div class="row mt-2">
	<div class="col-sm">
		<form action="" method="post" name="vk_token_form">
			<div class="card">
				<div class="card-header">Настройка VK API</div>
				<div class="card-body">
					<div class="list">
						<label>VK API Access Token:</label>
						<input type="text" class="form-control" name="vk_token" value="{{ vk_token }}" placeholder="Вставьте пользовательский access_token">
						<small class="form-text text-muted">
							<strong>⚠️ Токен сообщества не подходит</strong> — он не умеет читать стену (ошибка "Group authorization failed"). Нужен <b>пользовательский токен</b> с правом <code>wall</code>.
							<br><br><strong>Как получить пользовательский токен:</strong>
							<br>1. Создайте standalone-приложение: <a href="https://vk.com/apps?act=manage" target="_blank">vk.com/apps?act=manage</a> → "Создать приложение" → тип <b>Standalone-приложение</b>. Скопируйте <b>ID приложения</b>.
							<br>2. Вставьте в браузере ссылку (замените <code>APP_ID</code> на ID вашего приложения):
							<br><code style="word-break:break-all;">https://oauth.vk.com/authorize?client_id=APP_ID&display=page&scope=wall,photos,groups,offline&response_type=token&v=5.199</code>
							<br>3. Разрешите доступ приложению. Браузер перейдёт на пустую страницу с адресом вида <code>https://oauth.vk.com/blank.html#access_token=ВАШ_ТОКЕН&expires_in=0&user_id=...</code>
							<br>4. Скопируйте значение <code>access_token</code> из адресной строки (до символа <code>&</code>) и вставьте в поле выше.
							<br><br><a href="https://dev.vk.com/ru/api/access-token/getting-started" target="_blank">Официальная инструкция VK</a>
						</small>
					</div>
				</div>
				<div class="card-footer">
					<input type="submit" name="save_vk_token" value="Сохранить токен" class="btn btn-outline-success">
				</div>
			</div>
		</form>
	</div>
</div>

<div class="row mt-2">
	<div class="col-sm">
		<form action="" method="post" name="manage_vk_groups_add">
			<div class="card">
				<div class="card-header">Сохранение VK группы</div>
				<div class="card-body">
					<div class="list">
						Новая группа:
						<input type="text" class="form-control" name="new_vk_group" placeholder="club123 или https://vk.com/public123" value="">
					</div>
				</div>
				<div class="card-footer">
					<input type="submit" name="submit" value="Добавить в список" class="btn btn-outline-secondary">
				</div>
			</div>
		</form>
	</div>
</div>
<div class="row mt-2">
	<div class="col-sm">
		<div class="card">
			<div class="card-header">Сохранённые VK группы</div>
			<div class="card-body">
				{% if vk_groups|length == 0 %}
					<p>Список пуст.</p>
				{% else %}
					<ul class="list-group">
						{% for g in vk_groups %}
							<li class="list-group-item d-flex justify-content-between align-items-center">
								<span>{{ g }}</span>
								<form action="" method="post" name="manage_vk_groups_delete_{{ loop.index }}">
									<input type="hidden" name="delete_vk_group" value="{{ g }}">
									<button type="submit" class="btn btn-sm btn-outline-danger">Удалить</button>
								</form>
							</li>
						{% endfor %}
					</ul>
				{% endif %}
			</div>
		</div>
	</div>
</div>
</div><!-- Конец tab-vk -->

<!-- Контент Telegram -->
<div id="tab-telegram" class="parser-tab-content">
<!-- Секция Telegram -->
<div class="row mt-2">
	<div class="col-sm">
		<form action="" method="post" name="parse_telegram_posts">
			<input type="hidden" name="source" value="telegram">
			<input type="hidden" name="actionName" value="generate_news">
			<div class="card">
				<div class="card-header">Посты из Telegram</div>
				<div class="card-body">
					<div class="list">
						Канал Telegram (имя или URL):
						<input type="text" class="form-control" name="tg_channel" placeholder="@channel или https://t.me/channel" required>
						<small class="form-text text-muted">
							Работает только с публичными каналами. Примеры: @durov, t.me/channel_name, https://t.me/s/channel_name
						</small>
					</div>
					<div class="list">
						Сохранённые каналы:
						<div class="input-group">
							<select class="form-control" name="tg_channel_select">
								<option value="" selected>— выберите канал —</option>
								{% for ch in tg_channels %}
									<option value="{{ ch }}">{{ ch }}</option>
								{% endfor %}
							</select>
							<button type="button" class="btn btn-outline-secondary" id="useSelectedTg">Вставить</button>
						</div>
					</div>
					<div class="list">
						Количество постов:
						<input type="number" class="form-control" name="count" value="{{ rss_limit }}" min="1" max="50">
					</div>
					<div class="list">
						Категория для публикации:
						<select class="form-control" name="category">
							<option value="0">— Без категории —</option>
							{% for c in categories %}
								<option value="{{ c.id }}">{{ c.name }}</option>
							{% endfor %}
						</select>
					</div>
					<div class="list">
						<div class="progressbar">
							<div class="progress-label"></div>
						</div>
					</div>
					<div class="message"></div>
				</div>
				<div class="card-footer">
					<input type="submit" name="submit" value="Парсить Telegram!" class="btn btn-outline-primary">
				</div>
			</div>
		</form>
	</div>
</div>

<div class="row mt-2">
	<div class="col-sm">
		<form action="" method="post" name="manage_tg_channels_add">
			<div class="card">
				<div class="card-header">Сохранение Telegram канала</div>
				<div class="card-body">
					<div class="list">
						Новый канал:
						<input type="text" class="form-control" name="new_tg_channel" placeholder="@channel или https://t.me/channel" value="">
					</div>
				</div>
				<div class="card-footer">
					<input type="submit" name="submit" value="Добавить в список" class="btn btn-outline-secondary">
				</div>
			</div>
		</form>
	</div>
</div>

<div class="row mt-2">
	<div class="col-sm">
		<div class="card">
			<div class="card-header">Сохранённые Telegram каналы</div>
			<div class="card-body">
				{% if tg_channels|length == 0 %}
					<p>Список пуст.</p>
				{% else %}
					<ul class="list-group">
						{% for ch in tg_channels %}
							<li class="list-group-item d-flex justify-content-between align-items-center">
								<span>@{{ ch }}</span>
								<form action="" method="post" name="manage_tg_channels_delete_{{ loop.index }}">
									<input type="hidden" name="delete_tg_channel" value="{{ ch }}">
									<button type="submit" class="btn btn-sm btn-outline-danger">Удалить</button>
								</form>
							</li>
						{% endfor %}
					</ul>
				{% endif %}
			</div>
		</div>
	</div>
</div>
</div><!-- Конец tab-telegram -->

<!-- Контент Settings (Настройки) -->
<div id="tab-settings" class="parser-tab-content">
<!-- Секция настройки Telegram API (MadelineProto) -->
<div class="row mt-2">
	<div class="col-sm">
		<form action="" method="post" name="telegram_api_form">
			<div class="card">
				<div class="card-header">Настройка Telegram API (MadelineProto)</div>
				<div class="card-body">
					{% if not madelineproto_installed %}
						<div class="alert alert-warning">
							<strong>⚠️ MadelineProto не установлена</strong><br>
							Для расширенного парсинга Telegram (включая приватные каналы с авторизацией) установите библиотеку:<br>
							<code>composer require danog/madelineproto</code><br>
							<small>Веб-парсинг публичных каналов работает без библиотеки.</small>
						</div>
					{% else %}
						<div class="alert alert-success">
							<strong>✅ MadelineProto установлена</strong><br>
							Можно использовать расширенный парсинг с авторизацией.
						</div>
					{% endif %}

					<div class="list">
						<label>
							<input type="checkbox" name="tg_use_madelineproto" value="1" {% if tg_use_madelineproto %}checked{% endif %}>
							Использовать MadelineProto для парсинга Telegram
						</label>
						<small class="form-text text-muted">
							Если включено - будет использоваться MadelineProto (требует API credentials).
							Если выключено или нет credentials - будет использоваться веб-парсинг публичных каналов.
						</small>
					</div>

					<div class="list mt-2">
						<label>Telegram API ID:</label>
						<input type="text" class="form-control" name="tg_api_id" value="{{ tg_api_id }}" placeholder="Получите на my.telegram.org">
						<small class="form-text text-muted">
							<a href="https://my.telegram.org/apps" target="_blank">Получить API ID и Hash на my.telegram.org</a>
						</small>
					</div>

					<div class="list mt-2">
						<label>Telegram API Hash:</label>
						<input type="text" class="form-control" name="tg_api_hash" value="{{ tg_api_hash }}" placeholder="Получите на my.telegram.org">
					</div>

					<div class="alert alert-info mt-3">
						<strong>ℹ️ Как получить API credentials:</strong>
						<ol>
							<li>Зайдите на <a href="https://my.telegram.org" target="_blank">my.telegram.org</a></li>
							<li>Авторизуйтесь через свой номер телефона</li>
							<li>Перейдите в раздел "API development tools"</li>
							<li>Создайте новое приложение (если еще нет)</li>
							<li>Скопируйте API ID и API Hash</li>
						</ol>
						<strong>Преимущества MadelineProto:</strong>
						<ul>
							<li>✅ Доступ к приватным каналам (с авторизацией)</li>
							<li>✅ Полные метаданные постов</li>
							<li>✅ Работает даже если t.me заблокирован</li>
							<li>✅ Скачивание всех медиафайлов</li>
						</ul>
					</div>
				</div>
				<div class="card-footer">
					<button type="submit" name="save_tg_api" class="btn btn-outline-success">Сохранить настройки</button>
					{% if madelineproto_installed and tg_api_id and tg_api_hash %}
					<button type="button" id="btn-telegram-auth" class="btn btn-outline-primary ml-2">
						<i class="fa fa-telegram"></i> Авторизоваться в Telegram
					</button>
					<button type="button" id="btn-check-auth" class="btn btn-outline-secondary ml-2">
						Проверить статус
					</button>
					<div id="telegram-auth-status" class="mt-2"></div>
					{% endif %}
				</div>
			</div>
		</form>
	</div>
</div>

<!-- Модальное окно авторизации Telegram -->
<style>
	#telegram-auth-modal {
		position: fixed;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		z-index: 1050;
		display: none;
		overflow-x: hidden;
		overflow-y: auto;
		outline: 0;
	}
	#telegram-auth-modal.show {
		display: block !important;
	}
	#telegram-auth-modal .modal-dialog {
		position: relative;
		width: auto;
		max-width: 500px;
		margin: 1.75rem auto;
		pointer-events: none;
	}
	#telegram-auth-modal .modal-content {
		position: relative;
		display: flex;
		flex-direction: column;
		width: 100%;
		pointer-events: auto;
		background-color: #fff;
		background-clip: padding-box;
		border: 1px solid rgba(0,0,0,.2);
		border-radius: 0.3rem;
		outline: 0;
	}
	.modal-backdrop {
		position: fixed;
		top: 0;
		left: 0;
		z-index: 1040;
		width: 100vw;
		height: 100vh;
		background-color: #000;
	}
	.modal-backdrop.show {
		opacity: 0.5;
	}
</style>
<div id="telegram-auth-modal" class="modal fade" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Авторизация в Telegram</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<!-- Шаг 1: Ввод номера телефона -->
				<div id="auth-step-phone" class="auth-step">
					<div class="form-group">
						<label>Номер телефона (международный формат):</label>
						<input type="text" id="auth-phone" class="form-control" placeholder="+79001234567" value="">
						<small class="form-text text-muted">
							Введите номер с кодом страны (например: +79001234567)
						</small>
					</div>
					<button type="button" id="btn-send-code" class="btn btn-primary">Отправить код</button>
				</div>

				<!-- Шаг 2: Ввод кода -->
				<div id="auth-step-code" class="auth-step" style="display:none;">
					<div class="alert alert-info">
						Код подтверждения отправлен в Telegram на ваш номер. Проверьте Saved Messages.
					</div>
					<div class="form-group">
						<label>Код из Telegram:</label>
						<input type="text" id="auth-code" class="form-control" placeholder="12345" value="">
					</div>
					<button type="button" id="btn-verify-code" class="btn btn-primary">Подтвердить</button>
					<button type="button" id="btn-back-phone" class="btn btn-secondary">Назад</button>
				</div>

				<!-- Шаг 3: Ввод 2FA пароля -->
				<div id="auth-step-2fa" class="auth-step" style="display:none;">
					<div class="alert alert-warning">
						У вас включена двухфакторная аутентификация. Введите пароль.
					</div>
					<div class="form-group">
						<label>Пароль 2FA:</label>
						<input type="password" id="auth-password" class="form-control" placeholder="Ваш пароль" value="">
					</div>
					<button type="button" id="btn-verify-2fa" class="btn btn-primary">Подтвердить</button>
				</div>

				<!-- Сообщения -->
				<div id="auth-message" class="mt-3"></div>
			</div>
		</div>
	</div>
</div>
</div><!-- Конец tab-settings -->

<!-- Подключение jQuery и jQuery UI -->
 <script src="{{ home }}/lib/jq/jquery.min.js"></script>
 <script src="{{ home }}/lib/jqueryui/core/jquery-ui.min.js"></script>
<link
rel="stylesheet" href="{{ home }}/lib/jqueryui/core/jquery-ui.min.css">  <script>
																		$(document).ready(function () {
																		// Используем прямой маршрут, как в рабочем content_generator
																	let progressbar,
																	progressLabel,
																	button,
																	message;
																	$('form[name="parse_rss_news"], form[name="parse_vk_posts"], form[name="parse_telegram_posts"], form[name="parse_site_news"]').on('submit', function (event) {
																	event.preventDefault();
																	// Определяем текущую форму
																	const form = $(this);
																	const actionName = form.find('input[name="actionName"]').val();
																	const source = form.find('input[name="source"]').val() || 'rss';
																	const count = parseInt(form.find('input[name="count"]').val(), 10);
																	// Источники: rss, vk, telegram или site
																	let rssUrl = '', vkGroup = '', tgChannel = '', siteUrl = '', siteSelector = '';
																	let hasError = false;
																	const category = parseInt(form.find('select[name="category"]').val(), 10) || 0;
																	if (!category || category <= 0) {
																		alert('Выберите категорию для публикации');
																		return;
																	}
																	if (source === 'rss') {
																		rssUrl = form.find('input[name="rss_url"]').val();
																		if (! rssUrl || rssUrl.trim() === '') {
																			alert('Введите корректный URL RSS-канала');
																			return;
																		}
																	} else if (source === 'vk') {
																		vkGroup = form.find('input[name="vk_group"]').val();
																		if (! vkGroup || vkGroup.trim() === '') {
																			alert('Введите корректное имя группы VK');
																			return;
																		}
																	} else if (source === 'telegram') {
																		tgChannel = form.find('input[name="tg_channel"]').val();
																		if (! tgChannel || tgChannel.trim() === '') {
																			alert('Введите корректное имя канала Telegram');
																			return;
																		}
																	} else if (source === 'site') {
																		siteUrl = form.find('input[name="site_url"]').val();
																		siteSelector = form.find('input[name="site_selector"]').val();
																		if (! siteUrl || siteUrl.trim() === '') {
																			alert('Введите корректный URL сайта');
																			return;
																		}
																		if (! siteSelector || siteSelector.trim() === '') {
																			alert('Укажите id или class блока новости');
																			return;
																		}
																	}
																	if (isNaN(count) || count < 1 || count > 1000) {
																	alert('Введите корректное количество (от 1 до 1000)');
																	return;
																	}
																	// Находим блок сообщений только в текущей форме
																	message = form.find('.message');
																	message.hide().removeClass('success error').text('');
																	startAjaxProcess(form, actionName, source, rssUrl, vkGroup, tgChannel, siteUrl, siteSelector, count, category);
																	});
																	// Подстановка выбранного сохранённого канала в поле URL
																	$(document).on('click', '#useSelected', function () {
																		const wrapper = $(this).closest('.list');
																		const select = wrapper.find('select[name="rss_channel_select"]');
																		const selected = select.val();
																		if (selected && selected.trim() !== '') {
																			const urlInput = $('form[name="parse_rss_news"]').find('input[name="rss_url"]');
																			urlInput.val(selected);
																		}
																	});
																	// Подстановка выбранной VK группы в поле vk_group
																	$(document).on('click', '#useSelectedVk', function () {
																		const wrapper = $(this).closest('.list');
																		const select = wrapper.find('select[name="vk_group_select"]');
																		const selected = select.val();
																		if (selected && selected.trim() !== '') {
																			const vInput = $('form[name="parse_vk_posts"]').find('input[name="vk_group"]');
																			vInput.val(selected);
																		}
																	});
																	// Подстановка выбранного Telegram канала в поле tg_channel
																	$(document).on('click', '#useSelectedTg', function () {
																		const wrapper = $(this).closest('.list');
																		const select = wrapper.find('select[name="tg_channel_select"]');
																		const selected = select.val();
																		if (selected && selected.trim() !== '') {
																			const tInput = $('form[name="parse_telegram_posts"]').find('input[name="tg_channel"]');
																			tInput.val(selected);
																		}
																	});
																	// Подстановка выбранного источника сайта в поля site_url и site_selector
																	$(document).on('click', '#useSelectedSite', function () {
																		const wrapper = $(this).closest('.list');
																		const select = wrapper.find('select[name="site_source_select"]');
																		const selectedOption = select.find('option:selected');
																		const selected = select.val();
																		if (selected && selected.trim() !== '') {
																			const form = $('form[name="parse_site_news"]');
																			form.find('input[name="site_url"]').val(selected);
																			form.find('input[name="site_selector"]').val(selectedOption.data('selector') || '');
																		}
																	});
																	function startAjaxProcess(form, actionName, source, rssUrl, vkGroup, tgChannel, siteUrl, siteSelector, count, category) {
																		const chunkSize = 100; // Размер одного чанка
																		const chunkCount = Math.ceil(count / chunkSize);
																		let currentChunk = 1;
																		let hasError = false;
																		progressbar = form.find(".progressbar");
																		progressLabel = form.find(".progress-label");
																		button = form.find('input[type="submit"]');
																		button.hide();
																		progressbar.show().progressbar({
																			value: false,
																			change: function () {
																				progressLabel.text(`${Math.round(progressbar.progressbar("value"))}%`);
																			},
																			complete: function () {
																				progressLabel.text("Готово!");
																			}
																		});
																		function processChunk(currentChunk) {
																			$.ajax({
																				method: "POST",
																				cache: false,
																			url: '/plugin/content_parser/',
																				data: {
																					actionName,
																					source,
																					rss_url: rssUrl,
																					vk_group: vkGroup,
																					tg_channel: tgChannel,
																					site_url: siteUrl,
																					site_selector: siteSelector,
																					category,
																					real_count: Math.min(chunkSize, count - chunkSize * (currentChunk - 1))
																				},
																				success: function (response) {
																					try {
																						const data = (typeof response === 'string') ? JSON.parse(response) : response;
																						if (data && data.status === 'success') {
																							progressbar.progressbar("value", (100 / chunkCount) * currentChunk);
																							// Сохраняем HTML нотификаций для показа после завершения
																							if (!window.parseMsgHtml) {
																								window.parseMsgHtml = '';
																							}
																							if (data.msg) {
																								window.parseMsgHtml += data.msg;
																							}
																						} else if (data && data.error) {
																							hasError = true;
																							showMessage('error', data.error);
																						} else {
																							progressbar.progressbar("value", (100 / chunkCount) * currentChunk);
																						}
																					} catch (e) {
																						progressbar.progressbar("value", (100 / chunkCount) * currentChunk);
																					}
																				},
																				error: function (xhr, status, error) {
																					hasError = true;
																					showMessage('error', `Произошла ошибка: ${error}`);
																				},
																				complete: function () {
																					if (!hasError && currentChunk < chunkCount) {
																						processChunk(currentChunk + 1);
																					} else {
																						finishProcess();
																						if (!hasError && window.parseMsgHtml) {
																							// Вставляем HTML нотификаций в начало страницы
																							$('h2:first').after(window.parseMsgHtml);
																							// Очищаем для следующего запуска
																							window.parseMsgHtml = null;
																						}
																					}
																				}
																			});
																		}
																		processChunk(currentChunk);
																	}
																	function finishProcess() {
																	button.show();
																	progressbar.hide();
																	}
																	// Функция для отображения сообщений
																	function showMessage(type, text) {
																	message.text(text).addClass(type).fadeIn();
																	setTimeout(() => {
																	message.fadeOut();
																	}, 5000); // Сообщение исчезает через 5 секунд
																	}

																	// === Telegram авторизация ===
																	console.log('Инициализация Telegram авторизации...');
																	console.log('Кнопка авторизации:', $('#btn-telegram-auth').length);
																	console.log('Модальное окно:', $('#telegram-auth-modal').length);

																	// Открытие модального окна авторизации
																	$('#btn-telegram-auth').on('click', function(e) {
																		e.preventDefault();
																		console.log('Клик по кнопке авторизации');

																		// Сброс к первому шагу
																		$('#auth-step-phone').show();
																		$('#auth-step-code, #auth-step-2fa').hide();
																		$('#auth-message').html('');

																		// Открытие модального окна (Bootstrap или fallback)
																		const $modal = $('#telegram-auth-modal');
																		if (typeof $.fn.modal === 'function') {
																			// Bootstrap доступен
																			$modal.modal('show');
																		} else {
																			// Fallback на jQuery
																			$('body').append('<div class="modal-backdrop fade show"></div>');
																			$modal.addClass('show').css('display', 'block');
																		}
																	});

																	// Закрытие модального окна
																	$(document).on('click', '#telegram-auth-modal .close, .modal-backdrop', function(e) {
																		e.preventDefault();
																		const $modal = $('#telegram-auth-modal');
																		if (typeof $.fn.modal === 'function') {
																			$modal.modal('hide');
																		} else {
																			$modal.removeClass('show').css('display', 'none');
																			$('.modal-backdrop').remove();
																		}
																	});

																	// Закрытие при клике по затемнённому фону
																	$('#telegram-auth-modal').on('click', function(e) {
																		if ($(e.target).is('#telegram-auth-modal')) {
																			$(this).fadeOut(300);
																		}
																	});

																	// Предотвращаем закрытие при клике внутри модального окна
																	$('#telegram-auth-modal .modal-content').on('click', function(e) {
																		e.stopPropagation();
																	});

																	// Проверка статуса авторизации
																	$('#btn-check-auth').on('click', function() {
																		console.log('Проверка статуса авторизации...');
																		$.ajax({
																			url: '?mod=extra-config&plugin=content_parser&action=check_telegram_auth',
																			method: 'GET',
																			dataType: 'json',
																			success: function(response) {
																				console.log('Ответ сервера (check_telegram_auth):', response);
																				const status = $('#telegram-auth-status');
																				if (response.authorized) {
																					status.html('<div class="alert alert-success mt-2">✅ Авторизован: ' + response.phone + (response.username ? ' (@' + response.username + ')' : '') + '</div>');
																				} else {
																					let message = response.message || response.error || 'Неизвестный статус';
																					if (response.debug) {
																						console.log('Отладочная информация:', response.debug);
																						message += ' (см. консоль для деталей)';
																					}

																					// Если требуется переавторизация
																					if (response.action_needed === 'reauth') {
																						status.html('<div class="alert alert-danger mt-2">❌ ' + message +
																							'<br><button type="button" id="btn-reset-auth" class="btn btn-sm btn-danger mt-2">🔄 Удалить сессию и переавторизоваться</button></div>');
																					} else {
																						status.html('<div class="alert alert-warning mt-2">⚠️ Не авторизован: ' + message + '</div>');
																					}
																				}
																			},
																			error: function(xhr, status, error) {
																				console.error('Ошибка AJAX (check_telegram_auth):', {xhr: xhr, status: status, error: error, responseText: xhr.responseText});
																				$('#telegram-auth-status').html('<div class="alert alert-danger mt-2">❌ Ошибка проверки статуса. См. консоль.</div>');
																			}
																		});
																	});

																	// Шаг 1: Отправка номера телефона
																	$('#btn-send-code').on('click', function() {
																		const phone = $('#auth-phone').val().trim();
																		if (!phone) {
																			showAuthMessage('error', 'Введите номер телефона');
																			return;
																		}

																		console.log('Отправка номера телефона:', phone);
																		showAuthMessage('info', 'Отправка кода...');
																		$(this).prop('disabled', true);

																		$.ajax({
																			url: '?mod=extra-config&plugin=content_parser&action=start_telegram_auth',
																			method: 'POST',
																			data: { phone: phone },
																			dataType: 'json',
																			success: function(response) {
																				console.log('Ответ сервера (start_telegram_auth):', response);
																				console.log('response.success:', response.success, 'тип:', typeof response.success);

																				if (response.success) {
																					console.log('Переключение на шаг 2...');
																					console.log('$auth-step-phone найден:', $('#auth-step-phone').length);
																					console.log('$auth-step-code найден:', $('#auth-step-code').length);

																					$('#auth-step-phone').hide();
																					$('#auth-step-code').show();

																					console.log('После переключения - phone visible:', $('#auth-step-phone').is(':visible'));
																					console.log('После переключения - code visible:', $('#auth-step-code').is(':visible'));

																					showAuthMessage('success', response.message || 'Код отправлен!');
																				} else {
																					showAuthMessage('error', response.error || 'Ошибка отправки кода');
																				}
																			},
																			error: function(xhr, status, error) {
																				console.error('Ошибка AJAX (start_telegram_auth):', {xhr: xhr, status: status, error: error, responseText: xhr.responseText});
																				showAuthMessage('error', 'Ошибка соединения с сервером. См. консоль.');
																			},
																			complete: function() {
																				$('#btn-send-code').prop('disabled', false);
																			}
																		});
																	});

																	// Возврат к вводу телефона
																	$('#btn-back-phone').on('click', function() {
																		$('#auth-step-code').hide();
																		$('#auth-step-phone').show();
																		$('#auth-code').val('');
																		$('#auth-message').html('');
																	});

																	// Шаг 2: Подтверждение кода
																	$('#btn-verify-code').on('click', function() {
																		const code = $('#auth-code').val().trim();
																		if (!code) {
																			showAuthMessage('error', 'Введите код из Telegram');
																			return;
																		}

																		showAuthMessage('info', 'Проверка кода...');
																		$(this).prop('disabled', true);

																		$.ajax({
																			url: '?mod=extra-config&plugin=content_parser&action=complete_telegram_auth',
																			method: 'POST',
																			data: { code: code },
																			dataType: 'json',
																			success: function(response) {
																				console.log('Ответ complete_telegram_auth:', response);
																				if (response.success) {
																					showAuthMessage('success', '✅ ' + response.message);
																					setTimeout(function() {
																						// Закрытие модального окна
																						const $modal = $('#telegram-auth-modal');
																						if (typeof $.fn.modal === 'function') {
																							$modal.modal('hide');
																						} else {
																							$modal.removeClass('show').css('display', 'none');
																							$('.modal-backdrop').remove();
																						}
																						// Обновляем статус авторизации
																						$('#btn-check-auth').click();
																					}, 1500);
																				} else if (response.need_2fa) {
																					$('#auth-step-code').hide();
																					$('#auth-step-2fa').show();
																					showAuthMessage('warning', response.message);
																				} else {
																					showAuthMessage('error', response.error || 'Неверный код');
																				}
																			},
																			error: function() {
																				showAuthMessage('error', 'Ошибка соединения с сервером');
																			},
																			complete: function() {
																				$('#btn-verify-code').prop('disabled', false);
																			}
																		});
																	});

																	// Шаг 3: Подтверждение 2FA пароля
																	$('#btn-verify-2fa').on('click', function() {
																		const password = $('#auth-password').val();
																		if (!password) {
																			showAuthMessage('error', 'Введите пароль 2FA');
																			return;
																		}

																		showAuthMessage('info', 'Проверка пароля...');
																		$(this).prop('disabled', true);

																		$.ajax({
																			url: '?mod=extra-config&plugin=content_parser&action=complete_2fa_telegram_auth',
																			method: 'POST',
																			data: { password: password },
																			dataType: 'json',
																			success: function(response) {
																				console.log('Ответ complete_2fa_telegram_auth:', response);
																				if (response.success) {
																					showAuthMessage('success', '✅ ' + response.message);
																					setTimeout(function() {
																						// Закрытие модального окна
																						const $modal = $('#telegram-auth-modal');
																						if (typeof $.fn.modal === 'function') {
																							$modal.modal('hide');
																						} else {
																							$modal.removeClass('show').css('display', 'none');
																							$('.modal-backdrop').remove();
																						}
																						// Обновляем статус авторизации
																						$('#btn-check-auth').click();
																					}, 1500);
																				} else {
																					showAuthMessage('error', response.error || 'Неверный пароль');
																				}
																			},
																			error: function() {
																				showAuthMessage('error', 'Ошибка соединения с сервером');
																			},
																			complete: function() {
																				$('#btn-verify-2fa').prop('disabled', false);
																			}
																		});
																	});

																	// Удаление битой сессии и переавторизация
																	$(document).on('click', '#btn-reset-auth', function() {
																		if (!confirm('Удалить текущую сессию и пройти авторизацию заново?')) {
																			return;
																		}

																		console.log('Удаление сессии...');
																		$(this).prop('disabled', true).text('Удаление...');

																		$.ajax({
																			url: '?mod=extra-config&plugin=content_parser&action=reset_telegram_auth',
																			method: 'POST',
																			dataType: 'json',
																			success: function(response) {
																				console.log('Ответ reset_telegram_auth:', response);
																				if (response.success) {
																					$('#telegram-auth-status').html('<div class="alert alert-info mt-2">✅ ' + response.message + '<br>Теперь нажмите "Авторизоваться в Telegram"</div>');
																				} else {
																					let errorHtml = '<div class="alert alert-danger mt-2">❌ Ошибка: ' + (response.error || 'Неизвестная ошибка');

																					if (response.manual_path) {
																						errorHtml += '<br><br><strong>Удалите файл вручную:</strong><br><code style="display:block;padding:8px;background:#f5f5f5;margin:5px 0;">' + response.manual_path + '</code>';
																						errorHtml += '<button type="button" class="btn btn-sm btn-info mt-2" onclick="navigator.clipboard.writeText(\'' + response.manual_path + '\'); alert(\'Путь скопирован\');">📋 Скопировать путь</button>';

																						if (response.hint) {
																							errorHtml += '<br><br><strong>Или выполните в PowerShell:</strong><br><code style="display:block;padding:8px;background:#f5f5f5;margin:5px 0;word-break:break-all;">' + response.hint + '</code>';
																							errorHtml += '<button type="button" class="btn btn-sm btn-info mt-2" onclick="navigator.clipboard.writeText(\'' + response.hint.replace(/'/g, "\\'") + '\'); alert(\'Команда скопирована\');">📋 Скопировать команду</button>';
																						}
																					}

																					if (response.debug) {
																						console.log('Отладка:', response.debug);
																						errorHtml += '<br><small>См. консоль для деталей</small>';
																					}

																					errorHtml += '</div>';
																					$('#telegram-auth-status').html(errorHtml);
																				}
																			},
																			error: function(xhr, status, error) {
																				console.error('Ошибка удаления сессии:', error);
																				$('#telegram-auth-status').html('<div class="alert alert-danger mt-2">❌ Ошибка соединения с сервером</div>');
																			}
																		});
																	});

																	// Функция показа сообщений в модальном окне
																	function showAuthMessage(type, text) {
																		const alertClass = {
																			'success': 'alert-success',
																			'error': 'alert-danger',
																			'warning': 'alert-warning',
																			'info': 'alert-info'
																		}[type] || 'alert-info';

																		$('#auth-message').html('<div class="alert ' + alertClass + '">' + text + '</div>');
																	}

																	// ===== УПРАВЛЕНИЕ ТАБАМИ =====

																	// Функции для работы с cookies
																	function setCookie(name, value, days) {
																		const expires = new Date();
																		expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
																		document.cookie = name + '=' + value + ';expires=' + expires.toUTCString() + ';path=/';
																	}

																	function getCookie(name) {
																		const nameEQ = name + '=';
																		const ca = document.cookie.split(';');
																		for(let i = 0; i < ca.length; i++) {
																			let c = ca[i];
																			while (c.charAt(0) === ' ') c = c.substring(1, c.length);
																			if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
																		}
																		return null;
																	}

																	// Переключение табов
																	$('.parser-tab').on('click', function() {
																		const tabName = $(this).data('tab');

																		// Убираем active со всех табов и контента
																		$('.parser-tab').removeClass('active');
																		$('.parser-tab-content').removeClass('active');

																		// Добавляем active к выбранному табу и контенту
																		$(this).addClass('active');
																		$('#tab-' + tabName).addClass('active');

																		// Сохраняем выбор в cookies на 30 дней
																		setCookie('content_parser_active_tab', tabName, 30);

																		console.log('Переключен таб:', tabName);
																	});

																	// Восстановление активного таба из cookies при загрузке
																	const savedTab = getCookie('content_parser_active_tab');
																	if (savedTab) {
																		$('.parser-tab[data-tab="' + savedTab + '"]').click();
																		console.log('Восстановлен таб из cookies:', savedTab);
																	}

																	});
																	</script>
