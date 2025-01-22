<div class="container-fluid">
	<div class="row mb-2">
		<div class="col-sm-6 d-none d-md-block ">
			<h1 class="m-0 text-dark">suser</h1>

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
				<li class="breadcrumb-item">Список пользователей - {global}</li>
			</ol>
		</div>
		<!-- /.col -->
	</div>
	<!-- /.row -->
</div>

<div class="container mt-5">
	<div class="card">
		<h5 class="card-header">suser</h5>

		<div class="card-body">
			<div class="btn-group mb-3" role="group">

				<input type="button" onmousedown="javascript:window.location.href='{admin_url}/admin.php?mod=extra-config&plugin=suser'" value="Общие" class="btn btn-outline-success"/>

				<input type="button" onmousedown="javascript:window.location.href='{admin_url}/admin.php?mod=extra-config&plugin=suser&action=url'" value="ЧПУ" class="btn btn-outline-success"/>
			</div>

			{entries}

		</div>
	</div>
</div>
