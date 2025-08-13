<span id="bookmarks_{{ news }}">
	<a href="{{ link }}" title="{{ link_title }}" onclick="bookmarks('{{ url }}', '{{ news }}', '{{ action }}', {{ isFullNews ? 'true' : 'false' }}); return false;">
		{% if found %}
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
<script type="text/javascript">
	document.getElementById('bookmarks_{{ news }}').querySelector('a').addEventListener('click', function (e) {
e.preventDefault();
var isFull = {% if isHandler('news:news') %}true{% else %}false{% endif %};
bookmarks("{{ url }}", "{{ news }}", "{{ action }}", isFull);
});
</script>
