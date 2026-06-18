<form action="{{ php_self }}?mod=extra-config&plugin=eshop&action=list_cat" method="post" name="catz_bar">
  <div class="table-responsive">
       <table class="table table-bordered table-striped">
           <thead class="thead-dark">
				<tr class="contHead" align="left">
					<td width="5%">ID</td>
					<td width="15%">Изображение</td>
					<td width="72%">Название</td>
					<td width="12%">Количество</td>
					<td width="5%">Порядок</td>
					<td width="5%">Действие</td>
				</tr>
           </thead>
           <tbody id="categoriesTable">
			{% for entry in entries %}
			<tr align="left">
					<td width="5%" class="contentEntry1">{{ entry.id }}</td>
					<td width="10%" class="contentEntry1">{% if entry.image %}<a href="{{ entry.edit_link }}" ><img src="{{ home }}/uploads/eshop/categories/thumb/{{ entry.image }}" width="100px" height="100px"/></a>{% endif %}</td>
					<td width="40%" class="contentEntry1">
                    <div style="float: left; margin: 0px;{% if entry.prefix %}margin-left: 35px;{% endif %}">
                        {{ entry.prefix }} <a href="{{ entry.edit_link }}">{{ entry.cat_name }}</a><br/>
                        <small><a href="{{ home }}{{ entry.view_link }}" target="_blank">{{ home }}{{ entry.view_link }}</a></small>&nbsp;
                    </div>
					</td>
					<td width="15%" class="contentEntry1" style="text-align:center;vertical-align:middle">{{ entry.catCnt }}</td>
					<td width="15%" class="contentEntry1" style="text-align:center;vertical-align:middle">{{ entry.position }}</td>
					<td width="15%" class="contentEntry1" style="text-align:center;vertical-align:middle"><a href="{{ entry.del_link }}"/><img src="{{ admin_url }}/plugins/eshop/tpl/img/delete.png"></a></td>
				</tr>
			{% else %}
				<tr align="left">
					<td colspan="8" class="contentEntry1">По вашему запросу ничего не найдено.</td>
				</tr>
			{% endfor %}
            </tbody>
            </table>
			
		<div class="card-footer">
			<div class="row">
				<div class="col-lg-6 mb-2 mb-lg-0"></div>

				<div class="col-lg-6">
					<a href="{{ admin_url }}/admin.php?mod=extra-config&plugin=eshop&action=add_cat" style="float:right;" class="btn btn-outline-success">Добавить категорию</a>
				</div>
			</div>
		</div>
	</div>		
</form>