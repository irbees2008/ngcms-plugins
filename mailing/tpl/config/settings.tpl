<form method="post" action="">
	<fieldset class="admGroup">
		<legend class="title">{{ lang.sender_section }}</legend>
		<div class="table-responsive">
			<table class="table table-bordered">
				<tbody>
					<tr>
						<th scope="row" class="align-middle">{{ lang.sender_email }}</th>
						<td><input name="from_email" type="text" class="form-control" value="{{ from_email }}"/></td>
					</tr>
					<tr>
						<th scope="row" class="align-middle">{{ lang.sender_name }}</th>
						<td><input name="from_name" type="text" class="form-control" value="{{ from_name }}"/></td>
					</tr>
					<tr>
						<th scope="row" class="align-middle">{{ lang.reply_to }}</th>
						<td><input name="reply_to" type="text" class="form-control" value="{{ reply_to }}"/></td>
					</tr>
				</tbody>
			</table>
		</div>
	</fieldset>

	<fieldset class="admGroup">
		<legend class="title">{{ lang.smtp_settings }}</legend>
		<div class="table-responsive">
			<table class="table table-bordered">
				<tbody>
					<tr>
						<th scope="row" class="align-middle">{{ lang.use_smtp }}</th>
						<td>
							<select name="smtp_enable" class="form-control">
								<option value="1" {% if smtp_enable == '1' %} selected {% endif %}>{{ lang.yes_option }}</option>
								<option value="0" {% if smtp_enable == '0' %} selected {% endif %}>{{ lang.no_option }}</option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row" class="align-middle">{{ lang.smtp_host }}</th>
						<td><input name="smtp_host" type="text" class="form-control" value="{{ smtp_host }}"/></td>
					</tr>
					<tr>
						<th scope="row" class="align-middle">{{ lang.smtp_port }}</th>
						<td><input name="smtp_port" type="text" class="form-control" value="{{ smtp_port }}"/></td>
					</tr>
					<tr>
						<th scope="row" class="align-middle">{{ lang.smtp_auth }}</th>
						<td>
							<select name="smtp_auth" class="form-control">
								<option value="1" {% if smtp_auth == '1' %} selected {% endif %}>{{ lang.yes_option }}</option>
								<option value="0" {% if smtp_auth == '0' %} selected {% endif %}>{{ lang.no_option }}</option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row" class="align-middle">{{ lang.smtp_user }}</th>
						<td><input name="smtp_user" type="text" class="form-control" value="{{ smtp_user }}"/></td>
					</tr>
					<tr>
						<th scope="row" class="align-middle">{{ lang.smtp_password }}</th>
						<td><input name="smtp_pass" type="password" class="form-control" value="{{ smtp_pass }}"/></td>
					</tr>
					<tr>
						<th scope="row" class="align-middle">
							{{ lang.smtp_secure }}
							<br><small>{{ lang.smtp_secure_help }}</small>
						</th>
						<td><input name="smtp_secure" type="text" class="form-control" value="{{ smtp_secure }}" placeholder="tls"/></td>
					</tr>
				</tbody>
			</table>
		</div>
	</fieldset>

	<fieldset class="admGroup">
		<legend class="title">{{ lang.send_parameters }}</legend>
		<div class="table-responsive">
			<table class="table table-bordered">
				<tbody>
					<tr>
						<th scope="row" class="align-middle">
							{{ lang.send_batch }}
							<br><small>{{ lang.send_batch_help }}</small>
						</th>
						<td><input name="send_batch" type="text" class="form-control" value="{{ send_batch }}"/></td>
					</tr>
					<tr>
						<th scope="row" class="align-middle">{{ lang.max_attempts }}</th>
						<td><input name="max_tries" type="text" class="form-control" value="{{ max_tries }}"/></td>
					</tr>
					<tr>
						<th scope="row" class="align-middle">{{ lang.allow_iframe }}</th>
						<td>
							<select name="allow_iframe" class="form-control">
								<option value="1" {% if allow_iframe == '1' %} selected {% endif %}>{{ lang.yes_option }}</option>
								<option value="0" {% if allow_iframe == '0' %} selected {% endif %}>{{ lang.no_option }}</option>
							</select>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</fieldset>

	<fieldset class="admGroup">
		<legend class="title">{{ lang.system_cron }} (syscron.php)</legend>
		<div class="table-responsive">
			<table class="table table-bordered">
				<tbody>
					<tr>
						<th scope="row" class="align-middle">
							{{ lang.run_period }}
							<br><small>{{ lang.run_period_help }}</small>
						</th>
						<td>
							<select name="period" class="form-control">
								<option value="0" {% if period == '0' %} selected {% endif %}>{{ lang.period_disabled }}</option>
								<option value="5m" {% if period == '5m' %} selected {% endif %}>{{ lang.period_5m }}</option>
								<option value="10m" {% if period == '10m' %} selected {% endif %}>{{ lang.period_10m }}</option>
								<option value="15m" {% if period == '15m' %} selected {% endif %}>{{ lang.period_15m }}</option>
								<option value="30m" {% if period == '30m' %} selected {% endif %}>{{ lang.period_30m }}</option>
								<option value="1h" {% if period == '1h' %} selected {% endif %}>{{ lang.period_1h }}</option>
								<option value="2h" {% if period == '2h' %} selected {% endif %}>{{ lang.period_2h }}</option>
								<option value="3h" {% if period == '3h' %} selected {% endif %}>{{ lang.period_3h }}</option>
								<option value="4h" {% if period == '4h' %} selected {% endif %}>{{ lang.period_4h }}</option>
								<option value="6h" {% if period == '6h' %} selected {% endif %}>{{ lang.period_6h }}</option>
								<option value="8h" {% if period == '8h' %} selected {% endif %}>{{ lang.period_8h }}</option>
								<option value="12h" {% if period == '12h' %} selected {% endif %}>{{ lang.period_12h }}</option>
								<option value="1d" {% if period == '1d' %} selected {% endif %}>{{ lang.period_1d }}</option>
							</select>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</fieldset>

	<fieldset class="admGroup">
		<legend class="title">{{ lang.visitor_processing }}</legend>
		<div class="table-responsive">
			<table class="table table-bordered">
				<tbody>
					<tr>
						<th scope="row" class="align-middle">{{ lang.enable_tick }}</th>
						<td>
							<select name="enable_tick" class="form-control">
								<option value="1" {% if enable_tick == '1' %} selected {% endif %}>{{ lang.yes_option }}</option>
								<option value="0" {% if enable_tick == '0' %} selected {% endif %}>{{ lang.no_option }}</option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row" class="align-middle">{{ lang.tick_chance }}</th>
						<td><input name="tick_chance" type="text" class="form-control" value="{{ tick_chance }}"/></td>
					</tr>
					<tr>
						<th scope="row" class="align-middle">
							{{ lang.cron_secret }}
							<br><small>{{ lang.cron_secret_help }}</small>
						</th>
						<td><input name="cron_secret" type="text" class="form-control" value="{{ cron_secret }}" placeholder="{{ lang.cron_secret_placeholder }}"/></td>
					</tr>
				</tbody>
			</table>
		</div>
	</fieldset>

	<fieldset class="admGroup">
		<legend class="title">{{ lang.auto_news_section }}</legend>
		<div class="table-responsive">
			<table class="table table-bordered">
				<tbody>
					<tr>
						<th scope="row" class="align-middle">{{ lang.auto_news_enable }}</th>
						<td>
							<select name="auto_news_enable" class="form-control">
								<option value="1" {% if auto_news_enable == '1' %} selected {% endif %}>{{ lang.yes_option }}</option>
								<option value="0" {% if auto_news_enable == '0' %} selected {% endif %}>{{ lang.no_option }}</option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row" class="align-middle">
							{{ lang.news_category }}
							<br><small>{{ lang.all_categories }}</small>
						</th>
						<td><input name="auto_news_category" type="text" class="form-control" value="{{ auto_news_category }}"/></td>
					</tr>
					<tr>
						<th scope="row" class="align-middle">
							{{ lang.user_groups }}
							<br><small>{{ lang.user_groups_help }}</small>
						</th>
						<td><input name="auto_news_groups" type="text" class="form-control" value="{{ auto_news_groups }}"/></td>
					</tr>
					<tr>
						<th scope="row" class="align-middle">{{ lang.auto_news_scan_limit }}</th>
						<td><input name="auto_news_scan_limit" type="text" class="form-control" value="{{ auto_news_scan_limit }}"/></td>
					</tr>
				</tbody>
			</table>
		</div>
	</fieldset>

	<div class="card-footer text-center">
		<input name="submit" type="submit" value="{{ lang.save_settings }}" class="btn btn-outline-success"/>
	</div>
</form>
