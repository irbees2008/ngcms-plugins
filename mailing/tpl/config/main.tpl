<style>
	.btn-outline-success {
		text-decoration: none;
	}
</style>
<div class="container-fluid">
	<div class="row mb-2">
		<div class="col-sm-6 d-none d-md-block">
			<h1 class="m-0 text-dark">Mailing</h1>
		</div>
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
				<li class="breadcrumb-item active" aria-current="page">Mailing</li>
			</ol>
		</div>
	</div>
</div>

<div class="container mt-5">
	<div class="card">
		<h5 class="card-header">Email рассылки</h5>
		<div class="card-body">
			<div class="btn-group mb-3" role="group">
				<a href="{{ admin_url }}/admin.php?mod=extra-config&plugin=mailing&action=settings" class="btn btn-outline-success">Настройки</a>
				<a href="{{ admin_url }}/admin.php?mod=extra-config&plugin=mailing&action=compose" class="btn btn-outline-success">Создать рассылку</a>
				<a href="{{ admin_url }}/admin.php?mod=extra-config&plugin=mailing&action=campaigns" class="btn btn-outline-success">Кампании</a>
				<a href="{{ admin_url }}/admin.php?mod=extra-config&plugin=mailing&action=cron" class="btn btn-outline-success">CRON</a>
				<a href="{{ admin_url }}/admin.php?mod=extra-config&plugin=mailing&action=upgrade" class="btn btn-outline-success">Обновить схему</a>
			</div>

			{{ entries }}
		</div>
	</div>
</div>
