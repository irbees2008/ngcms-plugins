<form method="post" action="">
	<fieldset class="admGroup">
		<legend class="title">Отправитель</legend>
		<div class="table-responsive">
			<table class="table table-bordered">
				<tbody>
					<tr>
						<th scope="row" class="align-middle">Email отправителя</th>
						<td><input name="from_email" type="text" class="form-control" value="{{ from_email }}"/></td>
					</tr>
					<tr>
						<th scope="row" class="align-middle">Имя отправителя</th>
						<td><input name="from_name" type="text" class="form-control" value="{{ from_name }}"/></td>
					</tr>
					<tr>
						<th scope="row" class="align-middle">Reply-To</th>
						<td><input name="reply_to" type="text" class="form-control" value="{{ reply_to }}"/></td>
					</tr>
				</tbody>
			</table>
		</div>
	</fieldset>

	<fieldset class="admGroup">
		<legend class="title">SMTP настройки</legend>
		<div class="table-responsive">
			<table class="table table-bordered">
				<tbody>
					<tr>
						<th scope="row" class="align-middle">Использовать SMTP</th>
						<td>
							<select name="smtp_enable" class="form-control">
								<option value="1" {% if smtp_enable == '1' %} selected {% endif %}>Да</option>
								<option value="0" {% if smtp_enable == '0' %} selected {% endif %}>Нет</option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row" class="align-middle">SMTP Host</th>
						<td><input name="smtp_host" type="text" class="form-control" value="{{ smtp_host }}"/></td>
					</tr>
					<tr>
						<th scope="row" class="align-middle">SMTP Port</th>
						<td><input name="smtp_port" type="text" class="form-control" value="{{ smtp_port }}"/></td>
					</tr>
					<tr>
						<th scope="row" class="align-middle">SMTP Auth</th>
						<td>
							<select name="smtp_auth" class="form-control">
								<option value="1" {% if smtp_auth == '1' %} selected {% endif %}>Да</option>
								<option value="0" {% if smtp_auth == '0' %} selected {% endif %}>Нет</option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row" class="align-middle">SMTP User</th>
						<td><input name="smtp_user" type="text" class="form-control" value="{{ smtp_user }}"/></td>
					</tr>
					<tr>
						<th scope="row" class="align-middle">SMTP Password</th>
						<td><input name="smtp_pass" type="password" class="form-control" value="{{ smtp_pass }}"/></td>
					</tr>
					<tr>
						<th scope="row" class="align-middle">
							SMTP Secure
							<br><small>tls, ssl или пусто</small>
						</th>
						<td><input name="smtp_secure" type="text" class="form-control" value="{{ smtp_secure }}" placeholder="tls"/></td>
					</tr>
				</tbody>
			</table>
		</div>
	</fieldset>

	<fieldset class="admGroup">
		<legend class="title">Параметры отправки</legend>
		<div class="table-responsive">
			<table class="table table-bordered">
				<tbody>
					<tr>
						<th scope="row" class="align-middle">
							Пакет отправки
							<br><small>Сколько писем отправлять за один проход</small>
						</th>
						<td><input name="send_batch" type="text" class="form-control" value="{{ send_batch }}"/></td>
					</tr>
					<tr>
						<th scope="row" class="align-middle">Максимум попыток</th>
						<td><input name="max_tries" type="text" class="form-control" value="{{ max_tries }}"/></td>
					</tr>
					<tr>
						<th scope="row" class="align-middle">Разрешить &lt;iframe&gt;</th>
						<td>
							<select name="allow_iframe" class="form-control">
								<option value="1" {% if allow_iframe == '1' %} selected {% endif %}>Да</option>
								<option value="0" {% if allow_iframe == '0' %} selected {% endif %}>Нет</option>
							</select>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</fieldset>

	<fieldset class="admGroup">
		<legend class="title">Системный CRON (syscron.php)</legend>
		<div class="table-responsive">
			<table class="table table-bordered">
				<tbody>
					<tr>
						<th scope="row" class="align-middle">
							Период запуска
							<br><small>Плагин будет вызываться через syscron.php</small>
						</th>
						<td>
							<select name="period" class="form-control">
								<option value="0" {% if period == '0' %} selected {% endif %}>Отключено</option>
								<option value="5m" {% if period == '5m' %} selected {% endif %}>5 минут</option>
								<option value="10m" {% if period == '10m' %} selected {% endif %}>10 минут</option>
								<option value="15m" {% if period == '15m' %} selected {% endif %}>15 минут</option>
								<option value="30m" {% if period == '30m' %} selected {% endif %}>30 минут</option>
								<option value="1h" {% if period == '1h' %} selected {% endif %}>1 час</option>
								<option value="2h" {% if period == '2h' %} selected {% endif %}>2 часа</option>
								<option value="3h" {% if period == '3h' %} selected {% endif %}>3 часа</option>
								<option value="4h" {% if period == '4h' %} selected {% endif %}>4 часа</option>
								<option value="6h" {% if period == '6h' %} selected {% endif %}>6 часов</option>
								<option value="8h" {% if period == '8h' %} selected {% endif %}>8 часов</option>
								<option value="12h" {% if period == '12h' %} selected {% endif %}>12 часов</option>
								<option value="1d" {% if period == '1d' %} selected {% endif %}>1 день</option>
							</select>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</fieldset>

	<fieldset class="admGroup">
		<legend class="title">Авто-обработка (по посещениям)</legend>
		<div class="table-responsive">
			<table class="table table-bordered">
				<tbody>
					<tr>
						<th scope="row" class="align-middle">Включить обработку по заходам</th>
						<td>
							<select name="enable_tick" class="form-control">
								<option value="1" {% if enable_tick == '1' %} selected {% endif %}>Да</option>
								<option value="0" {% if enable_tick == '0' %} selected {% endif %}>Нет</option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row" class="align-middle">Шанс запуска на хите, %</th>
						<td><input name="tick_chance" type="text" class="form-control" value="{{ tick_chance }}"/></td>
					</tr>
					<tr>
						<th scope="row" class="align-middle">
							Секрет для cron URL
							<br><small>Для ручного запуска через URL</small>
						</th>
						<td><input name="cron_secret" type="text" class="form-control" value="{{ cron_secret }}" placeholder="сгенерируйте строку"/></td>
					</tr>
				</tbody>
			</table>
		</div>
	</fieldset>

	<fieldset class="admGroup">
		<legend class="title">Авто-рассылка новых новостей</legend>
		<div class="table-responsive">
			<table class="table table-bordered">
				<tbody>
					<tr>
						<th scope="row" class="align-middle">Включить авто-рассылку</th>
						<td>
							<select name="auto_news_enable" class="form-control">
								<option value="1" {% if auto_news_enable == '1' %} selected {% endif %}>Да</option>
								<option value="0" {% if auto_news_enable == '0' %} selected {% endif %}>Нет</option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row" class="align-middle">
							Категория новостей (ID)
							<br><small>0 = все категории</small>
						</th>
						<td><input name="auto_news_category" type="text" class="form-control" value="{{ auto_news_category }}"/></td>
					</tr>
					<tr>
						<th scope="row" class="align-middle">
							Группы пользователей
							<br><small>JSON массив, пример: [1,2]</small>
						</th>
						<td><input name="auto_news_groups" type="text" class="form-control" value="{{ auto_news_groups }}"/></td>
					</tr>
					<tr>
						<th scope="row" class="align-middle">Сколько новостей за проход</th>
						<td><input name="auto_news_scan_limit" type="text" class="form-control" value="{{ auto_news_scan_limit }}"/></td>
					</tr>
				</tbody>
			</table>
		</div>
	</fieldset>

	<div class="card-footer text-center">
		<input name="submit" type="submit" value="Сохранить настройки" class="btn btn-outline-success"/>
	</div>
</form>
