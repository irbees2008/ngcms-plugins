{% if recs > 0 %}
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
						<td align="right">{{ entry.count }}</td>
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
{% else %}
	<p>Ваша корзина пуста!</p>
{% endif %}
