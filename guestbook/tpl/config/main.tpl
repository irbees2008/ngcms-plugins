<style>
	.btn btn-outline-success {
		text-decoration: none;
	}
</style>
<div class="container-fluid">
	<div class="row mb-2">
		<div class="col-sm-6 d-none d-md-block ">
			<h1 class="m-0 text-dark">guestbook</h1>

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
				<li class="breadcrumb-item active" aria-current="page">{{ lang['gbconfig']['guestbook'] }}</li>

			</ol>
		</div>
		<!-- /.col -->
	</div>
	<!-- /.row -->
</div>
<div class="container mt-5">
	<div class="card">
		<h5 class="card-header">guestbook</h5>
		<div class="card-body">
			<div class="btn-group mb-3" role="group">
				<a href="{{ admin_url }}/admin.php?mod=extra-config&plugin=guestbook" class="btn btn-outline-success">{{ lang['gbconfig']['menu_settings'] }}</a>
				<a href="{{ admin_url }}/admin.php?mod=extra-config&plugin=guestbook&action=show_messages" class="btn btn-outline-success">{{ lang['gbconfig']['menu_messages'] }}</a>
				<a href="{{ admin_url }}/admin.php?mod=extra-config&plugin=guestbook&action=manage_fields" class="btn btn-outline-success">{{ lang['gbconfig']['menu_fields'] }}</a>
				
			</div>

			{{ entries }}
		</div>
	</div>
</div>
