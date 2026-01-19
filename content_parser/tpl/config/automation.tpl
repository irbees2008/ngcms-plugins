<style>
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
</style>
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
<div class="row mt-2">
	<div class="col-sm">
		<form action="" method="post" name="parse_instagram_posts">
			<input type="hidden" name="source" value="instagram">
			<input type="hidden" name="actionName" value="generate_news">
			<div class="card">
				<div class="card-header">Посты из Instagram</div>
				<div class="card-body">
					<div class="list">
						Имя пользователя (username):
						<input type="text" class="form-control" name="ig_user" placeholder="username или @username" required>
					</div>
					<div class="list">
						Сохранённые аккаунты:
						<div class="input-group">
							<select class="form-control" name="ig_account_select">
								<option value="" selected>— выберите аккаунт —</option>
								{% for u in ig_accounts %}
									<option value="{{ u }}">{{ u }}</option>
								{% endfor %}
							</select>
							<button type="button" class="btn btn-outline-secondary" id="useSelectedIg">Вставить</button>
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
					<input type="submit" name="submit" value="Парсить Instagram!" class="btn btn-outline-primary">
				</div>
			</div>
		</form>
	</div>
</div>
<div class="row mt-2">
	<div class="col-sm">
		<form action="" method="post" name="manage_ig_accounts_add">
			<div class="card">
				<div class="card-header">Сохранение Instagram-аккаунта</div>
				<div class="card-body">
					<div class="list">
						Новый аккаунт:
						<input type="text" class="form-control" name="new_ig_user" placeholder="username или @username" value="">
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
			<div class="card-header">Сохранённые Instagram-аккаунты</div>
			<div class="card-body">
				{% if ig_accounts|length == 0 %}
					<p>Список пуст.</p>
				{% else %}
					<ul class="list-group">
						{% for u in ig_accounts %}
							<li class="list-group-item d-flex justify-content-between align-items-center">
								<span>@{{ u }}</span>
								<form action="" method="post" name="manage_ig_accounts_delete_{{ loop.index }}">
									<input type="hidden" name="delete_ig_user" value="{{ u }}">
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
						<input type="text" class="form-control" name="vk_token" value="{{ vk_token }}" placeholder="Получите на vk.com/dev">
						<small class="form-text text-muted">
							Для парсинга VK необходим токен доступа.
							<a href="https://dev.vk.com/ru/api/access-token/getting-started" target="_blank">Инструкция по получению токена</a>
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
															$('form[name="parse_rss_news"], form[name="parse_instagram_posts"], form[name="parse_vk_posts"]').on('submit', function (event) {
															event.preventDefault();
															// Определяем текущую форму
															const form = $(this);
															const actionName = form.find('input[name="actionName"]').val();
															const source = form.find('input[name="source"]').val() || 'rss';
															const count = parseInt(form.find('input[name="count"]').val(), 10);
															// Источники: rss, instagram или vk
															let rssUrl = '', igUser = '', vkGroup = '';
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
															} else if (source === 'instagram') {
																igUser = form.find('input[name="ig_user"]').val();
																if (! igUser || igUser.trim() === '') {
																	alert('Введите корректное имя пользователя Instagram');
																	return;
																}
															} else if (source === 'vk') {
																vkGroup = form.find('input[name="vk_group"]').val();
																if (! vkGroup || vkGroup.trim() === '') {
																	alert('Введите корректное имя группы VK');
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
															startAjaxProcess(form, actionName, source, rssUrl, igUser, vkGroup, count, category);
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
															// Подстановка выбранного IG-аккаунта в поле username
															$(document).on('click', '#useSelectedIg', function () {
																const wrapper = $(this).closest('.list');
																const select = wrapper.find('select[name="ig_account_select"]');
																const selected = select.val();
																if (selected && selected.trim() !== '') {
																	const uInput = $('form[name="parse_instagram_posts"]').find('input[name="ig_user"]');
																	uInput.val(selected);
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
															function startAjaxProcess(form, actionName, source, rssUrl, igUser, vkGroup, count, category) {
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
																			ig_user: igUser,
																			vk_group: vkGroup,
																			category,
																			real_count: Math.min(chunkSize, count - chunkSize * (currentChunk - 1))
																		},
																		success: function (response) {
																			try {
																				const data = (typeof response === 'string') ? JSON.parse(response) : response;
																				if (data && data.status === 'success') {
																					progressbar.progressbar("value", (100 / chunkCount) * currentChunk);
																					// Сохраняем статистику для финального вывода
																					if (!window.parseStats) {
																						window.parseStats = { added: 0, skipped: 0, errors: [] };
																					}
																					window.parseStats.added += (data.added || 0);
																					window.parseStats.skipped += (data.skipped || 0);
																					if (data.errors && data.errors.length > 0) {
																						window.parseStats.errors = window.parseStats.errors.concat(data.errors);
																					}
																					// Сохраняем debug информацию
																					if (data.debug) {
																						window.parseDebug = data.debug;
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
																				if (!hasError) {
																					const stats = window.parseStats || { added: 0, skipped: 0, errors: [] };
																					const debugData = window.parseDebug || {};
																					let msg = `Парсинг завершен! Добавлено: ${stats.added}, Пропущено: ${stats.skipped}`;
	
																					// Добавляем debug информацию
																					if (debugData.items_received !== undefined) {
																						msg += `<br>Debug: получено=${debugData.items_received}, категория=${debugData.category}`;
																						if (debugData.first_item_title) {
																							msg += `<br>Первый элемент: ${debugData.first_item_title}`;
																						}
																					}
	
																					if (stats.errors.length > 0) {
																						msg += '<br>Ошибки:<br>' + stats.errors.join('<br>');
																						showMessage('warning', msg);
																					} else {
																						showMessage('success', msg);
																					}
																					// Очищаем статистику для следующего запуска
																					window.parseStats = null;
																					window.parseDebug = null;
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
															});
															</script>
