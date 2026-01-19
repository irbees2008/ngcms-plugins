<div class="container-fluid">
	<div class="row mb-2">
		<div class="col-sm-6">
			<h1 class="m-0 text-dark" style="padding: 20px 0 0 0;">
				ads_pro
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
				<li class="breadcrumb-item active" aria-current="page">ads_pro -
					{{ action }}</li>
			</ol>
		</div>
	</div>
</div>
<div style="text-align : left;">
	<ul class="nav nav-tabs nav-fill mb-3 d-md-flex d-block" role="tablist">
		<li class="nav-item">
			<a href="admin.php?mod=extra-config&plugin=ads_pro" class="nav-link {{ tab_general_active }}">{{ lang['ads_pro:button_general'] }}</a>
		</li>
		<li class="nav-item">
			<a href="admin.php?mod=extra-config&plugin=ads_pro&action=list" class="nav-link {{ tab_list_active }}">{{ lang['ads_pro:button_list'] }}</a>
		</li>
		<li class="nav-item">
			<a href="admin.php?mod=extra-config&plugin=ads_pro&action=add" class="nav-link {{ tab_add_active }}">{{ lang['ads_pro:button_add'] }}</a>
		</li>
	</ul>
	<div id="userTabs" class="tab-content">
		<div id="userTabs-db" class="tab-pane show active">
			{{ entries|raw }}
		</div>
	</div>
</div>
