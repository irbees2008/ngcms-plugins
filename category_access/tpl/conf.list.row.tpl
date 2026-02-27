<tr>
	<td>{{ category_name }}</td>
	<td>
		<a title="{{ lang['category_access:button_dell'] }}" onmousedown="window.location.href='{{ admin_url }}/admin.php?mod=extra-config&plugin=category_access&action=dell_category&category={{ category|url_encode }}'">
			<i class="fa fa-times" aria-hidden="true" style="color: red;"></i>
		</a>
	</td>
</tr>
