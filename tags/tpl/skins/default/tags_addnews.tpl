<div class="news-tags-form">
	<div class="form-section-header">
		<h3>Теги новости</h3>
	</div>
	<div class="form-row">
		<div class="form-label">
			<label for="pTags">Список тегов:</label>
			<small class="form-hint">указывается через запятую</small>
		</div>
		<div class="form-input">
			<input type="text"
				   name="tags"
				   id="pTags"
				   class="form-control tags-input"
				   placeholder="Введите теги через запятую"
				   autocomplete="off"/>
			<div id="suggestLoader" class="suggest-loader">
				<i class="fa fa-spinner fa-spin"></i>
				<span>Загрузка...</span>
			</div>
		</div>
	</div>
</div>

<style>
.news-tags-form {
	background: #f8f9fa;
	border: 1px solid #e9ecef;
	border-radius: 5px;
	padding: 15px;
	margin: 10px 0;
}

.form-section-header h3 {
	margin: 0 0 15px 0;
	color: #333;
	font-size: 16px;
	border-bottom: 2px solid #0066cc;
	padding-bottom: 5px;
}

.form-row {
	display: flex;
	align-items: flex-start;
	gap: 15px;
}

.form-label {
	min-width: 200px;
	padding-top: 8px;
}

.form-label label {
	font-weight: bold;
	color: #333;
}

.form-hint {
	display: block;
	color: #666;
	font-size: 0.9em;
	margin-top: 3px;
}

.form-input {
	flex: 1;
	position: relative;
}

.tags-input {
	width: 100%;
	padding: 8px 12px;
	border: 1px solid #ced4da;
	border-radius: 4px;
	font-size: 14px;
}

.tags-input:focus {
	border-color: #0066cc;
	outline: none;
	box-shadow: 0 0 0 2px rgba(0, 102, 204, 0.25);
}

.suggest-loader {
	position: absolute;
	right: 10px;
	top: 50%;
	transform: translateY(-50%);
	visibility: hidden;
	color: #0066cc;
	font-size: 12px;
}

.suggest-loader i {
	margin-right: 5px;
}
</style>

<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
	// Инициализация автоподбора тегов
	if (typeof ngSuggest !== 'undefined') {
		var aSuggest = new ngSuggest('pTags', {
			'localPrefix': '{{ localPrefix }}',
			'reqMethodName': 'plugin.tags.suggest',
			'lId': 'suggestLoader',
			'hlr': 'true',
			'iMinLen': 1,
			'stCols': 2,
			'stColsClass': ['cleft', 'cright'],
			'stColsHLR': [true, false],
			'listDelimiter': ','
		});
	}
});
</script>
