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
			<li class="breadcrumb-item active" aria-current="page">
				{{ lang.xfconfig['editfield'] }} (<a href="?mod=extra-config&plugin=xfields&action=edit&section={{ sectionID }}&field={{ id }}">{{ id }}</a>)
			</li>
		</ol>
	  </div><!-- /.col -->
	</div><!-- /.row -->
</div>

<div class="card">
	<div class="card-body">
		{{ lang.xfconfig['savedone'] }}
	</div>

	<div class="card-footer">
		<a href="admin.php?mod=extra-config&amp;plugin=xfields" class="btn btn-outline-success">xfields</a>
	</div>
</div>
