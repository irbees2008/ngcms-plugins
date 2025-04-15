<div class="container-fluid">
	<div class="row mb-2">
		<div class="col-sm-6">
			<h1 class="m-0 text-dark" style="padding: 20px 0 0 0;">
				<a href="?mod=extra-config&plugin=ads_pro">Simple Title Pro</a>
				&#8594;
				{{ global }}</h1>
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
					<a href="admin.php?mod=extras">{l_extras}</a>
				</li>
				<li class="breadcrumb-item active" aria-current="page">Simple Title Pro -
					{{ global }}</li>
			</ol>
		</div>
		<!-- /.col -->
	</div>
	<!-- /.row -->
</div>
<!-- /.container-fluid -->
<div
	class="container-fluid mt-3">
	<!-- Навигационные кнопки -->
	<div class="card mb-4">
		<div class="card-body p-2">
			<div class="d-flex flex-wrap gap-2">
				<a href="{{ admin_url }}/admin.php?mod=extra-config&plugin=simple_title_pro" class="btn btn-outline-primary">Общие</a>
				<a href="{{ admin_url }}/admin.php?mod=extra-config&plugin=simple_title_pro&action=list_static" class="btn btn-outline-primary">Список статиков</a>
				<a href="{{ admin_url }}/admin.php?mod=extra-config&plugin=simple_title_pro&action=list_cat" class="btn btn-outline-primary">Список категорий</a>
				<a href="{{ admin_url }}/admin.php?mod=extra-config&plugin=simple_title_pro&action=list_news" class="btn btn-outline-primary">Список новостей</a>
				<a href="{{ admin_url }}/admin.php?mod=extra-config&plugin=simple_title_pro&action=clear_cache" class="btn btn-outline-warning">Очистить кэш</a>
				<a href="{{ admin_url }}/admin.php?mod=extra-config&plugin=simple_title_pro&action=about" class="btn btn-outline-info">О плагине</a>
			</div>
		</div>
	</div>
	<!-- Сообщения -->
	{% if (info.true) %}
		<div class="alert alert-danger mb-3">
			{{ info.print }}
		</div>
	{% endif %}
	{% if (reklama.true) %}
		<div class="alert alert-info mb-3">
			{{ reklama.print }}
		</div>
	{% endif %}
	<!-- Основное содержимое -->
	{{ entries }}
</div>
