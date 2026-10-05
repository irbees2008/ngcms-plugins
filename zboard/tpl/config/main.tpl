<div class="container-fluid">
	<div class="row mb-2">
		<div class="col-sm-6 d-none d-md-block ">
<h1 class="m-0 text-dark">{l_zboard:admin_title}</h1>
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
					<a href="admin.php?mod=extras">{l_zboard:admin_manage_plugins}</a>
				</li>
<li class="breadcrumb-item active" aria-current="page">{l_zboard:admin_breadcrumb} &rarr; {global}</li>
			</ol>
		</div>
		<!-- /.col -->
	</div>
	<!-- /.row -->
</div>
<div class="container mt-4">
	<div class="row mb-3">
		<div class="col">
			<nav class="nav nav-pills flex-wrap">
				<a class="nav-link btn btn-outline-primary mb-1 mr-1" href="{admin_url}/admin.php?mod=extra-config&plugin=zboard">{l_zboard:admin_nav_general}</a>
				<a class="nav-link btn btn-outline-primary mb-1 mr-1" href="{admin_url}/admin.php?mod=extra-config&plugin=zboard&action=list_announce">{l_zboard:admin_nav_announcements} {active}</a>
				<a class="nav-link btn btn-outline-primary mb-1 mr-1" href="{admin_url}/admin.php?mod=extra-config&plugin=zboard&action=list_cat">{l_zboard:admin_nav_categories}</a>
				<a class="nav-link btn btn-outline-primary mb-1 mr-1" href="{admin_url}/admin.php?mod=extra-config&plugin=zboard&action=list_price">{l_zboard:admin_nav_prices}</a>
				<a class="nav-link btn btn-outline-primary mb-1 mr-1" href="{admin_url}/admin.php?mod=extra-config&plugin=zboard&action=list_order">{l_zboard:admin_nav_payments}</a>
				<a class="nav-link btn btn-outline-primary mb-1" href="{admin_url}/admin.php?mod=extra-config&plugin=zboard&action=url">{l_zboard:admin_nav_urls}</a>
			</nav>
		</div>
	</div>
	<div class="row">
		<div class="col">
			{entries}
		</div>
	</div>
</div>
