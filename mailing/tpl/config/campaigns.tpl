<h4>{{ lang.campaign_list }}</h4>

{% if entries %}
	{% if not hasStats %}
		<div class="alert alert-warning">
			<b>{{ lang.stats_columns_missing }}</b>
			{{ lang.stats_fallback_notice }}
			<code>migration_stats.sql</code>
			{{ lang.stats_migration_or_reinstall }}
		</div>
	{% endif %}
	<div class="table-responsive">
		<table class="table table-striped table-bordered">
			<thead>
				<tr>
					<th>ID</th>
					<th>{{ lang.subject }}</th>
					<th>{{ lang.status }}</th>
					<th>{{ lang.send_time }}</th>
					<th>{{ lang.queue }}</th>
					<th>{{ lang.statistics }}</th>
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
								{{ lang.total }}:
								{{ campaign.queue_total }}<br>
								{{ lang.sent }}:
								<span class="text-success">{{ campaign.queue_sent }}</span><br>
								{{ lang.errors }}:
								<span class="text-danger">{{ campaign.queue_failed }}</span>
							</small>
						</td>
						<td>
							<small>
								📤 {{ lang.sent }}:
								<strong class="text-primary">{{ campaign.sent_count }}</strong><br>
								✅ {{ lang.delivered }}:
								<strong class="text-success">{{ campaign.delivered_count }}</strong><br>
								❌ {{ lang.not_delivered }}:
								<strong class="text-danger">{{ campaign.failed_count }}</strong>
							</small>
						</td>
					</tr>
				{% endfor %}
			</tbody>
		</table>
	</div>

	<div class="alert alert-info">
		<strong>{{ lang.tip }}:</strong>
		{{ lang.tip_detail }}
	</div>
{% else %}
	<div class="alert alert-warning">
		{{ lang.no_campaigns }}
	</div>
{% endif %}
