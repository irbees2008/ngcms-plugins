<tbody>
	<tr>
		<th colspan="2">
			Теги новости
		</th>
	</tr>
	<tr>
		<td>Список тегов:<br/>
			<small>указывается через запятую</small>
		</td>
		<td><input style="width: 100%" name="tags" id="pTags" class="form-control"/></td>
	</tr>
</tbody>
<script language="javascript" type="text/javascript">
	// INIT NEW SUGGEST LIBRARY [ call only after full document load ]
var aSuggest = new ngSuggest('pTags', {
'localPrefix': '{{ localPrefix }}',
'reqMethodName': 'plugin.tags.suggest',
'lId': 'suggestLoader',
'hlr': 'true',
'iMinLen': 1,
'stCols': 2,
'stColsClass': [
'cleft', 'cright'
],
'stColsHLR': [
true, false
],
'listDelimiter': ','
});
</script>
