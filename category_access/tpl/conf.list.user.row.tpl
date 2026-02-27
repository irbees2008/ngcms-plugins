<tr>
	<td>{{ user }}</td>
	<td>{{ category }}</td>
	<td>
		<a title="{{ lang['category_access:button_edit_user'] }}" onmousedown="window.location.href='{{ admin_url }}/admin.php?mod=extra-config&plugin=category_access&action=add_user&user={{ user|url_encode }}'">
			<i class="fa fa-plus fa-lg" aria-hidden="true" style="color: green;"></i>
		</a>
		<a title="{{ lang['category_access:button_dell'] }}" onmousedown="window.location.href='{{ admin_url }}/admin.php?mod=extra-config&plugin=category_access&action=dell_user&user={{ user|url_encode }}'">
			<i class="fa fa-times fa-lg" aria-hidden="true" style="color: red;"></i>
		</a>
	</td>
</tr>
