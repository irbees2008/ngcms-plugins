<div class="currencies-edit">
	<h2>
		{% if row.id %}Редактировать валюту{% else %}Добавить валюту
		{% endif %}
	</h2>
	<form method="post" action="?action=save">
		<input type="hidden" name="id" value="{{ row.id }}">
		<div class="mb-2">
			<label>ISO-код (RUB, USD, UAH…)</label>
			<input type="text" name="code" value="{{ row.code }}" maxlength="10" class="form-control" style="width:100px" required>
		</div>
		<div class="mb-2">
			<label>Название</label>
			<input type="text" name="name" value="{{ row.name }}" maxlength="100" class="form-control" style="width:300px">
		</div>
		<div class="mb-2">
			<label>Символ (₽, $, €…)</label>
			<input type="text" name="symbol" value="{{ row.symbol }}" maxlength="10" class="form-control" style="width:80px">
		</div>
		<div class="mb-2">
			<label>Курс к базовой валюте</label>
			<input type="number" name="rate" value="{{ row.rate }}" step="0.000001" min="0.000001" class="form-control" style="width:150px">
		</div>
		<div class="mb-2">
			<label>
				<input type="checkbox" name="is_base" value="1" {% if row.is_base %} checked {% endif %}>
				Базовая валюта
			</label>
		</div>
		<button type="submit" class="btn btn-success">Сохранить</button>
		<a href="?action=list" class="btn btn-secondary">Отмена</a>
	</form>
</div>
