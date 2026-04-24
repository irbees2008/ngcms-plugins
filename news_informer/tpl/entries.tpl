<div class="news-informer-item">
	{% if entry.image %}
		<div class="news-informer-image">
			<a href="{{ entry.link }}"><img src="{{ entry.image }}" alt="{{ entry.title }}" style="max-width: 100%;"></a>
		</div>
	{% endif %}
	<div class="news-informer-title">
		<a href="{{ entry.link }}">{{ entry.title }}</a>
	</div>
	<div class="news-informer-meta">
		<span class="news-informer-category">
			<a href="{{ entry.category.link }}">{{ entry.category.name }}</a>
		</span>
		<span class="news-informer-date">{{ entry.date }}</span>
	</div>
</div>
<style>
	.news-informer-item {
		margin-bottom: 15px;
		padding-bottom: 15px;
		border-bottom: 1px dotted #ddd;
	}
	.news-informer-item:last-child {
		border-bottom: none;
		margin-bottom: 0;
		padding-bottom: 0;
	}
	.news-informer-image {
		margin-bottom: 5px;
	}
	.news-informer-title {
		font-weight: bold;
		margin-bottom: 3px;
	}
	.news-informer-title a {
		text-decoration: none;
		color: #0066cc;
	}
	.news-informer-title a:hover {
		text-decoration: underline;
	}
	.news-informer-meta {
		font-size: 11px;
		color: #666;
	}
	.news-informer-category:after {
		content: " • ";
	}
</style>
