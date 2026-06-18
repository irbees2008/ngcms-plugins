<div class="payments-page">
	<h2>Оплата заказа #{{ order.id }}</h2>
	<p>Сумма к оплате:
		<strong>{{ order.total }}</strong>
	</p>

	<div class="payments-methods">
		<h3>Выберите способ оплаты:</h3>
		{% for method in adapters %}
			<a href="{{ app.request.uri }}?order_id={{ order.id }}&uniqid={{ order.uniqid }}&method={{ method }}" class="payment-method-btn btn btn-outline-primary">
				{{ method|upper }}
			</a>
		{% endfor %}
	</div>
</div>
