<div class="tags-sidebar-wrapper">
	<div class="tags-sidebar-header">
		<h3 class="tags-sidebar-title">Облако тегов</h3>
	</div>
	<div class="tags-sidebar-content">
		<div class="tags-list">
			{{ entries }}
		</div>
	</div>
</div>

<style>
.tags-sidebar-wrapper {
	width: 100%;
	border: 1px solid #ddd;
	border-radius: 5px;
	margin: 10px 0;
	background: #fff;
}

.tags-sidebar-header {
	background: linear-gradient(to bottom, #f5f5f5, #e8e8e8);
	padding: 8px 12px;
	border-bottom: 1px solid #ddd;
	border-radius: 5px 5px 0 0;
}

.tags-sidebar-title {
	margin: 0;
	font-size: 14px;
	font-weight: bold;
	color: #333;
}

.tags-sidebar-content {
	padding: 10px;
}

.tags-list {
	line-height: 1.5;
}

.tags-list a {
	color: #0066cc;
	text-decoration: none;
	margin: 2px;
	padding: 2px 4px;
	border-radius: 3px;
	transition: background-color 0.3s ease;
}

.tags-list a:hover {
	background-color: #e8f4fd;
	text-decoration: underline;
}

.tags-list sup {
	font-size: 0.7em;
}

.tags-list sup small font {
	color: #cc0000 !important;
}
</style>
