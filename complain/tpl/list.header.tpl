{# extform info скрыт в модалке для компактности #}

<form method="post" action="{{ form_url }}" class="complain-form" data-ajax="true" data-list="1">
	<div class="table-responsive">
		<table class="table table-striped table-bordered">
			<thead>
				<tr>
					<th style="width:32px"></th>
					<th>{{ lang['complain:th.date']|default('Дата') }}</th>
					<th>{{ lang['complain:th.error']|default('Проблема') }}</th>
					<th>{{ lang['complain:th.title']|default('Материал') }}</th>
					<th>{{ lang['complain:th.publisher']|default('Отправитель') }}</th>
					<th>{{ lang['complain:th.owner']|default('Ответственный') }}</th>
					<th>{{ lang['complain:th.status']|default('Статус') }}</th>
				</tr>
			</thead>
			<tbody>
				{{ entries|raw }}
			</tbody>
		</table>
	</div>

	<div class="row g-2 align-items-center">
		<div class="col-auto">
			<label for="newstatus" class="col-form-label">{{ lang['complain:lbl.status']|default('Установить статус') }}</label>
		</div>
		<div class="col-auto">
			<select name="newstatus" id="newstatus" class="form-select form-select-sm">
				{{ status_options|raw }}
			</select>
		</div>
		<div class="col-auto">
			<button class="btn btn-primary btn-sm" name="setstatus" value="1" type="submit">{{ lang['complain:btn.setstatus']|default('Применить статус') }}</button>
			<button class="btn btn-secondary btn-sm" name="setowner" value="1" type="submit">{{ lang['complain:btn.setowner']|default('Назначить меня ответственным') }}</button>
		</div>
	</div>

	<script>
		// Делаем ETEXT доступным для inline-иконок в строках
window.ETEXT = {{ ETEXT|raw }};
	</script>
</form>
