<form method="post" action="{{ form_url }}" class="complain-form" data-ajax="true">
	<input type="hidden" name="ds_id" value="{{ ds_id }}">
	<input type="hidden" name="entry_id" value="{{ entry_id }}">

	<div class="mb-2">
		<label class="form-label">{{ lang['complain:lbl.error']|default('Что не так?') }}</label>
		<select name="error" class="form-select" required>
			{{ errorlist|raw }}
		</select>
	</div>

	[text]
	<div class="mb-2">
		<label class="form-label">{{ lang['complain:lbl.text']|default('Комментарий (необязательно)') }}</label>
		<textarea name="error_text" class="form-control" rows="3"></textarea>
	</div>
	[/text]
	
			[notify]
	<div class="form-check mb-2">
		<input class="form-check-input" type="checkbox" name="notify" id="complain_notify_ext">
		<label class="form-check-label" for="complain_notify_ext">{{ lang['complain:lbl.notify']|default('Уведомлять меня об изменениях') }}</label>
	</div>
	[/notify]
	
			[email]
	<div class="mb-2">
		<label class="form-label">E-mail</label>
		<input type="email" name="mail" class="form-control" placeholder="name@example.com">
	</div>
	[/email]

	<div class="d-flex gap-2">
		<button type="submit" class="btn btn-primary">{{ lang['complain:btn.send']|default('Отправить') }}</button>
		<button type="button" class="btn btn-outline-secondary" data-close-modal>{{ lang['complain:btn.cancel']|default('Отмена') }}</button>
	</div>
</form>
