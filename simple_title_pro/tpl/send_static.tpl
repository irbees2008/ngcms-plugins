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
						<label class="form-label fw-bold">{{ lang['form.static_title'] }}</label>
						<div class="form-text text-muted">
							{{ lang['help.static_title'] }}
						</div>
					</div>
					<div class="col-md-6">
						<div class="input-group has-validation">
							<input type="text" class="form-control rounded-end-0" name="title" value="{{ title }}" placeholder="{{ lang['input.title_placeholder'] }}" aria-label="{{ lang['form.static_title'] }}">
							<select class="form-select rounded-start-0" name="id" aria-label="{{ lang['input.select_static'] }}">
								{{ options }}
							</select>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="d-grid gap-2 col-md-4 mx-auto">
			<button type="submit" name="submit" class="btn btn-primary btn-lg">
				{{ lang['btn.save'] }}
			</button>
		</div>
	</form>
</div>
