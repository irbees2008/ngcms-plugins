/*!
 * AVTEK Callback — autosubmit (embed-only)
 * Purpose: keep user on the same page after submit, show only success text OR redirect.
 * Works when the form HTML is rendered server-side (Twig callPlugin / direct embed).
 * If you inject the form via innerHTML from fetch(), browsers usually do NOT execute <script> tags.
 * In that case include this JS once in the template manually.
 */
(function () {
  if (window.AVTEK_CB_AUTOSUBMIT) return;
  window.AVTEK_CB_AUTOSUBMIT = true;

  function closestForm(el) {
    while (el && el !== document) {
      if (el.tagName === 'FORM' && el.getAttribute('data-avtek-cb-form') === '1') return el;
      el = el.parentNode;
    }
    return null;
  }

  function ensureResultBox(form) {
    var box = form.querySelector('[data-avtek-cb-result="1"]');
    if (!box) {
      box = document.createElement('div');
      box.setAttribute('data-avtek-cb-result', '1');
      box.style.marginTop = '10px';
      box.style.fontWeight = '600';
      form.appendChild(box);
    }
    return box;
  }

  function disableForm(form, flag) {
    var els = form.querySelectorAll('button, input, textarea, select');
    for (var i = 0; i < els.length; i++) {
      if (els[i].hasAttribute('data-avtek-cb-keep-enabled')) continue;
      els[i].disabled = !!flag;
    }
  }

  async function handleSubmit(ev) {
    var form = closestForm(ev.target);
    if (!form) return;

    ev.preventDefault();
    ev.stopPropagation();

    var action = form.getAttribute('action') || '';
    if (!action) return;

    var url;
    try {
      url = new URL(action, window.location.origin);
    } catch (e) {
      return;
    }
    // Ask server for JSON response for AJAX
    url.searchParams.set('ajax', '1');

    var successMode = form.getAttribute('data-avtek-cb-success-mode') || 'inline';
    var redirectUrl = form.getAttribute('data-avtek-cb-redirect-url') || '';
    var defaultOkText = form.getAttribute('data-avtek-cb-success-text') || 'Спасибо! Заявка отправлена.';

    var resultBox = ensureResultBox(form);
    resultBox.textContent = '';

    disableForm(form, true);

    try {
      var resp = await fetch(url.toString(), {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: new FormData(form)
      });

      var text = await resp.text();
      var data = null;
      try { data = JSON.parse(text); } catch (e) {}

      if (data && typeof data === 'object') {
        // Prefer server settings
        if (data.success_mode) successMode = data.success_mode;
        if (data.redirect_url) redirectUrl = data.redirect_url;
        if (data.message) defaultOkText = data.message;

        if (data.ok && successMode === 'redirect' && redirectUrl) {
          window.location.href = redirectUrl;
          return;
        }
        // Inline
        resultBox.textContent = defaultOkText;
        if (data.ok) {
          try { form.reset(); } catch (e) {}
        }
      } else {
        // Fallback: server returned plain text
        if (successMode === 'redirect' && redirectUrl) {
          window.location.href = redirectUrl;
          return;
        }
        resultBox.textContent = text || defaultOkText;
        try { form.reset(); } catch (e) {}
      }
    } catch (e) {
      resultBox.textContent = 'Ошибка отправки. Попробуйте позже.';
    } finally {
      disableForm(form, false);
    }
  }

  // Capture submit events globally
  document.addEventListener('submit', handleSubmit, true);
})();
