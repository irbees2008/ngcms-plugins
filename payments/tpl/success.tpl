<div class="payments-success">
	<div class="alert alert-success">
		<h2>{{ lang.success_title }}</h2>
		{% if order %}
			<p>{{ lang.order_label }} #{{ order.id }}
				{{ lang.success_order_paid }}</p>
		{% endif %}
	</div>
	<a href="/" class="btn btn-primary">{{ lang.home }}</a>
</div>
