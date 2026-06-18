<div class="container-fluid">
	<div
		class="row mb-2">
		<!-- Левая часть заголовка -->
		<div class="col-sm-6 d-none d-md-block">
			<h1 class="m-0 text-dark">eshop</h1>
		</div>
		<!-- Правая часть: хлебные крошки -->
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
				<li class="breadcrumb-item">
					<a href="{{ admin_url }}/admin.php?mod=extra-config&plugin=eshop">Интернет магазин</a>
					→
					{{ current_title }}
				</li>
			</ol>
		</div>
	</div>
</div>

<!-- Панель навигации с кнопками -->
<div class="container-fluid mt-3">
	<div class="btn-toolbar justify-content-center" role="toolbar">
		<div class="btn-group mr-2" role="group">
			<a href="{{ plugin_url }}" class="btn btn-outline-success">Продукция</a>
			<a href="{{ plugin_url }}&action=list_cat" class="btn btn-outline-success">Категории</a>
			<a href="{{ plugin_url }}&action=list_feature" class="btn btn-outline-success">Свойства</a>
			<a href="{{ plugin_url }}&action=list_order" class="btn btn-outline-success">Заказы</a>
			<a href="{{ plugin_url }}&action=options" class="btn btn-outline-success">Настройки</a>
			<a href="{{ plugin_url }}&action=list_currencies" class="btn btn-outline-success">Валюты</a>
		</div>
		<div class="btn-group mr-2" role="group">
			<a href="{{ plugin_url }}&action=list_comment" class="btn btn-outline-success">Комментарии</a>
			<a href="{{ plugin_url }}&action=automation" class="btn btn-outline-success">Автоматизация</a>
			<a href="{{ plugin_url }}&action=list_payment" class="btn btn-outline-success">Системы оплаты</a>
			<a href="{{ plugin_url }}&action=list_payment_type" class="btn btn-outline-success">Способы оплаты</a>
			<a href="{{ plugin_url }}&action=list_delivery_type" class="btn btn-outline-success">Способы доставки</a>
		</div>
		<div class="btn-group" role="group">
			<a href="{{ plugin_url }}&action=urls" class="btn btn-outline-success">ЧПУ</a>
			<a href="{{ plugin_url }}&action=list_api" class="btn btn-outline-success">API</a>
		</div>
	</div>
</div>

<!-- Основной контент -->
{{ error }}
<div class="container-fluid mt-4">
	<div class="card">
		<div class="card-header">
			<h5 class="mb-0">{{ current_title }}</h5>
		</div>
		<div class="card-body">
			{{ entries }}
		</div>
	</div>
</div>
