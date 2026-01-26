<div class="ng-advanced-captcha" data-form-id="{{ form_id }}">
	<input type="hidden" name="ng_captcha_form_id" value="{{ form_id }}">
	<input type="hidden" name="ng_captcha_answer" id="ng_captcha_answer_{{ form_id }}">
	<input type="hidden" name="ng_captcha_token" id="ng_captcha_token_{{ form_id }}">
	<input type="hidden" name="ng_captcha_interactions" id="ng_captcha_interactions_{{ form_id }}">
	{{ honeypot|raw }}

	{% if type == 'math' %}
		<div class="captcha-math">
			<label class="captcha-label">Решите пример:</label>
			<div class="captcha-question">{{ challenge }}</div>
			<input type="text" class="captcha-input" id="captcha_input_{{ form_id }}" placeholder="Введите ответ" autocomplete="off" required>
		</div>
	{% elseif type == 'text' %}
		<div class="captcha-text">
			<label class="captcha-label">Введите символы с картинки:</label>
			<div class="captcha-image-container">
				<canvas id="captcha_canvas_{{ form_id }}" width="200" height="60"></canvas>
			</div>
			<input type="text" class="captcha-input" id="captcha_input_{{ form_id }}" placeholder="Введите символы" autocomplete="off" required>
			<button type="button" class="captcha-refresh" onclick="refreshCaptcha('{{ form_id }}')">
				🔄 Обновить
			</button>
		</div>
	{% elseif type == 'question' %}
		<div class="captcha-question-type">
			<label class="captcha-label">Ответьте на вопрос:</label>
			<div class="captcha-question">{{ challenge }}</div>
			<input type="text" class="captcha-input" id="captcha_input_{{ form_id }}" placeholder="Введите ответ" autocomplete="off" required>
		</div>
	{% elseif type == 'checkbox' %}
		<div class="captcha-checkbox">
			<label class="captcha-checkbox-label">
				<input type="checkbox" id="captcha_checkbox_{{ form_id }}" class="captcha-checkbox-input">
				<span class="captcha-checkbox-box"></span>
				<span class="captcha-checkbox-text">{{ challenge }}</span>
			</label>
		</div>
	{% elseif type == 'slider' %}
		<div class="captcha-slider">
			<label class="captcha-label">{{ challenge }}</label>
			<div class="captcha-slider-track">
				<div class="captcha-slider-thumb" id="captcha_slider_{{ form_id }}">
					<span class="captcha-slider-arrow">→</span>
				</div>
			</div>
			<div class="captcha-slider-feedback" id="captcha_feedback_{{ form_id }}"></div>
		</div>
	{% endif %}
</div>

 <script>
(function() {
    const formId = '{{ form_id }}';
    const type = '{{ type }}';
    const challenge = '{{ challenge }}';

    // Инициализация в зависимости от типа
    if (type === 'text') {
        initTextCaptcha(formId, challenge);
    } else if (type === 'checkbox') {
        initCheckboxCaptcha(formId);
    } else if (type === 'slider') {
        initSliderCaptcha(formId);
    } else {
        initSimpleCaptcha(formId);
    }
})();
</script>
