<div class="container-fluid">
	<div class="row mb-2">
		<div class="col-sm-6 d-none d-md-block">
			<h1 class="m-0 text-dark">uprofile_del</h1>
		</div>
		<!-- /.col -->
		<div class="col-sm-6">
			<ol class="breadcrumb float-sm-right">
				<li class="breadcrumb-item"> <a href="admin.php"> <i class="fa fa-home"></i> </a></li>
				<li class="breadcrumb-item"> <a href="admin.php?mod=extras">Управление плагинами</a></li>
				<li class="breadcrumb-item"> Удаление профиля</li>
			</ol>
		</div>
		<!-- /.col -->
	</div>
	<!-- /.row -->
</div>


<div class="card plugin-settings-card">
	<div class="card-header d-flex justify-content-between align-items-center" data-plugin-settings-header>
		<span>{{global}}</span>{{documentation_button|raw}}
		<div style="margin-right: auto;float: right;">{{header}}</div>
	</div>
	<div class="card-body plugin-settings-content">
		{{entries}}
	</div>
</div>
