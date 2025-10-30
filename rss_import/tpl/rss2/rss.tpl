    <!--Last 5 Bags-->
    <h2>{{ author }}</h2>
    {% for entry in entries %}
        {% include localPath(0) ~ 'entries.tpl' %}
    {% endfor %}
    <!--/Last 5 Bags-->
<style>
	.shortits img {
		width: 100%;
		height: 185px;
		object-fit: cover;
		object-position: center;
		display: block;
	}
</style>
