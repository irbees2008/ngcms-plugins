<div class="container-fluid mt-3">
	<div class="card">
		<div
			class="card-body p-0">
			<!-- Заголовок таблицы -->
			<div class="table-responsive">
				<table class="table table-striped table-hover mb-0">
					<thead class="table-light">
						<tr>
							<th scope="col" style="width: 10%;" class="text-nowrap">#</th>
							<th scope="col" style="width: 40%;">Название</th>
							<th scope="col" style="width: 60%;">Заголовок</th>
							<th scope="col" style="width: 80%;">Действие</th>
						</tr>
					</thead>
					<tbody>
						{{ entries }}
					</tbody>
				</table>
			</div>

			<!-- Кнопка добавления -->
			<div class="card-footer bg-transparent border-0 d-flex justify-content-end">
				<a href="{{ admin_url }}/admin.php?mod=extra-config&plugin=simple_title_pro&action=send_title&do=cat" class="btn btn-primary">
					Добавить категорию
				</a>
			</div>

			<!-- Пагинация -->
			<div class="card-footer bg-transparent border-0 text-center">
				{{ pagesss }}
			</div>
		</div>
	</div>
</div>
