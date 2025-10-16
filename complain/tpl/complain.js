(function () {
  function q(sel, ctx) {
    return (ctx || document).querySelector(sel);
  }
  function qa(sel, ctx) {
    return Array.prototype.slice.call((ctx || document).querySelectorAll(sel));
  }
  function on(el, ev, fn, opts) {
    el.addEventListener(ev, fn, opts || false);
  }
  function ensureAjax(url) {
    if (!url) return url;
    return url.indexOf("ajax=1") === -1
      ? url + (url.indexOf("?") === -1 ? "?ajax=1" : "&ajax=1")
      : url;
  }
  function openModalWithHTML(innerHtml) {
    var existing = q("#complain-modal");
    if (existing) existing.remove();
    var modal = document.createElement("div");
    modal.className = "modal";
    modal.id = "complain-modal";
    modal.innerHTML =
      "" +
      '<div class="modal-box">' +
      '<div class="modal-clouse"></div>' +
      '<div class="title">Жалобы</div>' +
      '<div class="modal-content clearfix">' +
      innerHtml +
      "</div>" +
      "</div>";
    var shadow = document.createElement("div");
    shadow.className = "shadow-bg";
    document.body.appendChild(shadow);
    document.body.appendChild(modal);
    // simple fade-in
    shadow.style.display = "block";
    modal.style.display = "block";
    var modBox = q(".modal-box", modal);
    var boxClick = true;
    on(modBox, "click", function () {
      boxClick = false;
    });
    on(modal, "click", function () {
      if (boxClick) closeModal(modal, shadow);
      boxClick = true;
    });
    on(q(".modal-clouse", modal), "click", function () {
      closeModal(modal, shadow);
    });
    // ESC to close
    function onKey(e) {
      if (e.key === "Escape") {
        closeModal(modal, shadow);
        document.removeEventListener("keydown", onKey);
      }
    }
    document.addEventListener("keydown", onKey);
    // Wrap long tables for horizontal scroll in modal
    qa("table", q(".modal-content", modal)).forEach(function (tbl) {
      var wrap = document.createElement("div");
      wrap.className = "table-wrap";
      tbl.parentNode.insertBefore(wrap, tbl);
      wrap.appendChild(tbl);
    });
  }
  function closeModal(modal, shadow) {
    if (modal && modal.parentNode) modal.parentNode.removeChild(modal);
    if (shadow && shadow.parentNode) shadow.parentNode.removeChild(shadow);
  }
  function fetchHTML(url, cb) {
    fetch(url, { credentials: "same-origin" })
      .then(function (r) {
        return r.text();
      })
      .then(function (html) {
        cb(null, html);
      })
      .catch(function (err) {
        cb(err);
      });
  }
  function postForm(form, submitter, cb) {
    var url = ensureAjax(form.getAttribute("action") || window.location.href);
    var fd;
    try {
      // Modern browsers: include clicked submit button
      fd = new FormData(form, submitter || null);
    } catch (e) {
      fd = new FormData(form);
      if (submitter && submitter.name) {
        fd.append(submitter.name, submitter.value || "");
      }
    }
    // Ensure all checked incidents are present in payload (robustness across browsers)
    qa('input[type="checkbox"][name^="inc_"]', form).forEach(function (ch) {
      if (ch.checked) {
        try {
          fd.append(ch.name, ch.value || "1");
        } catch (_e) {}
      }
    });
    fetch(url, { method: "POST", body: fd, credentials: "same-origin" })
      .then(function (r) {
        return r.text();
      })
      .then(function (html) {
        cb(null, html);
      })
      .catch(function (err) {
        cb(err);
      });
  }
  // Delegate clicks on links with class complain-open (capture phase, to prevent navigation reliably)
  document.addEventListener(
    "click",
    function (e) {
      var target = e.target;
      if (!target || !target.closest) return;
      var a = target.closest("a.complain-open");
      if (!a) return;
      // ignore modified/middle/right clicks
      if (e.defaultPrevented) return;
      if (e.button !== 0) return; // left click only
      if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
      var href = a.getAttribute("href");
      if (!href || href.charAt(0) === "#") return;
      e.preventDefault();
      fetchHTML(ensureAjax(href), function (err, html) {
        if (err) {
          if (window.showToast) {
            window.showToast("Не удалось загрузить содержимое", {
              type: "error",
              title: "Ошибка",
            });
          } else {
            alert("Не удалось загрузить содержимое");
          }
          return;
        }
        openModalWithHTML(html);
      });
    },
    true
  );
  // Delegate submit for AJAX forms inside modal
  on(document, "submit", function (e) {
    var form = e.target.closest
      ? e.target.closest('.complain-form[data-ajax="true"]')
      : null;
    if (!form) return;
    // Fallback: ensure checked rows produce fields inc_<id>=1 if table uses checkboxes with name="inc_<id>"
    try {
      var tbody = form.querySelector("tbody");
      if (tbody) {
        qa('input[type="checkbox"][name^="inc_"]', tbody).forEach(function (
          ch
        ) {
          if (!ch.checked) return;
          // already part of form; nothing to do
        });
      }
    } catch (_e) {}
    e.preventDefault();
    var submitter =
      typeof e.submitter !== "undefined"
        ? e.submitter
        : form.__lastSubmitter || document.activeElement;
    // Client-side guard: for admin list require at least one checkbox
    if (form.getAttribute("data-list") === "1") {
      var anyChecked = qa('input[type="checkbox"][name^="inc_"]', form).some(
        function (ch) {
          return ch.checked;
        }
      );
      if (!anyChecked) {
        if (window.showToast) {
          window.showToast("Выберите хотя бы один отчёт", {
            type: "warning",
            title: "Внимание",
          });
        }
        return;
      }
    }
    postForm(form, submitter, function (err, html) {
      if (err) {
        if (window.showToast) {
          window.showToast("Не удалось отправить форму", {
            type: "error",
            title: "Ошибка",
          });
        } else {
          alert("Не удалось отправить форму");
        }
        return;
      }
      var modal = q("#complain-modal");
      if (!modal) return;
      // Try to detect success marker and close modal with toast
      var temp = document.createElement("div");
      temp.innerHTML = html;
      var marker = temp.querySelector('.complain-result[data-status="ok"]');
      if (marker) {
        var msg = marker.getAttribute("data-message") || "Жалоба отправлена";
        if (window.showToast) {
          window.showToast(msg, { type: "success", title: "Готово" });
        }
        // If this was an admin list form (data-list="1"), refresh content instead of closing
        if (form.getAttribute("data-list") === "1") {
          var refreshUrl =
            form.getAttribute("data-refresh-url") || location.href;
          fetchHTML(ensureAjax(refreshUrl), function (err2, fresh) {
            if (err2) {
              /* fallback: keep modal */ return;
            }
            var cont = q(".modal-content", modal);
            if (cont) cont.innerHTML = fresh;
            // refresh menu counter immediately
            if (typeof window.__complainUpdateCounter === "function") {
              window.__complainUpdateCounter();
            }
          });
        } else {
          closeModal(modal, q(".shadow-bg"));
          // refresh menu counter immediately
          if (typeof window.__complainUpdateCounter === "function") {
            window.__complainUpdateCounter();
          }
        }
        return;
      }
      // Otherwise replace modal content as usual
      var cont = q(".modal-content", modal);
      if (cont) cont.innerHTML = html;
    });
  });
  // Remember last clicked submitter inside complain forms
  document.addEventListener(
    "click",
    function (e) {
      var btn =
        e.target &&
        e.target.closest &&
        e.target.closest(
          '.complain-form[data-ajax="true"] button[type="submit"]'
        );
      if (!btn) return;
      var form = btn.closest("form");
      if (form) form.__lastSubmitter = btn;
    },
    true
  );
  // Make links clicked inside modal load via AJAX into modal as well (for fallback info pages)
  document.addEventListener(
    "click",
    function (e) {
      var modal = q("#complain-modal");
      if (!modal) return;
      var link =
        e.target &&
        e.target.closest &&
        e.target.closest(".modal-content a[href]");
      if (!link) return;
      var href = link.getAttribute("href");
      if (!href || href.indexOf("javascript:") === 0 || href.charAt(0) === "#")
        return;
      e.preventDefault();
      fetchHTML(ensureAjax(href), function (err, html) {
        if (err) return;
        var cont = q(".modal-content", modal);
        if (cont) cont.innerHTML = html;
      });
    },
    true
  );
  // Update complaints counter in usermenu periodically (init after DOM is ready)
  (function () {
    var urlCandidates = [];
    if (window.NG_COMPLAIN_COUNT_URL) {
      urlCandidates.push(window.NG_COMPLAIN_COUNT_URL);
    }
    urlCandidates.push(
      location.origin + "/plugin/complain/count",
      location.origin + "/plugin/complain/count/",
      location.origin + "/plugin/complain/?handler=count"
    );
    function pickMenuLink() {
      // Prefer a menu link marked with data-modal (to avoid picking news item links)
      return (
        document.querySelector('a.complain-open[data-modal="true"]') ||
        document.querySelector("a.complain-open")
      );
    }
    function rewriteTextPreservingMeaning(text, n) {
      // Replace first occurrence of (digits) with (n); if none, append
      if (/\(\d+\)/.test(text)) {
        return text.replace(/\(\d+\)/, "(" + n + ")");
      }
      return text.replace(/\s*$/, " (" + n + ")");
    }
    function fetchCount() {
      // try candidates sequentially until one returns JSON
      var i = 0;
      function next(resolve, reject) {
        if (i >= urlCandidates.length) {
          return reject && reject();
        }
        var u = urlCandidates[i++];
        fetch(u, { credentials: "same-origin" })
          .then(function (r) {
            if (!r.ok) throw new Error("HTTP " + r.status);
            return r.json();
          })
          .then(function (d) {
            resolve && resolve(d);
          })
          .catch(function () {
            next(resolve, reject);
          });
      }
      return new Promise(function (resolve, reject) {
        next(resolve, reject);
      });
    }
    function doUpdate() {
      var link = pickMenuLink();
      if (!link) return;
      fetchCount()
        .then(function (d) {
          try {
            var newText = rewriteTextPreservingMeaning(
              link.textContent || "",
              d.count
            );
            link.textContent = newText;
          } catch (_e) {
            // silent
          }
        })
        .catch(function () {});
    }
    // Expose manual trigger for immediate refresh after actions
    window.__complainUpdateCounter = doUpdate;
    function init() {
      doUpdate();
      setInterval(doUpdate, 30000);
    }
    if (
      document.readyState === "complete" ||
      document.readyState === "interactive"
    ) {
      // DOM is already ready
      setTimeout(init, 0);
    } else {
      document.addEventListener("DOMContentLoaded", init, { once: true });
    }
  })();
})();
