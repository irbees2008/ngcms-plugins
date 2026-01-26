<div class="container-fluid">
	<div class="row mb-2">
		<div class="col-sm-6">
			<h1 class="m-0 text-dark" style="padding: 20px 0 0 0;">
				{{ lang['subscribe_comments:title'] ?? 'Подписка на комментарии' }}
			</h1>
		</div>
		<div class="col-sm-6">
			<ol class="breadcrumb float-sm-right">
				<li class="breadcrumb-item">
					<a href="admin.php">
						<i class="fa fa-home"></i>
					</a>
				</li>
				<li class="breadcrumb-item">
					<a href="admin.php?mod=extras">{{ lang['extras'] }}</a>
				</li>
				<li class="breadcrumb-item active" aria-current="page">Подписка на комментарии -
					{{ action }}</li>
			</ol>
		</div>
	</div>
</div>
<div style="text-align : left;">
	<ul class="nav nav-tabs nav-fill mb-3 d-md-flex d-block" role="tablist">
		<li class="nav-item">
			<a href="admin.php?mod=extra-config&plugin=subscribe_comments" class="nav-link {{ tab_general_active }}">Общие настройки</a>
		</li>
		<li class="nav-item">
			<a href="admin.php?mod=extra-config&plugin=subscribe_comments&action=list_subscribe" class="nav-link {{ tab_list_active }}">Список подписчиков</a>
		</li>
		{% if not hide_delayed %}
			<li class="nav-item">
				<a href="admin.php?mod=extra-config&plugin=subscribe_comments&action=list_subscribe_post" class="nav-link {{ tab_post_active }}">Сформированные письма</a>
			</li>
		{% endif %}
	</ul>
	<div id="subscribeTabs" class="tab-content">
		<div id="subscribeTabs-content" class="tab-pane show active">
			<!-- Debug: entries_cron = '{{ entries_cron }}' -->
			<!-- Debug: entries length = {{ entries|length }} -->
			{{ entries_cron|raw }}
		{{ entries|raw }}
	</div>
</div></div>
