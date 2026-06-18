<div class="payments-success">
	<div class="alert alert-success">
		<h2>Оплата прошла успешно!</h2>
		{% if order %}
			<p>Заказ #{{ order.id }}
				оплачен. Спасибо!</p>
		{% endif %}
	</div>
	<a href="/" class="btn btn-primary">На главную</a>
</div>
