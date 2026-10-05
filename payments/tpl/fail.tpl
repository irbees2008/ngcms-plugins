<div class="payments-fail">
	<div class="alert alert-danger">
		<h2>{{ lang.fail_title }}</h2>
		{% if order %}
			<p>{{ lang.order_label }} #{{ order.id }}
				{{ lang.fail_order_unpaid }}</p>
		{% endif %}
	</div>
	{% if order %}
		<a href="/payments/pay/?order_id={{ order.id }}&uniqid={{ order.uniqid }}" class="btn btn-warning">{{ lang.fail_retry }}</a>
	{% endif %}
	<a href="/" class="btn btn-secondary">{{ lang.home }}</a>
</div>
