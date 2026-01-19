<div class="container-fluid">
	<div class="row mb-2">
		<div class="col-sm-6 d-none d-md-block ">
<h1 class="m-0 text-dark">Доска объявлений</h1>
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
<li class="breadcrumb-item active" aria-current="page">Доска объявлений => {global}</li>
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
				<a class="nav-link btn btn-outline-primary mb-1 mr-1" href="{admin_url}/admin.php?mod=extra-config&plugin=zboard">Общие</a>
				<a class="nav-link btn btn-outline-primary mb-1 mr-1" href="{admin_url}/admin.php?mod=extra-config&plugin=zboard&action=list_announce">Список объявлений {active}</a>
				<a class="nav-link btn btn-outline-primary mb-1 mr-1" href="{admin_url}/admin.php?mod=extra-config&plugin=zboard&action=list_cat">Список категорий</a>
				<a class="nav-link btn btn-outline-primary mb-1 mr-1" href="{admin_url}/admin.php?mod=extra-config&plugin=zboard&action=list_price">Прайс</a>
				<a class="nav-link btn btn-outline-primary mb-1 mr-1" href="{admin_url}/admin.php?mod=extra-config&plugin=zboard&action=list_order">Оплаты</a>
				<a class="nav-link btn btn-outline-primary mb-1" href="{admin_url}/admin.php?mod=extra-config&plugin=zboard&action=url">ЧПУ</a>
			</nav>
		</div>
	</div>
	<div class="row">
		<div class="col">
			{entries}
		</div>
	</div>
</div>
