[twig]
{% if not is_frame %}
	<style>
		#template-template_switch-container select {
			padding: 5px !important;
			border: 1px solid #ddd !important;
			border-radius: 4px !important;
			margin: 0 !important;
		}

		#template-template_switch-container button {
			padding: 5px 15px !important;
			background: #E01010 !important;
			color: white !important;
			border: none !important;
			border-radius: 4px !important;
			cursor: pointer !important;
			margin: 0 !important;
		}

		#template-template_switch-container button:hover {
			background: #C00 !important;
		}

		/* Сброс возможных влияний родительских элементов */
		html,
		body {
			margin-top: 50px !important; /* Компенсируем высоту фиксированного блока */
            overflow: hidden !important;
            margin: 0 !important;
			padding: 0 !important;
			height: 100% !important;
		}
		/* Стили для контейнера переключателя */
		#template-template_switch-container {
			position: fixed !important;
			top: 0 !important;
			left: 0 !important;
			width: 100% !important;
			background: #fff !important;
			padding: 20px !important;
			box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2) !important;
			z-index: 99999999 !important;
			display: flex !important;
			justify-content: center !important;
			align-items: center !important;
			gap: 10px !important;
			margin: 0 !important;
			border: none !important;
		}

		/* Основные стили для растягивания фрейма */
		#frame-container {
			position: absolute !important;
			top: 70px !important;
			left: 0 !important;
			right: 0 !important;
			bottom: 0 !important;
			width: 100% !important;
			height: calc(100% - 50px) !important;
			border: none !important;
			margin: 0 !important;
			padding: 0 !important;
			z-index: 9999999;

		}

		#template-preview-frame {
			width: 100% !important;
			height: 100% !important;
			border: none !important;
			margin: 0 !important;
			padding: 0 !important;
			overflow: auto !important; /* Разрешаем прокрутку */
		}
		#template-template_switch-container .download-btn {
    background: #4CAF50 !important;
    margin-left: 10px !important;
}

#template-template_switch-container .download-btn:hover {
    background: #45a049 !important;
}
	</style>

	<div id="template-template_switch-container">
		{l_template_switch_select}:
		<select id="template_switch_selector">
			{list}
		</select>
		<button onclick="updateTemplate()">Выбрать</button>
<!-- Кнопка будет показана только если есть description_link -->
<button id="downloadBtn" class="download-btn" onclick="window.open('{description_link}', '_blank')" {if !description_link}style="display:none" {/if}>
	Скачать описание
</button>

	</div>

	<div id="frame-container">
<iframe id="template-preview-frame" name="template-preview-frame" src="?template_switch_frame=1&template={current_template}" sandbox="allow-same-origin allow-forms allow-top-navigation allow-scripts"></iframe>

	</div>

	<script>
		// Функция для обновления iframe
function updateTemplate() {
const selector = document.getElementById('template_switch_selector');
const iframe = document.getElementById('template-preview-frame');

// Сохраняем выбор в cookie
const date = new Date();
date.setTime(date.getTime() + (365 * 24 * 60 * 60 * 1000));
document.cookie = `sw_template=${
selector.value
}; expires=${
date.toUTCString()
}; path=/`;

// Обновляем iframe
iframe.src = `?template_switch_frame=1&template=${
selector.value
}`;
}

// Обработка содержимого фрейма
window.addEventListener('DOMContentLoaded', function () {
const iframe = document.getElementById('template-preview-frame');

iframe.addEventListener('load', function () {
try {
const frameDoc = iframe.contentDocument || iframe.contentWindow.document;

// Добавляем параметр ко всем ссылкам
const links = frameDoc.querySelectorAll('a[href]');
links.forEach(link => {
const url = new URL(link.href, window.location.href);
if (url.hostname === window.location.hostname) {
url.searchParams.set('template_switch_frame', '1');
link.href = url.toString();
}
});

// Оставляем стандартное поведение прокрутки
frameDoc.body.style.overflow = 'auto';
frameDoc.body.style.margin = '0';
frameDoc.body.style.padding = '0';

} catch (e) {
console.log('Frame access error:', e);
}
});
});
// Обновляем кнопку скачивания при смене шаблона
function updateTemplate() {
    const selector = document.getElementById('template_switch_selector');
    const iframe = document.getElementById('template-preview-frame');
    
    // Получаем ID выбранного профиля
    const profileName = selector.options[selector.selectedIndex].text;
    const profileId = profileName.toLowerCase().replace(/[^a-z0-9]/g, '');
    
    // Сохраняем выбор в cookie
    const date = new Date();
    date.setTime(date.getTime() + (365 * 24 * 60 * 60 * 1000));
    document.cookie = `sw_template=${selector.value}; expires=${date.toUTCString()}; path=/`;
    
    // Обновляем iframe с правильным ID профиля
    iframe.src = `?template_switch_frame=1&template=${selector.value}&profile=${profileId}`;
}
	</script>
{% endif %}

[/twig]
