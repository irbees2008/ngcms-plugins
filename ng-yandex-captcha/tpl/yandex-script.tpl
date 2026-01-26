 <script>
document.addEventListener('DOMContentLoaded', function() {
    // Получаем все формы документа
    const FORMS = document.getElementsByTagName('form');

    // Перебираем все найденные формы
    [...FORMS].forEach(function(form) {
        // Если форма содержит поле для токена Яндекс Капчи
        if ('smart-token' in form.elements) {
            // Сохраняем оригинальный обработчик onsubmit
            if (form.hasAttribute('onsubmit')) {
                form.setAttribute('data-onsubmit', form.getAttribute('onsubmit'));
                form.removeAttribute('onsubmit');
            }

            // Создаем контейнер для виджета капчи
            const captchaContainer = document.createElement('div');
            captchaContainer.id = 'captcha-container-' + Math.random().toString(36).substr(2, 9);
            captchaContainer.className = 'smart-captcha';
            captchaContainer.setAttribute('data-sitekey', '{{ site_key }}');

            // Вставляем контейнер перед кнопкой отправки
            const submitButton = form.querySelector('[type="submit"]');
            if (submitButton) {
                submitButton.parentNode.insertBefore(captchaContainer, submitButton);
            } else {
                form.appendChild(captchaContainer);
            }

            // Рендерим виджет капчи
            if (typeof window.smartCaptcha !== 'undefined') {
                const widgetId = window.smartCaptcha.render(captchaContainer, {
                    sitekey: '{{ site_key }}',
                    invisible: false,
                    callback: function(token) {
                        // Устанавливаем токен в скрытое поле
                        form.elements['smart-token'].value = token;
                    }
                });
            }

            // Вешаем обработчик на отправку формы
            form.addEventListener('submit', attachYandexCaptchaToken);
        }
    });

    /**
     * Прикрепление токена к форме при отправке.
     * @param  {Event} event
     * @return {void}
     */
    function attachYandexCaptchaToken(event) {
        const form = event.target;
        const input = form.elements['smart-token'];

        // Проверяем, есть ли токен
        if (!input.value) {
            event.preventDefault();
            alert('Пожалуйста, пройдите проверку капчи');
            return false;
        }

        let result = true;

        // Если есть сохраненный onsubmit, выполняем его
        if (form.hasAttribute('data-onsubmit')) {
            const onsubmitCode = form.getAttribute('data-onsubmit');
            try {
                result = eval(onsubmitCode);
            } catch (e) {
                console.error('Error executing onsubmit:', e);
                result = false;
            }
        }

        // Если результат false, отменяем отправку
        if (result === false) {
            event.preventDefault();
            return false;
        }

        // Если результат не false, разрешаем отправку формы
        return true;
    }
});
</script>
