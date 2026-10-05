<form method="post" action="admin.php?mod=extra-config&plugin=zboard">
	<div class="card mb-4">
		<div class="card-header bg-info text-white font-weight-bold">{l_zboard:admin_settings}</div>
		<div class="card-body">
			<div class="form-row">
				<div class="form-group col-md-6">
					<label for="send_guest">{l_zboard:admin_allow_guest}</label>
					<select class="form-control" name="send_guest" id="send_guest">{send_guest}</select>
				</div>
				<div class="form-group col-md-6">
					<label for="count">{l_zboard:admin_count_page}</label>
					<input class="form-control" name="count" id="count" type="text" title="{l_zboard:admin_count_page}" value="{count}"/>
				</div>
			</div>
			<div class="form-row">
				<div class="form-group col-md-6">
					<label for="count_list">{l_zboard:admin_count_user_page}</label>
					<input class="form-control" name="count_list" id="count_list" type="text" title="{l_zboard:admin_count_user_page}" value="{count_list}"/>
				</div>
				<div class="form-group col-md-6">
					<label for="count_search">{l_zboard:admin_count_search_page}</label>
					<input class="form-control" name="count_search" id="count_search" type="text" title="{l_zboard:admin_count_search_page}" value="{count_search}"/>
				</div>
			</div>
			<div class="form-row">
				<div class="form-group col-md-6">
					<label for="description">{l_zboard:admin_home_description}</label>
					<input class="form-control" name="description" id="description" type="text" title="{l_zboard:admin_home_description}" value="{description}"/>
				</div>
				<div class="form-group col-md-6">
					<label for="keywords">{l_zboard:admin_home_keywords}</label>
					<input class="form-control" name="keywords" id="keywords" type="text" title="{l_zboard:admin_home_keywords}" value="{keywords}"/>
				</div>
			</div>
			<div class="form-group">
				<label for="info_send">{l_zboard:admin_after_add}</label>
				<textarea class="form-control" name="info_send" id="info_send" title="{l_zboard:admin_after_add}" rows="4">{info_send}</textarea>
			</div>
			<div class="form-group">
				<label for="info_edit">{l_zboard:admin_after_edit}</label>
				<textarea class="form-control" name="info_edit" id="info_edit" title="{l_zboard:admin_after_edit}" rows="4">{info_edit}</textarea>
			</div>
			<div class="form-row">
				<div class="form-group col-md-6">
					<label for="use_expired">{l_zboard:admin_expire_toggle}</label>
					<select class="form-control" name="use_expired" id="use_expired">{use_expired}</select>
				</div>
				<div class="form-group col-md-6">
					<label for="list_period">{l_zboard:admin_listing_duration}</label>
					<input class="form-control" name="list_period" id="list_period" type="text" title="{l_zboard:admin_listing_duration}" value="{list_period}"/>
				</div>
			</div>
			<div class="form-row">
				<div class="form-group col-md-6">
					<label for="views_count">{l_zboard:admin_count_views}</label>
					<select class="form-control" name="views_count" id="views_count">{views_count}</select>
				</div>
				<div class="form-group col-md-6">
					<label for="notice_mail">{l_zboard:admin_notice_admin}</label>
					<select class="form-control" name="notice_mail" id="notice_mail">{notice_mail}</select>
				</div>
			</div>
		</div>
	</div>
	<div class="card mb-4">
		<div class="card-header bg-secondary text-white font-weight-bold">{l_zboard:admin_templates_notifications}</div>
		<div class="card-body">
			<div class="form-group">
				<label for="template_mail">{l_zboard:admin_mail_template}</label>
				<small class="form-text text-muted">{l_zboard:admin_available_tags} %announce_name%, %author%, %announce_description%, %announce_period%, %announce_contacts%, %date%</small>
				<textarea class="form-control" name="template_mail" id="template_mail" title="{l_zboard:admin_mail_template}" rows="4">{template_mail}</textarea>
			</div>
			<div class="form-row">
				<div class="form-group col-md-6">
					<label for="main_template">{l_zboard:admin_main_template}</label>
					<input class="form-control" name="main_template" id="main_template" type="text" title="{l_zboard:admin_main_template}" value="{main_template}"/>
					<small class="form-text text-muted">{l_zboard:admin_main_template_help}</small>
				</div>
				<div class="form-group col-md-6">
					<label for="width_thumb">{l_zboard:admin_thumb_width}</label>
					<input class="form-control" name="width_thumb" id="width_thumb" type="text" title="{l_zboard:admin_thumb_width}" value="{width_thumb}"/>
				</div>
			</div>
			<div class="form-group">
				<label for="ext_image">{l_zboard:admin_image_extensions}</label>
				<input class="form-control" name="ext_image" id="ext_image" type="text" title="{l_zboard:admin_image_extensions}" value="{ext_image}"/>
				<small class="form-text text-muted">{l_zboard:admin_image_format}
					<b>*.jpg;*.jpeg;*.gif;*.png</b>
				</small>
			</div>
		</div>
	</div>
	<div class="card mb-4">
		<div class="card-header bg-warning text-dark font-weight-bold">{l_zboard:admin_recaptcha_settings}</div>
		<div class="card-body">
			<div class="form-row">
				<div class="form-group col-md-4">
					<label for="use_recaptcha">{l_zboard:admin_use_recaptcha}</label>
					<select class="form-control" name="use_recaptcha" id="use_recaptcha">{use_recaptcha}</select>
				</div>
				<div class="form-group col-md-4">
					<label for="public_key">{l_zboard:admin_public_key}</label>
					<input class="form-control" name="public_key" id="public_key" type="text" title="{l_zboard:admin_public_key}" value="{public_key}"/>
				</div>
				<div class="form-group col-md-4">
					<label for="private_key">{l_zboard:admin_private_key}</label>
					<input class="form-control" name="private_key" id="private_key" type="text" title="{l_zboard:admin_private_key}" value="{private_key}"/>
				</div>
			</div>
		</div>
	</div>
	<div class="card mb-4">
		<div class="card-header bg-success text-white font-weight-bold">{l_zboard:admin_panel_settings}</div>
		<div class="card-body">
			<div class="form-row">
				<div class="form-group col-md-6">
					<label for="admin_count">{l_zboard:admin_count_page}</label>
					<input class="form-control" name="admin_count" id="admin_count" type="text" title="{l_zboard:admin_count_page}" value="{admin_count}"/>
				</div>
				<div class="form-group col-md-6">
					<label for="date">{l_zboard:admin_date_format}</label>
					<input class="form-control" name="date" id="date" type="text" title="{l_zboard:admin_date_format}" value="{date}"/>
				</div>
			</div>
		</div>
	</div>
	<div class="card mb-4">
		<div class="card-header bg-primary text-white font-weight-bold">{l_zboard:admin_pay2pay_settings}</div>
		<div class="card-body">
			<div class="form-group">
				<label for="pay2pay_merchant_id">{l_zboard:admin_pay2pay_merchant}</label>
				<input class="form-control" name="pay2pay_merchant_id" id="pay2pay_merchant_id" type="text" title="{l_zboard:admin_pay2pay_merchant}" value="{pay2pay_merchant_id}"/>
			</div>
			<div class="form-group">
				<label for="pay2pay_secret_key">{l_zboard:admin_secret_key}</label>
				<input class="form-control" name="pay2pay_secret_key" id="pay2pay_secret_key" type="text" title="{l_zboard:admin_secret_key}" value="{pay2pay_secret_key}"/>
			</div>
			<div class="form-group">
				<label for="pay2pay_hidden_key">{l_zboard:admin_hidden_key}</label>
				<input class="form-control" name="pay2pay_hidden_key" id="pay2pay_hidden_key" type="text" title="{l_zboard:admin_hidden_key}" value="{pay2pay_hidden_key}"/>
			</div>
			<div class="form-group">
				<label for="pay2pay_test_mode">{l_zboard:admin_test_mode}</label>
				<select class="form-control" name="pay2pay_test_mode" id="pay2pay_test_mode">{pay2pay_test_mode}</select>
			</div>
		</div>
	</div>
	<div class="card mb-4">
		<div class="card-header bg-primary text-white font-weight-bold">{l_zboard:admin_robokassa_settings}</div>
		<div class="card-body">
			<div class="form-group">
				<label for="robokassa_login">{l_zboard:admin_robokassa_login}</label>
				<input class="form-control" name="robokassa_login" id="robokassa_login" type="text" value="{robokassa_login}"/>
			</div>
			<div class="form-group">
				<label for="robokassa_pass1">{l_zboard:admin_robokassa_pass1}</label>
				<input class="form-control" name="robokassa_pass1" id="robokassa_pass1" type="text" value="{robokassa_pass1}"/>
			</div>
			<div class="form-group">
				<label for="robokassa_pass2">{l_zboard:admin_robokassa_pass2}</label>
				<input class="form-control" name="robokassa_pass2" id="robokassa_pass2" type="text" value="{robokassa_pass2}"/>
			</div>
			<div class="form-group">
				<label for="robokassa_is_test">{l_zboard:admin_test_mode}</label>
				<select class="form-control" name="robokassa_is_test" id="robokassa_is_test">{robokassa_is_test}</select>
			</div>
		</div>
	</div>
	<div class="text-center mb-4">
		<button name="submit" type="submit" class="btn btn-lg btn-primary">{l_zboard:admin_save}</button>
	</div>
</form>
