<div class="pagination">
	<ul>
		{% if prevLink %}
			<li>
				<a href="{{ prevLink }}">&laquo;</a>
			</li>
		{% endif %}

		{% if startPage > 1 %}
			<li>
				<a href="{{ firstPageLink }}">1</a>
			</li>
			{% if startPage > 2 %}
				<li class="disabled">
					<span>...</span>
				</li>
			{% endif %}
		{% endif %}

		{% for page in pages %}
			{% if page.current %}
				<li class="active">
					<span>{{ page.num }}</span>
				</li>
			{% else %}
				<li>
					<a href="{{ page.link }}">{{ page.num }}</a>
				</li>
			{% endif %}
		{% endfor %}

		{% if endPage < totalPages %}
			{% if endPage < totalPages - 1 %}
				<li class="disabled">
					<span>...</span>
				</li>
			{% endif %}
			<li>
				<a href="{{ lastPageLink }}">{{ totalPages }}</a>
			</li>
		{% endif %}

		{% if nextLink %}
			<li>
				<a href="{{ nextLink }}">&raquo;</a>
			</li>
		{% endif %}
	</ul>
</div>
