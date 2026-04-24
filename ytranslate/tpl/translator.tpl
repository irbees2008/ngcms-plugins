<div class="lang {% if translator.position == 'fixed' %}lang_fixed{% endif %}">
	<div id="ytWidget" style="display: none;"></div>
	<div class="lang__link lang__link_select" data-lang-active="">
		{{ translator.langs[translator.default_lang].name }}
	</div>
	<div class="lang__list" data-lang-list="">
		{% for code, lang in translator.langs %}
			<a class="lang__link lang__link_sub" data-ya-lang="{{ code }}">
				{{ lang.name }}
			</a>
		{% endfor %}
	</div>
</div>
<script>
	const yatranslate = {
lang: "{{ translator.default_lang }}",
// langFirstVisit: 'en' // Раскомментируйте, если нужно автоматически переводить при первом посещении
};
document.addEventListener('DOMContentLoaded', function () {
yaTranslateInit();
});
function yaTranslateInit() {
if (yatranslate.langFirstVisit && !localStorage.getItem('yt-widget')) {
yaTranslateSetLang(yatranslate.langFirstVisit);
}
let script = document.createElement('script');
script.src = `https://translate.yandex.net/website-widget/v1/widget.js?widgetId=ytWidget&pageLang=${
yatranslate.lang
}&widgetTheme={{ translator.theme }}&autoMode=false`;
document.getElementsByTagName('head')[0].appendChild(script);
let code = yaTranslateGetCode();
yaTranslateHtmlHandler(code);
yaTranslateEventHandler('click', '[data-ya-lang]', function (el) {
yaTranslateSetLang(el.getAttribute('data-ya-lang'));
window.location.reload();
});
}
function yaTranslateSetLang(lang) {
localStorage.setItem('yt-widget', JSON.stringify({"lang": lang, "active": true}));
}
function yaTranslateGetCode() {
return(localStorage["yt-widget"] != undefined && JSON.parse(localStorage["yt-widget"]).lang != undefined) ? JSON.parse(localStorage["yt-widget"]).lang : yatranslate.lang;
}
function yaTranslateHtmlHandler(code) {
const activeEl = document.querySelector('[data-lang-active]');
if (activeEl) {
activeEl.textContent = yatranslate.lang === code ? "{{ translator.langs[translator.default_lang].name }}" : "{{ translator.langs[translator.default_lang].name }}";
}
}
function yaTranslateEventHandler(event, selector, handler) {
document.addEventListener(event, function (e) {
let el = e.target.closest(selector);
if (el)
handler(el);
});
}
</script>
<style>
	.lang {
		position: relative;
		z-index: 10;
		text-align: center;
		background: rgba(157, 157, 157, 0.3);
		perspective: 700px;
	}
	.lang_fixed {
		position: fixed;
		right: 20px;
		top: 20px;
	}
	.lang__link {
		width: 100%;
		cursor: pointer;
		transition: 0.3s all;
		display: flex;
		justify-content: center;
		align-items: center;
		flex-direction: column;
		flex-shrink: 0;
		box-sizing: border-box;
		text-decoration: none;
		border-radius: 2px;
		padding: 8px 12px;
		color: #333;
		background: #f5f5f5;
		border: 1px solid #ddd;
	}
	.lang__link_sub {
		width: 100%;
		height: auto;
		position: relative;
		padding: 8px 12px;
		margin-bottom: 2px;
		color: #333;
		background: #f5f5f5;
		border: 1px solid #ddd;
	}
	.lang__link_sub:hover {
		background: #e0e0e0;
	}
	.lang__list {
		background: white;
		display: flex;
		justify-content: center;
		align-items: center;
		flex-direction: column;
		width: 100%;
		opacity: 0;
		visibility: hidden;
		transition: 0.3s all;
		transform: rotateX(-90deg);
		position: absolute;
		left: 0;
		top: 100%;
		z-index: 10;
		padding: 4px;
		transform-origin: center top;
		box-sizing: border-box;
		border: 1px solid #ddd;
		border-radius: 4px;
		box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
	}
	.lang:hover .lang__list {
		opacity: 1;
		visibility: visible;
		transform: rotateX(0);
	}
</style>
