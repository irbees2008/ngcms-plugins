<!--[if lt IE 9]><script type="text/javascript" src="{{ admin_url }}/plugins/tags/tpl/skins/js/excanvas.js"></script><![endif]-->
<script type="text/javascript" src="{{ admin_url }}/plugins/tags/tpl/skins/3d/js/tagcanvas.js"></script>
		<div id="myCanvasContainer" class="canvas-container">
			<canvas width="200" height="200" id="myCanvas" class="tag-canvas">
				<div class="canvas-fallback">
					<p class="fallback-message">Ваш браузер не поддерживает HTML5 Canvas</p>
					<ul id="weightTags" class="fallback-tags">
						{{ entries }}
					</ul>
				</div>
			</canvas>
		</div>
<style>
.canvas-container {
	position: relative;
	display: inline-block;
}
.tag-canvas {
	border: 1px dashed #ccc;
	border-radius: 5px;
	background: radial-gradient(circle, #f8f9fa 0%, #ffffff 100%);
}
.canvas-fallback {
	padding: 20px;
	text-align: center;
}
.fallback-message {
	color: #666;
	font-style: italic;
	margin-bottom: 15px;
}
.fallback-tags {
	list-style: none;
	padding: 0;
	text-align: left;
}
.fallback-tags li {
	margin: 5px 0;
}
.fallback-tags a {
	color: #0066cc;
	text-decoration: none;
	padding: 2px 5px;
	border-radius: 3px;
	transition: background-color 0.3s ease;
}
.fallback-tags a:hover {
	background-color: #e8f4fd;
	text-decoration: underline;
}
</style>
<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
	// Настройки 3D облака тегов
	TagCanvas.textFont = 'Impact,"Arial Black",sans-serif';
	TagCanvas.textColour = '#0066cc';
	TagCanvas.outlineThickness = 2;
	TagCanvas.outlineOffset = 1;
	TagCanvas.outlineMethod = 'block';
	TagCanvas.maxSpeed = 0.04;
	TagCanvas.minBrightness = 0.2;
	TagCanvas.depth = 0.85;
	TagCanvas.pulsateTo = 0.3;
	TagCanvas.pulsateTime = 0.5;
	TagCanvas.decel = 0.95;
	TagCanvas.reverse = true;
	TagCanvas.hideTags = false;
	TagCanvas.shadowBlur = 3;
	TagCanvas.fadeIn = 1000;
	TagCanvas.initial = [0.1, -0.1];
	try {
		TagCanvas.Start('myCanvas', 'weightTags', {
			textFont: null,
			textColour: null,
			weight: true,
			weightMode: 'size',
			weightFrom: 'data-weight'
		});
	} catch (e) {
		// Ошибка инициализации canvas, показываем fallback
		console.warn('3D TagCanvas не удалось инициализировать:', e);
		document.getElementById('myCanvasContainer').style.display = 'none';
		document.querySelector('.fallback-tags').style.display = 'block';
	}
});
</script>
