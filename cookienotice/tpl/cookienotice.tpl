{# Cookie Notice Popup Widget #}
{# Всплывающее уведомление о куках — показывается один раз через localStorage #}
	<div id="ngcms-cookie-notice" class="ngcms-cookie-notice ngcms-cookie-notice--{{ position }}" style="display:none;" aria-live="polite" role="dialog" aria-label="{{ title }}"> <div class="ngcms-cookie-notice__inner">
		<div class="ngcms-cookie-notice__body">
			<strong class="ngcms-cookie-notice__title">{{ title }}</strong>
			<p class="ngcms-cookie-notice__text">{{ text|raw }}</p>
		</div>
		<div class="ngcms-cookie-notice__actions">
			<button id="ngcms-cookie-accept" class="ngcms-cookie-notice__btn" type="button">{{ btn_ok }}</button>
		</div>
	</div>
</div>
<style>
	.ngcms-cookie-notice {
		position: fixed;
		left: 0;
		right: 0;
		z-index: 99999;
		padding: 0 16px;
		box-sizing: border-box;
	}
	.ngcms-cookie-notice--bottom {
		bottom: 0;
	}
	.ngcms-cookie-notice--top {
		top: 0;
	}
	.ngcms-cookie-notice--center {
		top: 50%;
		left: 50%;
		right: auto;
		transform: translate(-50%, -50%);
		width: 100%;
		max-width: 520px;
	}
	.ngcms-cookie-notice__inner {
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		gap: 12px;
		background: #222;
		color: #f0f0f0;
		border-radius: 6px 6px 0 0;
		padding: 16px 20px;
		box-shadow: 0 -2px 12px rgba(0, 0, 0, .35);
		margin: 0 auto;
		max-width: 960px;
	}
	.ngcms-cookie-notice--top .ngcms-cookie-notice__inner {
		border-radius: 0 0 6px 6px;
		box-shadow: 0 2px 12px rgba(0, 0, 0, .35);
	}
	.ngcms-cookie-notice--center .ngcms-cookie-notice__inner {
		border-radius: 8px;
		box-shadow: 0 4px 24px rgba(0, 0, 0, .45);
	}
	.ngcms-cookie-notice__body {
		flex: 1 1 280px;
	}
	.ngcms-cookie-notice__title {
		display: block;
		font-size: 15px;
		margin-bottom: 6px;
	}
	.ngcms-cookie-notice__text {
		margin: 0;
		font-size: 13px;
		line-height: 1.5;
		color: #ccc;
	}
	.ngcms-cookie-notice__text a {
		color: #7ec8e3;
		text-decoration: underline;
	}
	.ngcms-cookie-notice__actions {
		flex: 0 0 auto;
	}
	.ngcms-cookie-notice__btn {
		background: #4caf50;
		color: #fff;
		border: none;
		border-radius: 4px;
		padding: 10px 24px;
		font-size: 14px;
		cursor: pointer;
		transition: background 0.2s;
		white-space: nowrap;
	}
	.ngcms-cookie-notice__btn:hover {
		background: #388e3c;
	}
	/* Анимация появления */
	@keyframes ngcmsCookieSlideUp {
		from {
			opacity: 0;
			transform: translateY(30px);
		}
		to {
			opacity: 1;
			transform: translateY(0);
		}
	}
	@keyframes ngcmsCookieSlideDown {
		from {
			opacity: 0;
			transform: translateY(-30px);
		}
		to {
			opacity: 1;
			transform: translateY(0);
		}
	}
	@keyframes ngcmsCookieFadeIn {
		from {
			opacity: 0;
		}
		to {
			opacity: 1;
		}
	}
	.ngcms-cookie-notice--bottom.ngcms-cookie-visible {
		animation: ngcmsCookieSlideUp 0.4s ease forwards;
	}
	.ngcms-cookie-notice--top.ngcms-cookie-visible {
		animation: ngcmsCookieSlideDown 0.4s ease forwards;
	}
	.ngcms-cookie-notice--center.ngcms-cookie-visible {
		animation: ngcmsCookieFadeIn 0.3s ease forwards;
	}
</style>
 <script>
(function () {
	var STORAGE_KEY = 'ngcms_cookie_accepted';
	var DAYS = {{ cookie_days }};
	var notice = document.getElementById('ngcms-cookie-notice');
	var btn    = document.getElementById('ngcms-cookie-accept');
	if (!notice) return;
	function isAccepted() {
		try {
			var val = localStorage.getItem(STORAGE_KEY);
			if (!val) return false;
			var data = JSON.parse(val);
			return data.expires && Date.now() < data.expires;
		} catch (e) {
			return false;
		}
	}
	function accept() {
		try {
			var expires = Date.now() + DAYS * 24 * 60 * 60 * 1000;
			localStorage.setItem(STORAGE_KEY, JSON.stringify({ accepted: true, expires: expires }));
		} catch (e) {}
		notice.style.opacity = '0';
		notice.style.transition = 'opacity .3s';
		setTimeout(function () { notice.style.display = 'none'; }, 320);
	}
	if (!isAccepted()) {
		notice.style.display = 'block';
		notice.classList.add('ngcms-cookie-visible');
	}
	if (btn) {
		btn.addEventListener('click', accept);
	}
})();
</script>
