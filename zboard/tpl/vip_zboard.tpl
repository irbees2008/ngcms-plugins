{% if (error) %}
	<div class="feed-me">
		{{error}}
	</div>
{% endif %}
<div class="comment">
	<h3>
		<span>{{ lang['zboard']['ui_vip_title'] }}</span>
	</h3>
	<form method="post" action="{{pay_url}}" class="comment-form" name="form">
		<input type="hidden" name="zid" value="{{zid}}">
		<ul class="comment-author">
			<li class="item clearfix">
				<label>{{ lang['zboard']['ui_payment_system'] }}</label>
				<select name="provider">
					<option value="pay2pay">Pay2Pay</option>
					<option value="robokassa">Robokassa</option>
				</select>
			</li>
		</ul>
		<ul class="comment-author">
			<li class="item clearfix">
				<select name="price_time_id">
					<option disabled>{{ lang['zboard']['ui_choose_period'] }}</option>
					{% for entry in entriesPrices %}
						<option value="{{entry.id}}">{{entry.time}}
							{{ lang['zboard']['ui_days_short'] }} -
							{{entry.price}}
							{{ lang['zboard']['ui_currency_rub'] }}</option>
					{% endfor %}
				</select>
			</li>
		</ul>
		<span class="submit">
			<button name="submit" type="submit" tabindex="5" onclick="javascript:$('#file_upload').uploadifive('upload')">{{ lang['zboard']['ui_submit'] }}</button>
		</span>
	</form>
</div>
