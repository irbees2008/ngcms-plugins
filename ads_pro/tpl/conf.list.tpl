<table class="table table-sm">
	<thead class="thead-dark">
		<tr>
			<th width="8%">#</th>
			<th width="15%">{{ lang['ads_pro:name'] }}</th>
			<th>{{ lang['ads_pro:description'] }}</th>
			<th width="10%">{{ lang['ads_pro:type'] }}</th>
			<th width="10%">{{ lang['ads_pro:state'] }}</th>
			<th width="10%">{{ lang['ads_pro:online'] }}</th>
			<th width="8%" class="text-right">{{ lang['ads_pro:action'] }}</th>
		</tr>
	</thead>
	<tbody>
		{% for item in items %}
			<tr>
				<td>
					<a href="admin.php?mod=extra-config&plugin=ads_pro&action=move_up&id={{ item.id }}" class="btn btn-sm btn-outline-primary" title="{{ lang['ads_pro:button_up'] }}">
						<i class="fa fa-arrow-up"></i>
					</a>
					<a href="admin.php?mod=extra-config&plugin=ads_pro&action=move_down&id={{ item.id }}" class="btn btn-sm btn-outline-primary" title="{{ lang['ads_pro:button_down'] }}">
						<i class="fa fa-arrow-down"></i>
					</a>
				</td>
				<td>{{ item.name }}</td>
				<td>{{ item.description }}</td>
				<td>{{ item.type }}</td>
				<td>{{ item.state }}</td>
				<td>{{ item.online }}</td>
				<td class="text-right">
					<div class="btn-group btn-group-sm">
						<a href="admin.php?mod=extra-config&plugin=ads_pro&action=edit&id={{ item.id }}" class="btn btn-outline-primary" title="{{ lang['ads_pro:button_edit'] }}">
							<i class="fa fa-pencil"></i>
						</a>
					</div>
					<div class="btn-group btn-group-sm">
						<a href="#" onclick="confirmit('admin.php?mod=extra-config&plugin=ads_pro&action=dell&id={{ item.id }}','{{ lang['ads_pro:sure_del'] }}');return false;" class="btn btn-outline-danger" title="{{ lang['ads_pro:button_dell'] }}">
							<i class="fa fa-trash-o"></i>
						</a>
					</div>
				</td>
			</tr>
		{% else %}
			<tr>
				<td colspan="7">{{ lang['ads_pro:not_found'] }}</td>
			</tr>
		{% endfor %}
	</tbody>
</table>
