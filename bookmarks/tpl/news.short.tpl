[TWIG]
<article class="article">
	<div class="article-img">
		<img src="{{ entry.image|default(tpl_url~'/img/img-none.png') }}" width="315" height="161"/>
		<div class="article-cat">{{ news.categories.masterText }}</div>
	</div>
	<div class="article-title">
		<a href="{{ news.url.full }}">{{ news.title }}</a>
	</div>
	<div class="article-meta">
		<span>{{ news.date }}</span>
		|
		<span>
			{% if pluginIsActive('uprofile') %}
				<a href="{{ news.author.url }}">
				{% endif %}
				{{ news.author.name }}
				{% if pluginIsActive('uprofile') %}
				</a>
			{% endif %}
		</span>
	</div>
	<div class="article-text">
		<p>{{ news.short|truncateHTML(150,'...')|striptags }}</p>
	</div>
</article>
[/TWIG]
