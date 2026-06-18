{# Audio Player — Page mode #}
{# Полностраничный плеер с большой обложкой и полным плейлистом #}
{% set player_id = 'ngap-' ~ random(100000, 999999) %}

	<div id="{{ player_id }}" class="ngap-page ngap-page--{{ skin }}"> <audio id="{{ player_id }}-audio" preload="none" {% if autoplay %} autoplay {% endif %}></audio>

	{# Левая колонка: плеер #}
	<div class="ngap-page__left">
		<div class="ngap-page__disc-wrap">
			<div class="ngap-page__disc" id="{{ player_id }}-disc">&#9835;</div>
		</div>
		<h2 class="ngap-page__title">{{ title }}</h2>
		<div class="ngap-page__track-name" id="{{ player_id }}-name">{{ tracks[0].name }}</div>
		<div class="ngap-page__counter" id="{{ player_id }}-counter">1 /
			{{ tracks|length }}</div>

		<div class="ngap-page__progress-wrap">
			<span class="ngap__time" id="{{ player_id }}-cur">0:00</span>
			<div class="ngap__progress" id="{{ player_id }}-bar">
				<div class="ngap__progress-fill" id="{{ player_id }}-fill"></div>
			</div>
			<span class="ngap__time" id="{{ player_id }}-dur">0:00</span>
		</div>

		<div class="ngap-page__controls">
			<button class="ngap__btn" id="{{ player_id }}-prev" title="Предыдущий">&#9664;&#9664;</button>
			<button class="ngap__btn ngap__btn--play" id="{{ player_id }}-play" title="Воспроизвести">&#9654;</button>
			<button class="ngap__btn" id="{{ player_id }}-next" title="Следующий">&#9654;&#9654;</button>
			<button class="ngap__btn ngap__btn--shuffle" id="{{ player_id }}-shuffle" title="Перемешать">&#8646;</button>
		</div>

		<div class="ngap-page__volume">
			<span>&#128266;</span>
			<input class="ngap__volume" id="{{ player_id }}-vol" type="range" min="0" max="1" step="0.05" value="0.8">
		</div>
	</div>

	{# Правая колонка: плейлист #}
	{% if show_list %}
		<div class="ngap-page__right">
			<div class="ngap-page__list-title">Плейлист
				<span class="ngap-page__list-count">{{ tracks|length }}
					треков</span>
			</div>
			<div class="ngap__playlist ngap-page__list" id="{{ player_id }}-list">
				{% for i, track in tracks %}
					<div class="ngap__item{% if i == 0 %} ngap__item--active{% endif %}" data-index="{{ i }}">
						<span class="ngap__item-num">{{ i + 1 }}</span>
						<span class="ngap__item-name">{{ track.name }}</span>
						<span class="ngap__item-ext">{{ track.ext }}</span>
					</div>
				{% endfor %}
			</div>
		</div>
	{% endif %}

</div>

<style>
	.ngap-page {
		display: flex;
		gap: 0;
		border-radius: 16px;
		overflow: hidden;
		box-shadow: 0 6px 32px rgba(0, 0, 0, .3);
		font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
		user-select: none;
		min-height: 420px;
	}
	.ngap-page__left {
		flex: 0 0 320px;
		display: flex;
		flex-direction: column;
		align-items: center;
		padding: 36px 28px 28px;
		gap: 10px;
	}
	.ngap-page__disc-wrap {
		width: 160px;
		height: 160px;
		border-radius: 50%;
		display: flex;
		align-items: center;
		justify-content: center;
		margin-bottom: 8px;
	}
	.ngap-page__disc {
		width: 130px;
		height: 130px;
		border-radius: 50%;
		display: flex;
		align-items: center;
		justify-content: center;
		font-size: 48px;
	}
	.ngap-page__disc.ngap--spinning {
		animation: ngapSpin 4s linear infinite;
	}
	@keyframes ngapSpin {
		to {
			transform: rotate(360deg);
		}
	}
	.ngap-page__title {
		font-size: 16px;
		font-weight: 700;
		letter-spacing: 0.5px;
		text-transform: uppercase;
		margin: 0;
		text-align: center;
	}
	.ngap-page__track-name {
		font-size: 15px;
		font-weight: 600;
		text-align: center;
		max-width: 260px;
		word-break: break-word;
	}
	.ngap-page__counter {
		font-size: 12px;
		opacity: .5;
	}
	.ngap-page__progress-wrap {
		display: flex;
		align-items: center;
		gap: 8px;
		width: 100%;
	}
	.ngap__time {
		font-size: 11px;
		opacity: .7;
		min-width: 30px;
	}
	.ngap__progress {
		flex: 1;
		height: 5px;
		border-radius: 3px;
		cursor: pointer;
		position: relative;
		overflow: hidden;
	}
	.ngap__progress-fill {
		height: 100%;
		width: 0;
		border-radius: 3px;
		transition: width 0.2s linear;
	}
	.ngap-page__controls {
		display: flex;
		align-items: center;
		gap: 4px;
	}
	.ngap__btn {
		background: none;
		border: none;
		cursor: pointer;
		font-size: 20px;
		padding: 8px 10px;
		border-radius: 8px;
		line-height: 1;
		transition: background 0.15s, transform 0.1s;
	}
	.ngap__btn:active {
		transform: scale(0.92);
	}
	.ngap__btn--play {
		font-size: 28px;
		padding: 10px 16px;
		border-radius: 50%;
	}
	.ngap-page__volume {
		display: flex;
		align-items: center;
		gap: 8px;
		font-size: 14px;
	}
	.ngap__volume {
		width: 90px;
		-webkit-appearance: none;
		height: 3px;
		border-radius: 2px;
		cursor: pointer;
	}
	/* Правая колонка */
	.ngap-page__right {
		flex: 1;
		display: flex;
		flex-direction: column;
		border-left: 1px solid;
		overflow: hidden;
	}
	.ngap-page__list-title {
		padding: 18px 18px 10px;
		font-size: 13px;
		font-weight: 700;
		letter-spacing: 0.4px;
		text-transform: uppercase;
	}
	.ngap-page__list-count {
		font-weight: 400;
		opacity: .5;
		margin-left: 6px;
	}
	.ngap-page__list {
		flex: 1;
		overflow-y: auto;
		max-height: none !important;
		border-top: none;
	}
	.ngap__item {
		display: flex;
		align-items: center;
		gap: 8px;
		padding: 9px 18px;
		font-size: 13px;
		cursor: pointer;
		transition: background 0.15s;
	}
	.ngap__item-num {
		font-size: 11px;
		opacity: .5;
		min-width: 22px;
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

	/* Тёмная тема */
	.ngap-page--dark {
		background: #1a1a2e;
		color: #e0e0e0;
	}
	.ngap-page--dark .ngap-page__disc-wrap {
		background: rgba(233, 69, 96, .1);
	}
	.ngap-page--dark .ngap-page__disc {
		background: #16213e;
		color: #e94560;
	}
	.ngap-page--dark .ngap__progress {
		background: #2a2a4a;
	}
	.ngap-page--dark .ngap__progress-fill {
		background: #e94560;
	}
	.ngap-page--dark .ngap__btn {
		color: #ccc;
	}
	.ngap-page--dark .ngap__btn:hover {
		background: rgba(255, 255, 255, .08);
	}
	.ngap-page--dark .ngap__btn--play {
		background: #e94560;
		color: #fff;
	}
	.ngap-page--dark .ngap__btn--play:hover {
		background: #c73652;
	}
	.ngap-page--dark .ngap__btn--shuffle.ngap--active {
		background: rgba(233, 69, 96, .25);
		color: #e94560;
	}
	.ngap-page--dark .ngap-page__right {
		border-color: #2a2a4a;
	}
	.ngap-page--dark .ngap__item:hover {
		background: rgba(255, 255, 255, .05);
	}
	.ngap-page--dark .ngap__item--active {
		background: rgba(233, 69, 96, .15);
		color: #e94560;
	}
	.ngap-page--dark .ngap__volume {
		background: #2a2a4a;
	}
	.ngap-page--dark .ngap-page__list::-webkit-scrollbar {
		width: 4px;
	}
	.ngap-page--dark .ngap-page__list::-webkit-scrollbar-track {
		background: #1a1a2e;
	}
	.ngap-page--dark .ngap-page__list::-webkit-scrollbar-thumb {
		background: #e94560;
		border-radius: 2px;
	}

	/* Светлая тема */
	.ngap-page--light {
		background: #f5f5f5;
		color: #222;
	}
	.ngap-page--light .ngap-page__disc-wrap {
		background: #e8f0fe;
	}
	.ngap-page--light .ngap-page__disc {
		background: #e0e0e0;
		color: #1976d2;
	}
	.ngap-page--light .ngap__progress {
		background: #ddd;
	}
	.ngap-page--light .ngap__progress-fill {
		background: #1976d2;
	}
	.ngap-page--light .ngap__btn {
		color: #555;
	}
	.ngap-page--light .ngap__btn:hover {
		background: rgba(0, 0, 0, .07);
	}
	.ngap-page--light .ngap__btn--play {
		background: #1976d2;
		color: #fff;
	}
	.ngap-page--light .ngap__btn--play:hover {
		background: #1565c0;
	}
	.ngap-page--light .ngap-page__right {
		border-color: #ddd;
	}
	.ngap-page--light .ngap__item:hover {
		background: rgba(0, 0, 0, .04);
	}
	.ngap-page--light .ngap__item--active {
		background: rgba(25, 118, 210, .12);
		color: #1976d2;
	}

	/* Синяя тема */
	.ngap-page--blue {
		background: linear-gradient(135deg,#0f3460 0%,#16213e 100%);
		color: #e0f7fa;
	}
	.ngap-page--blue .ngap-page__disc-wrap {
		background: rgba(0, 229, 255, .08);
	}
	.ngap-page--blue .ngap-page__disc {
		background: rgba(255, 255, 255, .1);
		color: #00e5ff;
	}
	.ngap-page--blue .ngap__progress {
		background: rgba(255, 255, 255, .15);
	}
	.ngap-page--blue .ngap__progress-fill {
		background: #00e5ff;
	}
	.ngap-page--blue .ngap__btn {
		color: #ccc;
	}
	.ngap-page--blue .ngap__btn:hover {
		background: rgba(255, 255, 255, .1);
	}
	.ngap-page--blue .ngap__btn--play {
		background: #00e5ff;
		color: #0f3460;
	}
	.ngap-page--blue .ngap__btn--play:hover {
		background: #00b8d4;
	}
	.ngap-page--blue .ngap-page__right {
		border-color: rgba(255, 255, 255, .1);
	}
	.ngap-page--blue .ngap__item:hover {
		background: rgba(255, 255, 255, .07);
	}
	.ngap-page--blue .ngap__item--active {
		background: rgba(0, 229, 255, .15);
		color: #00e5ff;
	}

	@media(max-width: 600px) {
		.ngap-page {
			flex-direction: column;
		}
		.ngap-page__left {
			flex: none;
			width: 100%;
		}
		.ngap-page__right {
			border-left: none;
			border-top: 1px solid;
		}
		.ngap-page--dark .ngap-page__right {
			border-color: #2a2a4a;
		}
		.ngap-page--light .ngap-page__right {
			border-color: #ddd;
		}
		.ngap-page--blue .ngap-page__right {
			border-color: rgba(255, 255, 255, .1);
		}
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
	var current = 0, playing = false, shuffle = false;
	function fmt(s) { s=Math.floor(s||0); return Math.floor(s/60)+':'+ ('0'+(s%60)).slice(-2); }
	function loadTrack(idx, andPlay) {
		current = idx;
		var t = tracks[idx];
		audio.src = t.file; audio.load();
		nameEl.textContent = t.name;
		counter.textContent = (idx+1)+' / '+tracks.length;
		fill.style.width = '0%'; curEl.textContent = '0:00'; durEl.textContent = '0:00';
		if (listEl) {
			listEl.querySelectorAll('.ngap__item').forEach(function(el,i){ el.classList.toggle('ngap__item--active', i===idx); });
			if (listEl.querySelectorAll('.ngap__item')[idx]) listEl.querySelectorAll('.ngap__item')[idx].scrollIntoView({block:'nearest'});
		}
		if (andPlay) audio.play();
	}
	function setPlaying(s) { playing=s; btnPlay.innerHTML=s?'&#9646;&#9646;':'&#9654;'; disc.classList.toggle('ngap--spinning',s); }
	function nextIdx() { return shuffle ? Math.floor(Math.random()*tracks.length) : (current+1)%tracks.length; }
	function prevIdx() { return shuffle ? Math.floor(Math.random()*tracks.length) : (current-1+tracks.length)%tracks.length; }
	btnPlay.addEventListener('click', function(){ if (!audio.src||audio.src===window.location.href){ loadTrack(current,true); return; } playing?audio.pause():audio.play(); });
	btnPrev.addEventListener('click', function(){ loadTrack(prevIdx(), playing); });
	btnNext.addEventListener('click', function(){ loadTrack(nextIdx(), playing); });
	btnShuffle.addEventListener('click', function(){ shuffle=!shuffle; btnShuffle.classList.toggle('ngap--active',shuffle); });
	audio.addEventListener('play', function(){ setPlaying(true); });
	audio.addEventListener('pause', function(){ setPlaying(false); });
	audio.addEventListener('ended', function(){ loadTrack(nextIdx(), true); });
	audio.addEventListener('timeupdate', function(){ if(!audio.duration)return; fill.style.width=(audio.currentTime/audio.duration*100)+'%'; curEl.textContent=fmt(audio.currentTime); });
	audio.addEventListener('durationchange', function(){ durEl.textContent=fmt(audio.duration); });
	bar.addEventListener('click', function(e){ if(!audio.duration)return; var r=bar.getBoundingClientRect(); audio.currentTime=((e.clientX-r.left)/r.width)*audio.duration; });
	if (volEl) { audio.volume=parseFloat(volEl.value); volEl.addEventListener('input',function(){ audio.volume=parseFloat(volEl.value); }); }
	if (listEl) { listEl.addEventListener('click',function(e){ var item=e.target.closest('.ngap__item'); if(!item)return; loadTrack(parseInt(item.getAttribute('data-index'),10),true); }); }
	{% if autoplay %}loadTrack(0, true);{% endif %}
})();
</script>
