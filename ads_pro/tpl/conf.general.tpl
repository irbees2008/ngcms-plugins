<form method="post" action="admin.php?mod=extra-config&plugin=ads_pro">
	<input type="hidden" name="action" value="main_submit"/>
	<div class="card">
		<div class="card-body">
			<div class="mb-3 row">
				<label class="col-sm-6 col-form-label">{{ lang['ads_pro:general_news'] }}</label>
				<div class="col-sm-6">
					<select name="support_news" class="form-control">
					<option value="0" {% if s_news == 0 %} selected {% endif %}>{{ lang['noa'] }}</option>
					<option value="1" {% if s_news == 1 %} selected {% endif %}>{{ lang['yesa'] }}</option>
					</select>
				</div>
			</div>
			<div class="mb-3 row">
				<label class="col-sm-6 col-form-label">{{ lang['ads_pro:news_cfg_sort'] }}</label>
				<div class="col-sm-6">
<select name="news_cfg_sort" class=" form-control">
						<option value="0" {% if s_news_sort == 0 %} selected {% endif %}>{{ lang['ads_pro:news_cfg_sort_id'] }}</option>
						<option value="1" {% if s_news_sort == 1 %} selected {% endif %}>{{ lang['ads_pro:news_cfg_sort_title'] }}</option>
					</select>
				</div>
			</div>
			<div class="mb-3 row">
				<label class="col-sm-6 col-form-label">{{ lang['ads_pro:multidisplay_mode'] }}</label>
				<div class="col-sm-6">
					<select name="multidisplay_mode" class=" form-control">
						<option value="0" {% if multidisplay_mode == 0 %} selected {% endif %}>{{ lang['ads_pro:multidisplay_mode0'] }}</option>
						<option value="1" {% if multidisplay_mode == 1 %} selected {% endif %}>{{ lang['ads_pro:multidisplay_mode1'] }}</option>
						<option value="2" {% if multidisplay_mode == 2 %} selected {% endif %}>{{ lang['ads_pro:multidisplay_mode2'] }}</option>
					</select>
				</div>
			</div>
		</div>
	</div>
	<div class="mt-3">
		<button type="submit" class="btn btn-success">{{ lang['ads_pro:general_submit'] }}</button>
		<a href="admin.php?mod=extra-config&plugin=ads_pro&action=clear_cash" class="btn btn-warning">{{ lang['ads_pro:button_clear_cash'] }}</a>
	</div>
</form>
