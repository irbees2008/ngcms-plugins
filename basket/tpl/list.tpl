{% if recs > 0 %}
	<form method="post" action="/plugin/basket/update/">
		<h3>Ваша корзина</h3>
		<div class="table">
			<table class="basket_tb">
				<thead>
					<tr valign="top">
						<td>#</td>
						<td>Наименование</td>
						<td>Размер</td>
						<td>Цена</td>
						<td>Кол-во</td>
						<td>Стоимость</td>
					</tr>
				</thead>
				<tbody>
					{% for entry in entries %}
						<tr>
							<td>{{ loop.index }}</td>
							<td>{{ entry.title }}</td>
							<td>{{ entry.xfields.news.size|default('—') }}</td>
							<td align="right">{{ entry.price_formatted }}</td>
							<td>
								<input name="count_{{ entry.id }}" type="text" maxlength="5" style="width: 35px;" value="{{ entry.count }}"/>
							</td>
							<td align="right">{{ entry.sum_formatted }}</td>
						</tr>
					{% endfor %}
				</tbody>
				<tfoot>
					<tr>
						<td colspan="5" align="right">
							<strong>Итого:</strong>
						</td>
						<td align="right">
							<strong>{{ total_formatted }}</strong>
						</td>
					</tr>
				</tfoot>
			</table>
		</div>
		<br/>
		<input type="submit" style="width: 150px;" value="Пересчитать"/>
		<input type="button" style="width: 150px;" value="Оформить заказ" onclick="document.location='{{ form_url }}';"/>
	</form>
{% else %}
	<p>Ваша корзина пуста!</p>
{% endif %}
