{{ error }}

<form method="post" action="" name="form">
	<dl>
		<dt>
			<label>{{ lang['faq:form.question']|default('Вопрос') }}
				{{ lang['faq:form.required']|default('[*]') }}</label>
		</dt>
		<dd>
			<textarea type="text" name="question" rows="8" cols="100">{{ question }}</textarea>
		</dd>
	</dl>
	<dl>
		<dt>
			<label>{{ lang['faq:form.answer']|default('Ответ') }}
				{{ lang['faq:form.required']|default('[*]') }}</label>
		</dt>
		<dd>
			<textarea type="text" name="answer" rows="8" cols="100">{{ answer }}</textarea>
		</dd>
	</dl>
	<div class="card-footer text-center"><input type="reset" class="btn btn-outline-success" value="{{ lang['faq:form.reset']|default('Сброс') }}"/>&nbsp;<input name="submit" type="submit" class="btn btn-outline-success" value="{{ lang['faq:form.submit']|default('Сохранить') }}"/></div>

</form>
