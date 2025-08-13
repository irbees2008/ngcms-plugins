<span id="bookmarks_{{ news }}">
	<a href="#" title="{{ link_title }}" onclick="bookmarks('{{ url }}', '{{ news }}', '{{ action }}', {{ isFullNews ? 'true' : 'false' }}); return false;">
		{% if action == 'delete' %}
			{% if isFullNews %}
				удалить закладку
			{% else %}
				-
			{% endif %}
		{% else %}
			{% if isFullNews %}
				добавить закладку
			{% else %}
				+
			{% endif %}
		{% endif %}
	</a>
	{% if isFullNews %}
		<span id="bookmarks_counter_{{ news }}">{{ counter }}</span>
	{% endif %}
</span>
