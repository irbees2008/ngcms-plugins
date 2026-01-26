<div class="cf-turnstile" data-sitekey="{{ site_key }}" data-theme="{{ theme }}" data-size="{{ size }}" data-appearance="{{ appearance }}" data-callback="onTurnstileSuccess" data-error-callback="onTurnstileError" data-expired-callback="onTurnstileExpired"></div>

 <script>
// Callback функции для Turnstile
function onTurnstileSuccess(token) {
    console.log('Turnstile verification successful');
    // Токен автоматически добавляется в форму как cf-turnstile-response
}

function onTurnstileError(error) {
    console.error('Turnstile error:', error);
    alert('Ошибка проверки безопасности. Пожалуйста, обновите страницу.');
}

function onTurnstileExpired() {
    console.warn('Turnstile token expired');
    // Токен истек, Turnstile автоматически обновит виджет
}
</script>
