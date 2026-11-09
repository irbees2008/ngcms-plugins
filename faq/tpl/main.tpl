<div class="container-fluid">
	<div class="row mb-2">
		<div class="col-sm-6 d-none d-md-block ">
			<h1 class="m-0 text-dark">FAQ</h1>
		</div>
		<!-- /.col -->
		<div class="col-sm-6">
			<ol class="breadcrumb float-sm-right">
				<li class="breadcrumb-item">
					<a href="admin.php">
						<i class="fa fa-home"></i>
					</a>
				</li>
				<li class="breadcrumb-item">
					<a href="admin.php?mod=extras">Управление плагинами</a>
				</li>
				<li class="breadcrumb-item active" aria-current="page">Вопросы и ответы</li>

			</ol>
		</div>
		<!-- /.col -->
	</div>
	<!-- /.row -->
</div>

<div class="card plugin-settings-card">
	<h5 class="card-header d-flex justify-content-between align-items-center" data-plugin-settings-header>
		<span>FAQ</span>{{ documentation_button|raw }}
	</h5>
	<div class="card-body plugin-settings-content">
	<div class="container mt-5">

		<div class="btn-group" role="group" aria-label="Basic example">

			<input type="button" onmousedown="javascript:window.location.href='{{ admin_url }}/admin.php?mod=extra-config&plugin=faq'" value="Список вопросов" class="btn btn-outline-success"/>
			<input type="button" onmousedown="javascript:window.location.href='{{ admin_url }}/admin.php?mod=extra-config&plugin=faq&action=add_faq'" value="Добавить вопрос" class="btn btn-outline-success"/>
		</div>
	</div>
	<div class="container mt-5">

		{{ entries }}
	</div>
	</div>
</div>
