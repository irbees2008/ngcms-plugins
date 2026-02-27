{% if (entries) %}
	<section class="questions">
		<h2 class="title">
			<span>{{ lang['faq:public.block.heading']|default('Вопрос / Ответ') }}</span>
		</h2>
		{% for entry in entries %}
			<div class="question_item">
				<div class="question">{{ entry.question }}</div>
				<div class="answer">{{ entry.answer }}</div>
			</div>
			<div class="line"></div>
		{% endfor %}
		<a href="{{ home }}/plugin/faq/" class="main_btn">{{ lang['faq:public.block.button']|default('Все вопросы') }}</a>
	</section>
{% endif %}
