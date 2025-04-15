<div class="container-fluid mt-3">
	{% if error %}
		<div class="alert alert-danger mb-4">
			{{ error }}
		</div>
	{% endif %}

	<form method="post" action="">
		<div class="card mb-4">
			<div class="card-body">
				<div class="row align-items-center mb-3">
					<div class="col-md-6">
						<label class="form-label">Заголовок категории</label>
						<small class="text-muted d-block">
							Текст поля &lt;title&gt;&lt;/title&gt; для категории (разрешено %cat%, %num% и %home%)
						</small>
					</div>
					<div class="col-md-6">
						<div class="input-group">
							<input type="text" class="form-control" name="title" value="{{ title }}" placeholder="Введите заголовок">
							<select class="form-select" name="id">
								{{ options }}
							</select>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="text-center">
			<button type="submit" name="submit" class="btn btn-primary px-4">
				Добавить
			</button>
		</div>
	</form>
</div>
