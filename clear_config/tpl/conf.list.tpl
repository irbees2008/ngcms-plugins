<div class="container-fluid">
	<div class="row mb-2">
		<div class="col-sm-6">
			<h1 class="m-0 text-dark" style="padding: 20px 0 0 0;">clear_config</h1>
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
				<li class="breadcrumb-item active" aria-current="page">clear_config</li>
			</ol>
		</div>
		<!-- /.col -->
	</div>
	<!-- /.row -->
</div>
<!-- /.container-fluid -->
<div class="col-sm-12 mt-2">
	<div class="card card-danger">
		<div class="card-header">
			<h3 class="card-title">
				<i class="fa fa-exclamation-triangle"></i>
				ВАЖНО! Меры предосторожности</h3>
		</div>
		<div class="card-body">
			<p>
				<strong>Перед удалением конфигураций обязательно создайте резервную копию!</strong>
			</p>
			<p>Путь к конфигурациям:
				<code>{conf_path}</code>
			</p>
			<a href="{backup_url}" class="btn btn-success" onclick="return confirm('Создать резервную копию папки /engine/conf/?');">
				<i class="fa fa-download"></i>
				Создать резервную копию
			</a>
			<p class="mt-2 mb-0">
				<small>
					<i class="fa fa-folder-o"></i>
					Резервные копии сохраняются в:
					<code>{backup_path}</code>
				</small>
			</p>
		</div>
	</div>
</div>
{% if has_backups %}
	<div class="col-sm-12 mt-2">
		<div class="card card-success">
			<div class="card-header">
				<h3 class="card-title">
					<i class="fa fa-history"></i>
					Доступные резервные копии</h3>
			</div>
			<div class="card-body">
				<table class="table table-sm table-bordered table-hover">
					<thead class="thead-light">
						<tr>
							<th>Файл</th>
							<th width="120">Размер</th>
							<th width="180">Дата создания</th>
							<th width="200">Действия</th>
						</tr>
					</thead>
					<tbody>
						{% for backup in backups %}
							<tr>
								<td>
									<code>{{ backup.name }}</code>
								</td>
								<td>{{ backup.size }}
									KB</td>
								<td>{{ backup.date }}</td>
								<td>
									<a href="{{ backup.url }}" class="btn btn-sm btn-warning" title="Восстановить из этого бэкапа">
										<i class="fa fa-refresh"></i>
										Восстановить
									</a>
									<a href="{{ backup.delete_url }}" class="btn btn-sm btn-danger" title="Удалить эту резервную копию" onclick="return confirm('Удалить резервную копию {{ backup.name }}?');">
										<i class="fa fa-trash"></i>
									</a>
								</td>
							</tr>
						{% endfor %}
					</tbody>
				</table>
			</div>
		</div>
	</div>
{% endif %}
<div class="col-sm-12 mt-2">
	<div class="card card-info">
		<div class="card-header">
			<h3 class="card-title">
				<i class="fa fa-info-circle"></i>
				Рекомендации по работе</h3>
		</div>
		<div class="card-body">
			<ul>
				<li>
					<strong>Активные плагины (active):</strong>
					удаляет плагин из списка активных, он перестанет загружаться</li>
				<li>
					<strong>Действия (actions):</strong>
					удаляет привязку действий плагина к хукам системы</li>
				<li>
					<strong>Установленные (installed):</strong>
					удаляет отметку об установке плагина</li>
				<li>
					<strong>Библиотеки (libs):</strong>
					удаляет регистрацию библиотек плагина</li>
				<li>
					<strong>Конфигурация (config):</strong>
					удаляет все настройки плагина</li>
				<li>
					<strong>URL команды (urlcmd):</strong>
					удаляет пользовательские маршруты URL</li>
			</ul>
			<div class="alert alert-warning mt-2">
				<i class="fa fa-lightbulb-o"></i>
				<strong>Совет:</strong>
				Используйте этот плагин для очистки конфигураций удалённых или неисправных плагинов.
			</div>
		</div>
	</div>
</div>
<div class="col-sm-12 mt-2">
	<div class="card">
		<div class="card-header">
			<h3 class="card-title">Список конфигураций плагинов</h3>
		</div>
		<div class="card-body">
			<table class="table table-sm table-bordered table-hover">
				<thead class="thead-light">
					<tr>
						<th width="30%">Код плагина</th>
						<th>Удалить конфигурацию</th>
					</tr>
				</thead>
				<tbody>
					{entries}
				</tbody>
			</table>
		</div>
	</div>
</div>
