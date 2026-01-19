{% if ajaxUpdate is not defined %}
	<div id="basketTotalDisplay">
	{% endif %}
	<div class="b500">
		<div id="basket">
			<ul>
				{% if count > 0 %}
					<li>Товаров:
						<span class="f12_product">{{ count }}</span>
					</li>
					<li>Всего:
						<span class="f12_summa">{{ price_formatted }}
							руб.</span>
					</li>
					<li>
						<a href="/plugin/basket/"><img src="{{ tpl_url }}/images/btn_order.png" alt="Оформить заказ" class="btn_order"/></a>
					</li>
				{% else %}
					<li>Корзина пуста</li>
				{% endif %}
			</ul>
		</div>
	</div>
	{% if ajaxUpdate is not defined %}
	</div>
{% endif %}
