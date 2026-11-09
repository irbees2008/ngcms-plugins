<div class="frame-crumbs">
	<div class="crumbs">
		<div class="container">
			<ul class="items items-crumbs" itemscope itemtype="http://schema.org/BreadcrumbList">
				{% for loc in location %}
					<li class="btn-crumb" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
						<a itemprop="item" href="{{ loc.url }}">
							<span class="text-el" itemprop="name">{{ loc.title }}</span>
						</a>
						<meta itemprop="position" content="{{ loop.index }}"/>
						<span class="divider">/</span>
					</li>
				{% endfor %}
				{% if (location_last) %}
					<li class="btn-crumb active" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
						<span class="text-el" itemprop="name">{{ location_last }}</span>
						<meta itemprop="position" content="{{ location|length + 1 }}"/>
					</li>
				{% endif %}
			</ul>
		</div>
	</div>
</div>
