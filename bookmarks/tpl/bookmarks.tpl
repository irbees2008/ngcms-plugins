<table border="0" width="100%" cellspacing="0" cellpadding="0">
	<tr>
		<td>
			<table border="0" width="100%" cellspacing="0" cellpadding="0">
				<tr>
					<td>
						<b>
							<font color="#FFFFFF">Ваши закладки</font>
						</b>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<table border="0" width="100%" cellspacing="0" cellpadding="0">
				<tr>
					<td bgcolor="#FFFFFF">
						<ul>
							{% for entry in entries %}
								<li>
<img src="{{ entry.image ? entry.image : tpl_url ~ '/img/img-none.png' }}" width="50" height="50" alt="{{ entry.title }}"/>
									<a href="{{ entry.link }}">{{ entry.title }}</a>
								</li>
							{% endfor %}
						</ul>
						<br/>
						{% if (count) %}
							<center>
								<a href="{{ bookmarks_page }}">Все закладки</a>
							</center>
						{% endif %}
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
{% if not (count) %}Если закладок нет :)
{% endif %}
