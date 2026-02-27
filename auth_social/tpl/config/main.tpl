<div class="container-fluid">
	<div class="row mb-2">
		<div class="col-sm-6 d-none d-md-block ">
			<h1 class="m-0 text-dark">{{ lang['auth_social:page.title'] }}</h1>
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
					<a href="admin.php?mod=extras">{{ lang['auth_social:breadcrumb.plugins'] }}</a>
				</li>
				<li class="breadcrumb-item active" aria-current="page">{{ lang['auth_social:breadcrumb.current'] }}</li>
			</ol>
		</div>
		<!-- /.col -->
	</div>
	<!-- /.row -->
</div>

<div class="card">
	<h5 class="card-header">{{ lang['auth_social:card.title'] }}</h5>
	{{ entries }}
</div>
