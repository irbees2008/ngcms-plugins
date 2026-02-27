<div class="guestbook-container">
	{%- if errors or success %}
		 <script>
					document.addEventListener('DOMContentLoaded', function () {
						if (typeof window.showToast !== 'function') { return; }
						{% for error in errors %}
						window.showToast({{ error|json_encode|raw }}, { type: 'error' });
						{% endfor %}
						{% for msg in success %}
						window.showToast({{ msg|json_encode|raw }}, { type: 'success' });
						{% endfor %}
					});
					</script>
	{%- endif %}

	<!-- Рабочая форма (ваш вариант) -->
	<form
		method="post" action="{{ home }}/plugin/guestbook/?action=add" id="gbForm" accept-charset="UTF-8" class="guestbook-form">

		<!-- Скрытые поля -->
		<input
		type="hidden" name="ip" value="{{ ip }}">

		<!-- Поле автора -->
		{% if global.user.id %}
			<input type="hidden" name="author" value="{{ global.user.name }}">
			<p class="guestbook-logged-user">Вы вошли как:
				<strong>{{ global.user.name }}</strong>
			</p>
		{% else %}
			<div class="guestbook-form-group">
				<label class="guestbook-label">Ваше имя:</label>
				<input type="text" name="author" class="guestbook-input" required>
			</div>
		{% endif %}

		<!-- Основное поле сообщения -->
		<div class="guestbook-form-group">
			<label class="guestbook-label">Ваш отзыв:</label>
			<textarea name="content" class="guestbook-textarea" rows="5" required></textarea>
		</div>

		<!-- CAPTCHA -->
		{% if captcha_widget %}
			<div class="guestbook-form-group">
				{{ captcha_widget|raw }}
			</div>
		{% endif %}

		<!-- Кнопка отправки -->
		<button type="submit" class="guestbook-button">Отправить отзыв</button>
	</form>

	<!-- Список существующих отзывов -->
	<div class="guestbook-entries">
		{% for entry in entries %}
			<div class="guestbook-entry">
				<div class="guestbook-entry-header">
					<strong class="guestbook-entry-author">{{ entry.author }}</strong>
					<span class="guestbook-entry-date">{{ entry.date }}</span>
				</div>
				<div class="guestbook-entry-content">
					{{ entry.message }}
				</div>
				{% if entry.answer %}
					<div class="guestbook-entry-answer">
						<strong>Ответ:</strong>
						{{ entry.answer }}
					</div>
				{% endif %}
			</div>
		{% endfor %}
	</div>

	<!-- Пагинация -->
	{% if pages %}
		<div class="guestbook-pagination">
			{{ pages }}
		</div>
	{% endif %}
</div>
<style>
	.guestbook-container {
		max-width: 800px;
		margin: 30px auto;
		font-family: Arial, sans-serif;
		color: #333;
	}

	.guestbook-form {
		background: #f9f9f9;
		padding: 20px;
		border-radius: 8px;
		box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
	}

	.guestbook-form-group {
		margin-bottom: 15px;
	}

	.guestbook-label {
		display: block;
		margin-bottom: 5px;
		font-weight: bold;
	}

	.guestbook-input,
	.guestbook-textarea {
		width: 100%;
		padding: 10px;
		border: 1px solid #ddd;
		border-radius: 4px;
		font-size: 16px;
		box-sizing: border-box;
	}

	.guestbook-textarea {
		min-height: 120px;
		resize: vertical;
	}

	.guestbook-button {
		background-color: #007bff;
		color: white;
		padding: 10px 20px;
		border: none;
		border-radius: 4px;
		cursor: pointer;
		font-size: 16px;
		transition: background-color 0.3s;
	}

	.guestbook-button:hover {
		background-color: #0069d9;
	}

	.guestbook-logged-user {
		margin-bottom: 15px;
		font-size: 16px;
	}

	.guestbook-entries {
		margin-top: 30px;
	}

	.guestbook-entry {
		border-bottom: 1px solid #eee;
		padding: 15px 0;
	}

	.guestbook-entry-header {
		display: flex;
		justify-content: space-between;
		margin-bottom: 10px;
	}

	.guestbook-entry-author {
		font-size: 18px;
		color: #333;
	}

	.guestbook-entry-date {
		color: #6c757d;
		font-size: 14px;
	}

	.guestbook-entry-content {
		margin: 10px 0;
		line-height: 1.5;
	}

	.guestbook-entry-answer {
		background: #f8f9fa;
		padding: 10px;
		border-radius: 4px;
		margin-top: 10px;
		border-left: 3px solid #007bff;
	}

	.guestbook-pagination {
		margin-top: 20px;
		display: flex;
		justify-content: center;
	}
</style>
