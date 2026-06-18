<div class="payments-fail">
	<div class="alert alert-danger">
		<h2>Оплата не выполнена</h2>
		{% if order %}
			<p>Заказ #{{ order.id }}
				не оплачен. Попробуйте ещё раз.</p>
		{% endif %}
	</div>
	{% if order %}
		<a href="/payments/pay/?order_id={{ order.id }}&uniqid={{ order.uniqid }}" class="btn btn-warning">Попробовать снова</a>
	{% endif %}
	<a href="/" class="btn btn-secondary">На главную</a>
</div>
