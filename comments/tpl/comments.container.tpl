{% if is_external %}
<article class="full-post">
	<h1 class="title"><a href="{{ link }}">{{ title }}</a></h1>
	<p><br/>{% if entries %}{{ lang['comments:external.title'] }}{% endif %}</p>
</article>
{% endif %}
<div class="comments">
	<ul>
		<div id="new_comments_rev"></div>
		{{ entries|raw }}
		<div id="new_comments"></div>
	</ul>
</div>
{% if more_comments %}
<div class="pagination">
	<ul>
		<li>{{ more_comments|raw }}</li>
	</ul>
</div>
{% endif %}
{{ form|raw }}
{% if regonly %}
<div class="alert alert-info">
	{{ lang['comments:alert.regonly']|raw }}
</div>
{% endif %}
{% if commforbidden %}
<div class="alert alert-info">
	{{ lang['comments:alert.forbidden'] }}
</div>
{% endif %}
