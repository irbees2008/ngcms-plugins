<div class="payments-page">
	<h2>{{ lang.pay_title }} #{{ order.id }}</h2>
	<p>{{ lang.pay_amount }}
		<strong>{{ order.total }}</strong>
	</p>

	<div class="payments-methods">
		<h3>{{ lang.pay_choose_method }}</h3>
		{% for method in adapters %}
			<a href="{{ app.request.uri }}?order_id={{ order.id }}&uniqid={{ order.uniqid }}&method={{ method }}" class="payment-method-btn btn btn-outline-primary">
				{{ method|upper }}
			</a>
		{% endfor %}
	</div>
</div>
