<h4>Список кампаний</h4>

{% if entries %}
	{% if not hasStats %}
		<div class="alert alert-warning">
			<b>Столбцы статистики не найдены в БД.</b>
			Показатели
					отправки/доставки рассчитываются по очереди. Рекомендуется
					выполнить миграцию из файла
			<code>migration_stats.sql</code>
			или
					переустановить плагин, чтобы добавить поля.
		</div>
	{% endif %}
	<div class="table-responsive">
		<table class="table table-striped table-bordered">
			<thead>
				<tr>
					<th>ID</th>
					<th>Тема</th>
					<th>Статус</th>
					<th>Время отправки</th>
					<th>Очередь</th>
					<th>Статистика</th>
				</tr>
			</thead>
			<tbody>
				{% for campaign in entries %}
					<tr>
						<td>{{ campaign.id }}</td>
						<td>{{ campaign.subject }}</td>
						<td>
							<span class="badge badge-info">{{ campaign.status }}</span>
						</td>
						<td>{{ campaign.send_at_formatted }}</td>
						<td>
							<small>
								Всего:
								{{ campaign.queue_total }}<br>
								Отправлено:
								<span class="text-success">{{ campaign.queue_sent }}</span><br>
								Ошибок:
								<span class="text-danger">{{ campaign.queue_failed }}</span>
							</small>
						</td>
						<td>
							<small>
								📤 Отправлено:
								<strong class="text-primary">{{ campaign.sent_count }}</strong><br>
								✅ Доставлено:
								<strong class="text-success">{{ campaign.delivered_count }}</strong><br>
								❌ Не доставлено:
								<strong class="text-danger">{{ campaign.failed_count }}</strong>
							</small>
						</td>
					</tr>
				{% endfor %}
			</tbody>
		</table>
	</div>

	<div class="alert alert-info">
		<strong>Совет:</strong>
		Чтобы прогнать очередь вручную, откройте вкладку CRON или включите обработку по посещениям в настройках.
	</div>
{% else %}
	<div class="alert alert-warning">
		Пока нет кампаний. Создайте первую рассылку во вкладке "Создать рассылку".
	</div>
{% endif %}
