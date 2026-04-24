<div class="news-informer">
	<h3>Последние новости</h3>
	<div class="news-informer-list">
		{% for entry in entries %}
			{% include localPath(0) ~ "entries.tpl" %}
		{% endfor %}
	</div>
	<div class="news-informer-footer">
		<a href="{{ home }}">Все новости</a>
	</div>
</div>
<style>
	.news-informer {
		font-family: Arial, sans-serif;
		width: 300px;
		border: 1px solid #ddd;
		background: #f9f9f9;
		padding: 10px;
	}
	.news-informer h3 {
		margin: 0 0 10px;
		padding: 0 0 5px;
		font-size: 16px;
		color: #333;
		border-bottom: 1px solid #ddd;
	}
	.news-informer-list {
		margin-bottom: 10px;
	}
	.news-informer-footer {
		text-align: right;
		font-size: 12px;
	}
</style>
