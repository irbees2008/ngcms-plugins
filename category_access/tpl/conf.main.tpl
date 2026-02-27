<div class="container-fluid">
	<div class="row mb-2">
		<div class="col-sm-6">
			<h1 class="m-0 text-dark" style="padding: 20px 0 0 0;">{{ lang['category_access:page_title'] }}</h1>
		</div>
		<div class="col-sm-6">
			<ol class="breadcrumb float-sm-right">
				<li class="breadcrumb-item">
					<a href="{{ admin_url }}/admin.php">
						<i class="fa fa-home"></i>
					</a>
				</li>
				<li class="breadcrumb-item">
					<a href="{{ admin_url }}/admin.php?mod=extras">{{ lang['extras'] }}</a>
				</li>
				<li class="breadcrumb-item active" aria-current="page">{{ lang['category_access:page_title'] }}
					-
					{{ action }}</li>
			</ol>
		</div>
	</div>
</div>
<div class="container-fluid">
	<div class="row mb-2">
		<div class="col-sm-6 text-center">
			<div class="btn-group" role="group" aria-label="Category access navigation">
				<input type="button" onmousedown="window.location.href='{{ admin_url }}/admin.php?mod=extra-config&plugin=category_access'" value="{{ lang['category_access:button_general'] }}" class="btn btn-outline-primary"/>
				<input type="button" onmousedown="window.location.href='{{ admin_url }}/admin.php?mod=extra-config&plugin=category_access&action=list_user'" value="{{ lang['category_access:button_list_user'] }}" class="btn btn-outline-primary"/>
				<input type="button" onmousedown="window.location.href='{{ admin_url }}/admin.php?mod=extra-config&plugin=category_access&action=list_category'" value="{{ lang['category_access:button_list_category'] }}" class="btn btn-outline-primary"/>
			</div>
		</div>
		<div class="col-sm-6 text-center">
			<div class="btn-group" role="group" aria-label="Category access actions">
				<input type="button" onmousedown="window.location.href='{{ admin_url }}/admin.php?mod=extra-config&plugin=category_access&action=add_user'" value="{{ lang['category_access:button_add_user'] }}" class="btn btn-outline-primary"/>
				<input type="button" onmousedown="window.location.href='{{ admin_url }}/admin.php?mod=extra-config&plugin=category_access&action=add_category'" value="{{ lang['category_access:button_add_category'] }}" class="btn btn-outline-primary"/>
			</div>
		</div>
	</div>
	{{ content|raw }}
</div>
