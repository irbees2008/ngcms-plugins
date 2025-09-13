<div class="container-fluid">
	<div class="row mb-2">
		<div class="col-sm-6">
			<h1 class="m-0 text-dark" style="padding: 20px 0 0 0;">
				<a href="?mod=extra-config&plugin=ads_pro">feedback</a>
			</h1>
		</div>
		<!-- /.col -->
		<div class="col-sm-6">
			<ol class="breadcrumb float-sm-right">
				<li class="breadcrumb-item">
					<a href="{{ admin_url }}">
						<i class="fa fa-home"></i>
					</a>
				</li>
				<li class="breadcrumb-item">
					<a href="?mod=extras">{{ lang['extras'] }}</a>
				</li>
				<li class="breadcrumb-item {{ not(flags.haveForm) ? 'active' : '' }}">
					<a href="?mod=extra-config&plugin=feedback">feedback</a>
				</li>
				{% if (flags.haveForm) %}
					<li class="breadcrumb-item {{ not(flags.haveField) and not(flags.addField) ? 'active' : '' }}">
						{{ lang['feedback:form'] }}
						[<a href="?mod=extra-config&plugin=feedback&action=form&id={{ formID }}">{{ formName }}</a>]
					</li>
					{% if (flags.haveField) %}
						<li class="breadcrumb-item active">
							{{ lang['feedback:field'] }}
							[<a href="?mod=extra-config&plugin=feedback&action=row&form_id={{ formID }}&row={{ fieldName }}">{{ fieldName }}</a>]
						</li>
					{% endif %}
					{% if (flags.addField) %}
						<li class="breadcrumb-item active">{{ lang['feedback:adding_new_field'] }}</li>
					{% endif %}
				{% else %}
					<li class="breadcrumb-item active">{{ lang['feedback:forms_list'] }}</li>
				{% endif %}
			</ol>
		</div>
		<!-- /.col -->
	</div>
	<!-- /.row -->
</div>
