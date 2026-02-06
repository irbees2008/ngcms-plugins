<script>
document.addEventListener('DOMContentLoaded', function() {
    const FORMS = document.getElementsByTagName('form');

    [...FORMS].forEach(function(form) {
        if ('g-recaptcha-response' in form.elements) {
            if (form.hasAttribute('onsubmit')) {
                form.setAttribute('data-onsubmit', form.getAttribute('onsubmit'));
                form.removeAttribute('onsubmit');
            }

            form.addEventListener('submit', attachGRecaptchaToken);
        }
    });

    function attachGRecaptchaToken(event) {
        event.preventDefault();

        const form = event.target;
        const input = form.elements['g-recaptcha-response'];

        if (typeof grecaptcha !== 'undefined') {
            grecaptcha.ready(function() {
                grecaptcha.execute('{{ site_key }}', {
                    action: form.id || '{{ action }}'
                }).then(function(token) {
                    input.value = token;

                    let result = true;

                    if (form.hasAttribute('data-onsubmit')) {
                        try {
                            result = eval(form.getAttribute('data-onsubmit'));
                        } catch (e) {
                            console.error('Error in onsubmit:', e);
                        }
                    }

                    if (result !== false) {
                        form.submit();
                    }
                });
            });
        } else {
            console.error('Google reCAPTCHA not loaded');
            form.submit();
        }
    }
});
</script>
