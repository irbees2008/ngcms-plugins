{% if (entries) %}
	<h2 class="title top">
		<span>{{ lang['faq:public.page.heading']|default('Вопросы / Ответы') }}</span>
	</h2>
	<section class="questions">
		{% for entry in entries %}
			<div class="question_item">
				<div class="question">{{ entry.question }}</div>
				<div class="answer">{{ entry.answer }}</div>
			</div>
			<div class="line"></div>
		{% endfor %}
	</section>
{% else %}
	<div class="questions-empty">{{ lang['faq:public.empty']|default('Записей пока нет') }}</div>
{% endif %}
