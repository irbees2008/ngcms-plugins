{# Audio Player Widget #}
{% set player_id = 'ngap-' ~ random(100000, 999999) %}

<div
	id="{{ player_id }}" class="ngap ngap--{{ skin }}">

	{# Скрытый HTML5 audio #}
	<audio id="{{ player_id }}-audio" preload="none" {% if autoplay %} autoplay {% endif %}></audio>

	{# Шапка #}
	<div class="ngap__header">
		<span class="ngap__icon">&#9835;</span>
		<span class="ngap__title">{{ title }}</span>
	</div>

	{# Обложка / визуализация #}
	<div class="ngap__cover">
		<div class="ngap__disc" id="{{ player_id }}-disc">&#9835;</div>
		<div class="ngap__track-info">
			<div class="ngap__track-name" id="{{ player_id }}-name">{{ tracks[0].name }}</div>
			<div class="ngap__track-counter" id="{{ player_id }}-counter">1 /
				{{ tracks|length }}</div>
		</div>
	</div>

	{# Прогресс-бар #}
	<div class="ngap__progress-wrap">
		<span class="ngap__time" id="{{ player_id }}-cur">0:00</span>
		<div class="ngap__progress" id="{{ player_id }}-bar">
			<div class="ngap__progress-fill" id="{{ player_id }}-fill"></div>
		</div>
		<span class="ngap__time" id="{{ player_id }}-dur">0:00</span>
	</div>

	{# Управление #}
	<div class="ngap__controls">
		<button class="ngap__btn" id="{{ player_id }}-prev" title="Предыдущий">&#9664;&#9664;</button>
		<button class="ngap__btn ngap__btn--play" id="{{ player_id }}-play" title="Воспроизвести">&#9654;</button>
		<button class="ngap__btn" id="{{ player_id }}-next" title="Следующий">&#9654;&#9654;</button>
		<button class="ngap__btn ngap__btn--shuffle" id="{{ player_id }}-shuffle" title="Перемешать">&#8646;</button>
		<div class="ngap__volume-wrap">
			<span class="ngap__vol-icon">&#128266;</span>
			<input class="ngap__volume" id="{{ player_id }}-vol" type="range" min="0" max="1" step="0.05" value="0.8">
		</div>
	</div>

	{# Плейлист #}
	{% if show_list %}
		<div class="ngap__playlist" id="{{ player_id }}-list">
			{% for i, track in tracks %}
				<div class="ngap__item{% if i == 0 %} ngap__item--active{% endif %}" data-index="{{ i }}">
					<span class="ngap__item-num">{{ i + 1 }}</span>
					<span class="ngap__item-name">{{ track.name }}</span>
					<span class="ngap__item-ext">{{ track.ext }}</span>
				</div>
			{% endfor %}
		</div>
	{% endif %}

</div>

<style>
	/* ===== Базовые стили ===== */
	.ngap {
		font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
		border-radius: 12px;
		overflow: hidden;
		max-width: 360px;
		box-shadow: 0 4px 24px rgba(0, 0, 0, .3);
		user-select: none;
	}
	.ngap__header {
		display: flex;
		align-items: center;
		gap: 8px;
		padding: 12px 16px 8px;
		font-size: 13px;
		font-weight: 600;
		letter-spacing: 0.5px;
		text-transform: uppercase;
	}
	.ngap__cover {
		display: flex;
		align-items: center;
		gap: 14px;
		padding: 8px 16px 10px;
	}
	.ngap__disc {
		width: 54px;
		height: 54px;
		border-radius: 50%;
		display: flex;
		align-items: center;
		justify-content: center;
		font-size: 22px;
		flex-shrink: 0;
		transition: transform 0.1s;
	}
	.ngap__disc.ngap--spinning {
		animation: ngapSpin 4s linear infinite;
	}
	@keyframes ngapSpin {
		to {
			transform: rotate(360deg);
		}
	}
	.ngap__track-info {
		flex: 1;
		overflow: hidden;
	}
	.ngap__track-name {
		font-size: 14px;
		font-weight: 600;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
	}
	.ngap__track-counter {
		font-size: 11px;
		margin-top: 3px;
		opacity: .6;
	}
	/* Прогресс */
	.ngap__progress-wrap {
		display: flex;
		align-items: center;
		gap: 8px;
		padding: 0 16px 10px;
	}
	.ngap__time {
		font-size: 11px;
		opacity: .7;
		min-width: 30px;
	}
	.ngap__progress {
		flex: 1;
		height: 4px;
		border-radius: 2px;
		cursor: pointer;
		position: relative;
		overflow: hidden;
	}
	.ngap__progress-fill {
		height: 100%;
		width: 0;
		border-radius: 2px;
		transition: width 0.2s linear;
	}
	/* Кнопки */
	.ngap__controls {
		display: flex;
		align-items: center;
		gap: 6px;
		padding: 6px 14px 12px;
	}
	.ngap__btn {
		background: none;
		border: none;
		cursor: pointer;
		font-size: 18px;
		padding: 6px 8px;
		border-radius: 6px;
		line-height: 1;
		transition: background 0.15s, transform 0.1s;
	}
	.ngap__btn:active {
		transform: scale(0.92);
	}
	.ngap__btn--play {
		font-size: 22px;
		padding: 6px 12px;
		border-radius: 50%;
	}
	.ngap__btn--shuffle.ngap--active {
		border-radius: 6px;
	}
	.ngap__volume-wrap {
		display: flex;
		align-items: center;
		gap: 4px;
		margin-left: auto;
	}
	.ngap__vol-icon {
		font-size: 14px;
	}
	.ngap__volume {
		width: 70px;
		-webkit-appearance: none;
		height: 3px;
		border-radius: 2px;
		cursor: pointer;
	}
	/* Плейлист */
	.ngap__playlist {
		max-height: 200px;
		overflow-y: auto;
		border-top: 1px solid;
	}
	.ngap__item {
		display: flex;
		align-items: center;
		gap: 8px;
		padding: 7px 14px;
		font-size: 13px;
		cursor: pointer;
		transition: background 0.15s;
	}
	.ngap__item-num {
		font-size: 11px;
		opacity: .5;
		min-width: 18px;
	}
	.ngap__item-name {
		flex: 1;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
	}
	.ngap__item-ext {
		font-size: 10px;
		opacity: .4;
		text-transform: uppercase;
	}

	/* ===== Тёмная тема (dark) ===== */
	.ngap--dark {
		background: #1a1a2e;
		color: #e0e0e0;
	}
	.ngap--dark .ngap__disc {
		background: #16213e;
		color: #e94560;
	}
	.ngap--dark .ngap__progress {
		background: #2a2a4a;
	}
	.ngap--dark .ngap__progress-fill {
		background: #e94560;
	}
	.ngap--dark .ngap__btn {
		color: #ccc;
	}
	.ngap--dark .ngap__btn:hover {
		background: rgba(255, 255, 255, .08);
	}
	.ngap--dark .ngap__btn--play {
		background: #e94560;
		color: #fff;
	}
	.ngap--dark .ngap__btn--play:hover {
		background: #c73652;
	}
	.ngap--dark .ngap__btn--shuffle.ngap--active {
		background: rgba(233, 69, 96, .25);
		color: #e94560;
	}
	.ngap--dark .ngap__playlist {
		border-color: #2a2a4a;
	}
	.ngap--dark .ngap__item:hover {
		background: rgba(255, 255, 255, .05);
	}
	.ngap--dark .ngap__item--active {
		background: rgba(233, 69, 96, .15);
		color: #e94560;
	}
	.ngap--dark .ngap__volume {
		background: #2a2a4a;
	}
	.ngap--dark .ngap__playlist::-webkit-scrollbar {
		width: 4px;
	}
	.ngap--dark .ngap__playlist::-webkit-scrollbar-track {
		background: #1a1a2e;
	}
	.ngap--dark .ngap__playlist::-webkit-scrollbar-thumb {
		background: #e94560;
		border-radius: 2px;
	}

	/* ===== Светлая тема (light) ===== */
	.ngap--light {
		background: #f5f5f5;
		color: #222;
	}
	.ngap--light .ngap__disc {
		background: #e0e0e0;
		color: #1976d2;
	}
	.ngap--light .ngap__progress {
		background: #ddd;
	}
	.ngap--light .ngap__progress-fill {
		background: #1976d2;
	}
	.ngap--light .ngap__btn {
		color: #555;
	}
	.ngap--light .ngap__btn:hover {
		background: rgba(0, 0, 0, .07);
	}
	.ngap--light .ngap__btn--play {
		background: #1976d2;
		color: #fff;
	}
	.ngap--light .ngap__btn--play:hover {
		background: #1565c0;
	}
	.ngap--light .ngap__btn--shuffle.ngap--active {
		background: rgba(25, 118, 210, .15);
		color: #1976d2;
	}
	.ngap--light .ngap__playlist {
		border-color: #ddd;
	}
	.ngap--light .ngap__item:hover {
		background: rgba(0, 0, 0, .04);
	}
	.ngap--light .ngap__item--active {
		background: rgba(25, 118, 210, .12);
		color: #1976d2;
	}

	/* ===== Синяя тема (blue) ===== */
	.ngap--blue {
		background: linear-gradient(135deg, #0f3460 0%, #16213e 100%);
		color: #e0f7fa;
	}
	.ngap--blue .ngap__disc {
		background: rgba(255, 255, 255, .1);
		color: #00e5ff;
	}
	.ngap--blue .ngap__progress {
		background: rgba(255, 255, 255, .15);
	}
	.ngap--blue .ngap__progress-fill {
		background: #00e5ff;
	}
	.ngap--blue .ngap__btn {
		color: #ccc;
	}
	.ngap--blue .ngap__btn:hover {
		background: rgba(255, 255, 255, .1);
	}
	.ngap--blue .ngap__btn--play {
		background: #00e5ff;
		color: #0f3460;
	}
	.ngap--blue .ngap__btn--play:hover {
		background: #00b8d4;
	}
	.ngap--blue .ngap__btn--shuffle.ngap--active {
		background: rgba(0, 229, 255, .2);
		color: #00e5ff;
	}
	.ngap--blue .ngap__playlist {
		border-color: rgba(255, 255, 255, .1);
	}
	.ngap--blue .ngap__item:hover {
		background: rgba(255, 255, 255, .07);
	}
	.ngap--blue .ngap__item--active {
		background: rgba(0, 229, 255, .15);
		color: #00e5ff;
	}
</style>

 <script>
(function () {
	var pid    = {{ player_id|json_encode }};
	var tracks = {{ tracks|json_encode }};

	var audio   = document.getElementById(pid + '-audio');
	var btnPlay = document.getElementById(pid + '-play');
	var btnPrev = document.getElementById(pid + '-prev');
	var btnNext = document.getElementById(pid + '-next');
	var btnShuffle = document.getElementById(pid + '-shuffle');
	var disc    = document.getElementById(pid + '-disc');
	var nameEl  = document.getElementById(pid + '-name');
	var counter = document.getElementById(pid + '-counter');
	var bar     = document.getElementById(pid + '-bar');
	var fill    = document.getElementById(pid + '-fill');
	var curEl   = document.getElementById(pid + '-cur');
	var durEl   = document.getElementById(pid + '-dur');
	var volEl   = document.getElementById(pid + '-vol');
	var listEl  = document.getElementById(pid + '-list');

	var current  = 0;
	var playing  = false;
	var shuffle  = false;
	var order    = tracks.map(function(_, i){ return i; });

	function fmt(s) {
		s = Math.floor(s || 0);
		return Math.floor(s / 60) + ':' + ('0' + (s % 60)).slice(-2);
	}

	function loadTrack(idx, andPlay) {
		current = idx;
		var t = tracks[idx];
		audio.src = t.file;
		audio.load();
		nameEl.textContent  = t.name;
		counter.textContent = (idx + 1) + ' / ' + tracks.length;
		fill.style.width    = '0%';
		curEl.textContent   = '0:00';
		durEl.textContent   = '0:00';
		if (listEl) {
			var items = listEl.querySelectorAll('.ngap__item');
			items.forEach(function(el, i){
				el.classList.toggle('ngap__item--active', i === idx);
			});
			// Прокрутка к активному треку
			if (items[idx]) {
				items[idx].scrollIntoView({ block: 'nearest' });
			}
		}
		if (andPlay) { audio.play(); }
	}

	function setPlaying(state) {
		playing = state;
		btnPlay.innerHTML = state ? '&#9646;&#9646;' : '&#9654;';
		disc.classList.toggle('ngap--spinning', state);
	}

	function nextIndex() {
		if (shuffle) {
			return Math.floor(Math.random() * tracks.length);
		}
		return (current + 1) % tracks.length;
	}
	function prevIndex() {
		if (shuffle) {
			return Math.floor(Math.random() * tracks.length);
		}
		return (current - 1 + tracks.length) % tracks.length;
	}

	btnPlay.addEventListener('click', function () {
		if (!audio.src || audio.src === window.location.href) {
			loadTrack(current, true);
			return;
		}
		if (playing) { audio.pause(); } else { audio.play(); }
	});

	btnPrev.addEventListener('click', function () { loadTrack(prevIndex(), playing); });
	btnNext.addEventListener('click', function () { loadTrack(nextIndex(), playing); });

	btnShuffle.addEventListener('click', function () {
		shuffle = !shuffle;
		btnShuffle.classList.toggle('ngap--active', shuffle);
	});

	audio.addEventListener('play',  function () { setPlaying(true); });
	audio.addEventListener('pause', function () { setPlaying(false); });
	audio.addEventListener('ended', function () { loadTrack(nextIndex(), true); });

	audio.addEventListener('timeupdate', function () {
		if (!audio.duration) return;
		var pct = (audio.currentTime / audio.duration) * 100;
		fill.style.width  = pct + '%';
		curEl.textContent = fmt(audio.currentTime);
	});
	audio.addEventListener('durationchange', function () {
		durEl.textContent = fmt(audio.duration);
	});

	bar.addEventListener('click', function (e) {
		if (!audio.duration) return;
		var rect = bar.getBoundingClientRect();
		audio.currentTime = ((e.clientX - rect.left) / rect.width) * audio.duration;
	});

	if (volEl) {
		audio.volume = parseFloat(volEl.value);
		volEl.addEventListener('input', function () {
			audio.volume = parseFloat(volEl.value);
		});
	}

	if (listEl) {
		listEl.addEventListener('click', function (e) {
			var item = e.target.closest('.ngap__item');
			if (!item) return;
			var idx = parseInt(item.getAttribute('data-index'), 10);
			loadTrack(idx, true);
		});
	}

	// Если autoplay — грузим первый трек
	{% if autoplay %}
	loadTrack(0, true);
	{% endif %}
})();
</script>
