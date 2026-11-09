<div class="cf-turnstile" data-sitekey="{{ site_key }}" data-theme="{{ theme }}" data-size="{{ size }}" data-callback="onTurnstileSuccess"></div>

<script>
function onTurnstileSuccess(token) {
    console.log('Turnstile verification successful');
}
</script>
