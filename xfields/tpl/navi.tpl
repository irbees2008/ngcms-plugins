<div class="container-fluid">
	<div class="row mb-2">
	  <div class="col-sm-6 d-none d-md-block ">
			<h1 class="m-0 text-dark">xfields</h1>
	  </div><!-- /.col -->
	  <div class="col-sm-6">
		<ol class="breadcrumb float-sm-right">
			<li class="breadcrumb-item"><a href="{{ admin_url }}"><i class="fa fa-home"></i></a></li>
			<li class="breadcrumb-item"><a href="?mod=extras">{{ lang['extras'] }}</a></li>
			<li class="breadcrumb-item"><a href="?mod=extra-config&plugin=xfields">xfields</a></li>
			<li class="breadcrumb-item active" aria-current="page">{{ lang.xfconfig['list'] }}</li>
		</ol>
	  </div><!-- /.col -->
	</div><!-- /.row -->
</div>

<ul class="nav nav-pills mb-3 d-md-flex d-block" role="tablist">
	<li class="nav-item">
		<a href="?mod=extra-config&plugin=xfields&section=news" class="nav-link {{ 'news' == sectionID ? 'active' : '' }}">Новости: поля</a>
	</li>
	<li class="nav-item">
		<a href="?mod=extra-config&plugin=xfields&section=grp.news" class="nav-link {{ 'grp.news' == sectionID ? 'active' : '' }}">Новости: группы</a>
		</li>
	<li class="nav-item">
		<a href="?mod=extra-config&plugin=xfields&section=tdata" class="nav-link {{ 'tdata' == sectionID ? 'active' : '' }}">Новости: таблицы</a>
	</li>
	{% if (pluginIsActive('uprofile')) %}
	<li class="nav-item">
		<a href="?mod=extra-config&plugin=xfields&section=users" class="nav-link {{ 'users' == sectionID ? 'active' : '' }}">Пользователи: поля</a>
	</li>
	{% endif %}
</ul>
