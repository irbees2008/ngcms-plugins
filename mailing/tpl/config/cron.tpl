<h4>CRON / Ручной запуск</h4>

<fieldset class="admGroup">
	<legend class="title">Системный CRON (рекомендуется)</legend>
	<div class="alert alert-info">
		<p>
			<strong>Текущий период:</strong>
			{{ period_label }}</p>
		<p>Плагин автоматически вызывается через
			<code>syscron.php</code>
			согласно настроенному периоду.</p>
		<p>Измените период в разделе "Настройки" → "Системный CRON"</p>
	</div>
</fieldset>

<fieldset class="admGroup">
	<legend class="title">Ручной запуск через URL</legend>

	{% if cron_secret %}
		<div class="alert alert-success">
			<p>
				<strong>URL для ручного запуска:</strong>
			</p>
			<pre>{{ cron_url }}</pre>

			<p class="mt-3">
				<strong>Пример добавления в crontab:</strong>
			</p>
			<pre>*/5 * * * * curl -s {{ cron_url }} >/dev/null 2>&1</pre>

			<p class="mt-3">
				<strong>Или через wget:</strong>
			</p>
			<pre>*/5 * * * * wget -q -O - {{ cron_url }} >/dev/null 2>&1</pre>
		</div>
	{% else %}
		<div class="alert alert-danger">
			<p>
				<strong>Секрет не задан!</strong>
			</p>
			<p>Задайте секретный ключ в разделе "Настройки" → "Авто-обработка" → "Секрет для cron URL"</p>
			<p>Без секрета URL для ручного запуска будет недоступен.</p>
		</div>
	{% endif %}
</fieldset>

<fieldset class="admGroup">
	<legend class="title">Обработка по посещениям сайта</legend>
	<div class="alert alert-warning">
		<p>
			<strong>Статус:</strong>
			{% if enable_tick == '1' %}✅ Включено{% else %}❌ Отключено
			{% endif %}
		</p>
		<p>Очередь обрабатывается автоматически при заходах посетителей на сайт с вероятностью
			{{ tick_chance }}%</p>
		<p>Измените настройки в разделе "Настройки" → "Авто-обработка"</p>
	</div>
</fieldset>
