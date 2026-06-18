<div class="basket-order-confirm">
	<h2>Заказ #{{ order.id }}
		оформлен</h2>
	<p>Спасибо за ваш заказ! Статус:
		<strong>{{ order.status }}</strong>
	</p>

	<table class="table">
		<thead>
			<tr>
				<th>Товар</th>
				<th>Цена</th>
				<th>Кол-во</th>
				<th>Сумма</th>
			</tr>
		</thead>
		<tbody>
			{% for item in items %}
				<tr>
					<td>{{ item.title }}</td>
					<td>{{ item.price }}</td>
					<td>{{ item.count }}</td>
					<td>{{ (item.price * item.count)|round(2) }}</td>
				</tr>
			{% endfor %}
		</tbody>
		<tfoot>
			<tr>
				<td colspan="3">
					<strong>Итого:</strong>
				</td>
				<td>
					<strong>{{ order.total }}</strong>
				</td>
			</tr>
		</tfoot>
	</table>

	{% if pay_active and order.status == 'new' %}
		<div class="basket-pay-block">
			<p>Выберите способ оплаты и оплатите заказ:</p>
			<a href="{{ pay_link }}" class="btn btn-primary">Перейти к оплате</a>
		</div>
	{% elseif order.status == 'paid' %}
		<div class="alert alert-success">Заказ оплачен. Спасибо!</div>
	{% endif %}
</div>
