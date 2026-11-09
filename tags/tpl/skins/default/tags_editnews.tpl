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
		<td>
			<div style="position: relative;">
				<div class="input-group">
					<div class="input-group-prepend">
						<button type="button" class="btn btn-outline-primary" title="Сгенерировать теги" aria-label="Сгенерировать теги" data-bs-toggle="tooltip" data-tags-mode="{mode}" data-news-id="{newsid}" data-news-token="{token}" data-tags-rpc="{localPrefix}/engine/rpc.php?json=1" onclick="generateNewsTags(this)">
							<i class="fa fa-magic"></i>
						</button>
					</div>
					<input id="pTags" name="tags" value="{{ tags }}" autocomplete="off" class="form-control"/>
				</div>
				<div id="tagsSuggestWindow"></div>
			</div>
			<span id="suggestLoader" style="width: 20px; visibility: hidden;">
				<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>
				<span class="sr-only">Loading...</span>
			</span>
			<small id="tagsGenerateStatus" class="form-text text-muted" role="status" aria-live="polite"></small>
		</td>
	</tr>
</tbody>
<script src="{localPrefix}/engine/plugins/tags/tpl/tags_generate.js?v=0.31.1"></script>
<script language="javascript" type="text/javascript">
	// INIT NEW SUGGEST LIBRARY [ call only after full document load ]
var aSuggest = new ngSuggest('pTags', {
'localPrefix': '{localPrefix}',
'reqMethodName': 'plugin.tags.suggest',
'sId': 'tagsSuggestWindow',
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
aSuggest.searchDIV.style.top = 'calc(100% + 3px)';
</script>
