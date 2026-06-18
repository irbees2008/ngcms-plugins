<!-- Вывод ошибок -->
{% if entries.error %}
	<div class="alert alert-danger" role="alert">
		{{ entries.error }}
	</div>
{% endif %}

<!-- Форма -->
<form method="post" action="" enctype="multipart/form-data">
	<div class="card">
		<div
			class="card-body">
			<!-- Поле "Токен" -->
			<div class="form-group row mb-3">
				<label for="token" class="col-lg-3 col-form-label">Токен</label>
				<div class="col-lg-9">
					<input type="text" id="token" name="token" value="{{ entries.token }}" class="form-control" placeholder="Введите токен"/>
				</div>
			</div>
		</div>

		<!-- Подвал карточки -->
		<div class="card-footer">
			<div class="row">
				<div class="col-lg-6 mb-2 mb-lg-0"></div>
				<div class="col-lg-6 text-right">
					<button type="submit" name="submit" class="btn btn-success">
						Сохранить
					</button>
				</div>
			</div>
		</div>
	</div>
</form>
