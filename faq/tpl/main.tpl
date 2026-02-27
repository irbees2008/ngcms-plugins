<div class="container-fluid">
	<div class="row mb-2">
		<div class="col-sm-6 d-none d-md-block ">
			<h1 class="m-0 text-dark">{{ lang['faq:page.title']|default('Вопросы и ответы') }}</h1>
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
					<a href="admin.php?mod=extras">{{ lang['faq:breadcrumb.plugins']|default('Управление плагинами') }}</a>
				</li>
				<li class="breadcrumb-item active" aria-current="page">{{ lang['faq:breadcrumb.current']|default('Вопросы и ответы') }}</li>

			</ol>
		</div>
		<!-- /.col -->
	</div>
	<!-- /.row -->
</div>

<div class="card">
	<h5 class="card-header">{{ lang['faq:card.title']|default('FAQ') }}</h5>
	<div class="container mt-5">

		<div class="btn-group" role="group" aria-label="FAQ actions">

			<input type="button" onmousedown="javascript:window.location.href='{{ admin_url }}/admin.php?mod=extra-config&plugin=faq'" value="{{ lang['faq:button.list']|default('Список вопросов') }}" class="btn btn-outline-success"/>
			<input type="button" onmousedown="javascript:window.location.href='{{ admin_url }}/admin.php?mod=extra-config&plugin=faq&action=add_faq'" value="{{ lang['faq:button.add']|default('Добавить вопрос') }}" class="btn btn-outline-success"/>
		</div>
	</div>
	<div class="container mt-5">

		{{ entries }}
	</div>

</div>
