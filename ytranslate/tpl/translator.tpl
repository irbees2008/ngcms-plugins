<div class="lang {% if translator.position == 'fixed' %}lang_fixed{% endif %}" id="ytranslate-widget">
	<div class="lang__link lang__link_select" data-lang-active="">{{ translator.langs[translator.default_lang].name }}</div>
	<div class="lang__list" data-lang-list="">
		{% for code, lang in translator.langs %}
			<a class="lang__link lang__link_sub" data-ytranslate-lang="{{ code }}">{{ lang.name }}</a>
		{% endfor %}
	</div>
</div>
<script>
(function () {
	var ytranslate = {
		source: '{{ translator.default_lang }}',
		endpoint: window.location.pathname + window.location.search,
		autoIp: {{ translator.auto_ip ? 'true' : 'false' }},
		langs: {{ translator.langs|json_encode|raw }}
	};
	var originalNodes = [];
	var translating = false;

	function pageTextNodes() {
		var nodes = [];
		var walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT, {
			acceptNode: function (node) {
				var parent = node.parentElement;
				if (!parent || !node.nodeValue.trim() || parent.closest('#ytranslate-widget,script,style,noscript,textarea,input,select,option,code,pre')) return NodeFilter.FILTER_REJECT;
				return NodeFilter.FILTER_ACCEPT;
			}
		});
		while (walker.nextNode()) nodes.push(walker.currentNode);
		return nodes;
	}

	function restoreOriginal() {
		originalNodes.forEach(function (item) { item.node.nodeValue = item.text; });
		originalNodes = [];
	}

	function setActive(code, label) {
		var active = document.querySelector('[data-lang-active]');
		if (active) active.textContent = label || (ytranslate.langs[code] ? ytranslate.langs[code].name : code);
	}

	function translateTo(target) {
		if (target === ytranslate.source) {
			restoreOriginal();
			setActive(target);
			return;
		}
		if (translating) return;
		restoreOriginal();
		var items = pageTextNodes().map(function (node) { return {node: node, text: node.nodeValue}; });
		originalNodes = items;
		setActive(target, {{ translator.messages.translating|json_encode|raw }});
		translating = true;
		var batches = [];
		for (var i = 0; i < items.length; i += 40) batches.push(items.slice(i, i + 40));
		var chain = Promise.resolve();
		batches.forEach(function (batch) {
			chain = chain.then(function () {
				return fetch(ytranslate.endpoint, {
					method: 'POST', credentials: 'same-origin',
					headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
					body: 'handler=ytranslate_translate&source=' + encodeURIComponent(ytranslate.source) + '&target=' + encodeURIComponent(target) + '&texts=' + encodeURIComponent(JSON.stringify(batch.map(function (item) { return item.text.trim(); })))
				}).then(function (response) {
					if (!response.ok) throw new Error('HTTP ' + response.status);
					return response.json();
				}).then(function (data) {
					if (!data.success || !Array.isArray(data.texts)) throw new Error(data.error || {{ translator.messages.error_translation_failed|json_encode|raw }});
					data.texts.forEach(function (text, index) {
						var item = batch[index];
						item.node.nodeValue = item.text.match(/^\s*/)[0] + text + item.text.match(/\s*$/)[0];
					});
				});
			});
		});
		chain.catch(function (error) { restoreOriginal(); setActive(ytranslate.source, {{ translator.messages.error_prefix|json_encode|raw }} + (error.message || {{ translator.messages.error_unavailable|json_encode|raw }})); }).then(function () { translating = false; });
	}

	function detectByIp() {
		if (!ytranslate.autoIp || localStorage.getItem('ytranslate-lang')) return;
		var separator = ytranslate.endpoint.indexOf('?') >= 0 ? '&' : '?';
		fetch(ytranslate.endpoint + separator + 'handler=ytranslate_country', {credentials: 'same-origin'})
			.then(function (response) { return response.json(); })
			.then(function (data) { if (data.success && ytranslate.langs[data.lang]) { localStorage.setItem('ytranslate-lang', data.lang); translateTo(data.lang); } })
			.catch(function () {});
	}

	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('[data-ytranslate-lang]').forEach(function (link) {
			link.addEventListener('click', function () {
				var code = link.getAttribute('data-ytranslate-lang');
				localStorage.setItem('ytranslate-lang', code);
				translateTo(code);
			});
		});
		var saved = localStorage.getItem('ytranslate-lang');
		if (saved && ytranslate.langs[saved]) translateTo(saved); else detectByIp();
	});
})();
</script>
<style>
	.lang { position: relative; z-index: 10; text-align: center; background: rgba(157, 157, 157, 0.3); perspective: 700px; }
	.lang_fixed { position: fixed; right: 20px; top: 20px; }
	.lang__link { width: 100%; cursor: pointer; transition: 0.3s all; display: flex; justify-content: center; align-items: center; flex-direction: column; box-sizing: border-box; text-decoration: none; border-radius: 2px; padding: 8px 12px; color: #333; background: #f5f5f5; border: 1px solid #ddd; }
	.lang__link_sub { width: 100%; position: relative; margin-bottom: 2px; }
	.lang__link_sub:hover { background: #e0e0e0; }
	.lang__list { background: white; display: flex; justify-content: center; align-items: center; flex-direction: column; width: 100%; opacity: 0; visibility: hidden; transition: 0.3s all; transform: rotateX(-90deg); position: absolute; left: 0; top: 100%; z-index: 10; padding: 4px; transform-origin: center top; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); }
	.lang:hover .lang__list { opacity: 1; visibility: visible; transform: rotateX(0); }
</style>
