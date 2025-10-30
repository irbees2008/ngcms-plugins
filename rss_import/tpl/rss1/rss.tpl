{% for entry in entries %}
    {% include localPath(0) ~ 'entries.tpl' %}
{% endfor %}
<style>
	.shortits img {
		width: 100%;
		height: 185px;
		object-fit: cover;
		object-position: center;
		display: block;
	}
</style>
