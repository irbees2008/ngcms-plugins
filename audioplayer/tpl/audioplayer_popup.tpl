{# Audio Player — Popup mode #}
{# Кнопка на странице, по клику открывается плавающее окно плеера #}
{% set player_id = 'ngap-' ~ random(100000, 999999) %}
{% set modal_id  = player_id ~ '-modal' %}

{# Кнопка-триггер #}
	<button class="ngap-popup-btn ngap-popup-btn--{{ skin }}" onclick="document.getElementById('{{ modal_id }}').classList.toggle('ngap-modal--open')" type="button"> &#9835;
	{{ title }}
</button>

{# Модальное окно #}
<div class="ngap-modal" id="{{ modal_id }}" role="dialog" aria-label="{{ title }}">
	<div class="ngap-modal__overlay" onclick="document.getElementById('{{ modal_id }}').classList.remove('ngap-modal--open')"></div>
	<div class="ngap-modal__box ngap--{{ skin }}">
		<button class="ngap-modal__close" onclick="document.getElementById('{{ modal_id }}').classList.remove('ngap-modal--open')" type="button" title="Закрыть">&times;</button>

		<audio id="{{ player_id }}-audio" preload="none" {% if autoplay %} autoplay {% endif %}></audio>

		<div class="ngap__header">
			<span class="ngap__icon">&#9835;</span>
			<span class="ngap__title">{{ title }}</span>
		</div>

		<div class="ngap__cover">
			<div class="ngap__disc" id="{{ player_id }}-disc">&#9835;</div>
			<div class="ngap__track-info">
				<div class="ngap__track-name" id="{{ player_id }}-name">{{ tracks[0].name }}</div>
				<div class="ngap__track-counter" id="{{ player_id }}-counter">1 /
					{{ tracks|length }}</div>
			</div>
		</div>

		<div class="ngap__progress-wrap">
			<span class="ngap__time" id="{{ player_id }}-cur">0:00</span>
			<div class="ngap__progress" id="{{ player_id }}-bar">
				<div class="ngap__progress-fill" id="{{ player_id }}-fill"></div>
			</div>
			<span class="ngap__time" id="{{ player_id }}-dur">0:00</span>
		</div>

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
</div>

<style>
	/* Кнопка-триггер */
	.ngap-popup-btn {
		display: inline-flex;
		align-items: center;
		gap: 8px;
		padding: 10px 20px;
		border: none;
		border-radius: 24px;
		font-size: 14px;
		font-weight: 600;
		cursor: pointer;
		transition: background 0.2s, transform 0.1s;
	}
	.ngap-popup-btn:active {
		transform: scale(0.96);
	}
	.ngap-popup-btn--dark {
		background: #e94560;
		color: #fff;
	}
	.ngap-popup-btn--dark:hover {
		background: #c73652;
	}
	.ngap-popup-btn--light {
		background: #1976d2;
		color: #fff;
	}
	.ngap-popup-btn--light:hover {
		background: #1565c0;
	}
	.ngap-popup-btn--blue {
		background: #00e5ff;
		color: #0f3460;
	}
	.ngap-popup-btn--blue:hover {
		background: #00b8d4;
	}

	/* Оверлей */
	.ngap-modal {
		display: none;
		position: fixed;
		inset: 0;
		z-index: 99998;
	}
	.ngap-modal--open {
		display: block;
	}
	.ngap-modal__overlay {
		position: absolute;
		inset: 0;
		background: rgba(0, 0, 0, .55);
		backdrop-filter: blur(3px);
	}
	.ngap-modal__box {
		position: absolute;
		top: 50%;
		left: 50%;
		transform: translate(-50%, -50%);
		width: 100%;
		max-width: 380px;
		border-radius: 14px;
		overflow: hidden;
		box-shadow: 0 8px 40px rgba(0, 0, 0, .5);
		animation: ngapPopIn 0.25s ease;
	}
	@keyframes ngapPopIn {
		from {
			opacity: 0;
			transform: translate(-50%, -46%);
		}
		to {
			opacity: 1;
			transform: translate(-50%, -50%);
		}
	}
	.ngap-modal__close {
		position: absolute;
		top: 10px;
		right: 12px;
		background: none;
		border: none;
		font-size: 22px;
		line-height: 1;
		cursor: pointer;
		opacity: .6;
		z-index: 1;
		padding: 2px 6px;
		border-radius: 4px;
	}
	.ngap-modal__close:hover {
		opacity: 1;
	}
	.ngap--dark .ngap-modal__close {
		color: #fff;
	}
	.ngap--light .ngap-modal__close {
		color: #222;
	}
	.ngap--blue .ngap-modal__close {
		color: #e0f7fa;
	}
</style>

{# Подключаем общие стили плеера из виджета — только если не подключены #}
<style>
	.ngap {
		font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
		border-radius: 12px;
		overflow: hidden;
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
	.ngap--blue {
		background: linear-gradient(135deg,#0f3460 0%,#16213e 100%);
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
