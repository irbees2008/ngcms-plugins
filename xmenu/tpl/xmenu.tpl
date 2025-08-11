<ul class="xmenu menu-{{ menu_id }}">
	{% for item in items %}
		<li class="{% if item.active or item.is_current %}active{% endif %}">
			{% if item.url and not (item.active or item.is_current) %}
				<a href="{{ item.url }}">{{ item.name }}</a>
				{% if item.news_count %}
					<span class="count">({{ item.news_count }})</span>
				{% endif %}
			{% else %}
				<span class="current-item">{{ item.name }}</span>
				{% if item.news_count %}
					<span class="count">({{ item.news_count }})</span>
				{% endif %}
			{% endif %}
		</li>
	{% endfor %}
</ul>
<style>
	.xmenu li.active span.current-item {
		font-weight: bold;
		color: #ff0000; /* или ваш цвет акцентного элемента */
	}
</style>
