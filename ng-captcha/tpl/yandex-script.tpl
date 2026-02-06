 <script>
document.addEventListener('DOMContentLoaded', function() {
    const FORMS = document.getElementsByTagName('form');

    [...FORMS].forEach(function(form) {
        if ('smart-token' in form.elements) {
            if (form.hasAttribute('onsubmit')) {
                form.setAttribute('data-onsubmit', form.getAttribute('onsubmit'));
                form.removeAttribute('onsubmit');
            }

            const captchaContainer = document.getElementById('captcha-container-{{ random_id }}') ||
                                    document.createElement('div');

            if (!captchaContainer.id) {
                captchaContainer.id = 'captcha-container-' + Math.random().toString(36).substr(2, 9);
                captchaContainer.className = 'smart-captcha';

                const submitButton = form.querySelector('[type="submit"]');
                if (submitButton) {
                    submitButton.parentNode.insertBefore(captchaContainer, submitButton);
                } else {
                    form.appendChild(captchaContainer);
                }
            }

            if (typeof window.smartCaptcha !== 'undefined') {
                window.smartCaptcha.render(captchaContainer, {
                    sitekey: '{{ site_key }}',
                    invisible: false,
                    callback: function(token) {
                        form.elements['smart-token'].value = token;
                    }
                });
            }

            form.addEventListener('submit', function(event) {
                const token = form.elements['smart-token'].value;
                if (!token) {
                    event.preventDefault();
                    alert('Пожалуйста, пройдите проверку безопасности');
                    return false;
                }
            });
        }
    });
});
</script>
