<div class="card card-widget" itemscope itemtype="http://schema.org/Article">
	{% for entry in entries %}
		{% if (entry.cat_id) %}
			{{ entry.cat_link }}<br/>
		{% elseif (entry.news_id) %}
			<div style="padding-left:50px">
				<a href="{{ entry.news_link }}">{{ entry.news_title }}</a>
				{{ entry.news_date|date("d.m.Y H:i") }}</div>
		{% elseif (entry.static_id) %}
			<a href="{{ entry.static_link }}">{{ entry.static_title }}</a>
			{{ entry.static_date|date("d.m.Y H:i") }}<br/>
		{% endif %}
	{% endfor %}
</div>
<div class="col-12">
	<div class="card">
		<div class="card-body">
			<ul class="pagination justify-content-center" style="margin-bottom: 0rem;">
				{{ pagination }}
			</ul>
		</div>
	</div>
</div>
