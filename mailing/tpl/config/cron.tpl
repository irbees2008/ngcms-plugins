<h4>{{ lang.cron_heading }}</h4>

<fieldset class="admGroup">
	<legend class="title">{{ lang.system_cron_recommended }}</legend>
	<div class="alert alert-info">
		<p>
			<strong>{{ lang.current_period }}:</strong>
			{{ period_label }}</p>
		<p>{{ lang.cron_auto_explanation }}
			<code>syscron.php</code>
			{{ lang.cron_auto_explanation_suffix }}</p>
		<p>{{ lang.cron_period_instructions }}</p>
	</div>
</fieldset>

<fieldset class="admGroup">
	<legend class="title">{{ lang.manual_url_title }}</legend>

	{% if cron_secret %}
		<div class="alert alert-success">
			<p>
				<strong>{{ lang.manual_url_label }}</strong>
			</p>
			<pre>{{ cron_url }}</pre>

			<p class="mt-3">
				<strong>{{ lang.crontab_example }}</strong>
			</p>
			<pre>*/5 * * * * curl -s {{ cron_url }} >/dev/null 2>&1</pre>

			<p class="mt-3">
				<strong>{{ lang.wget_example }}</strong>
			</p>
			<pre>*/5 * * * * wget -q -O - {{ cron_url }} >/dev/null 2>&1</pre>
		</div>
	{% else %}
		<div class="alert alert-danger">
			<p>
				<strong>{{ lang.secret_not_set }}</strong>
			</p>
			<p>{{ lang.set_secret_instruction }}</p>
			<p>{{ lang.secret_required }}</p>
		</div>
	{% endif %}
</fieldset>

<fieldset class="admGroup">
	<legend class="title">{{ lang.visitor_processing }}</legend>
	<div class="alert alert-warning">
		<p>
			<strong>{{ lang.status }}:</strong>
			{% if enable_tick == '1' %}✅ {{ lang.enabled }}{% else %}❌ {{ lang.disabled }}
			{% endif %}
		</p>
		<p>{{ lang.queue_visit_probability }}
			{{ tick_chance }}%</p>
		<p>{{ lang.visitor_settings_instruction }}</p>
	</div>
</fieldset>
