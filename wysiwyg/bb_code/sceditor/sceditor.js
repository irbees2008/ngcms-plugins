/**
 * SCEditor
 * http://www.sceditor.com/
 *
 * Copyright (C) 2017, Sam Clarke (samclarke.com)
 *
 * SCEditor is licensed under the MIT license:
 *	http://www.opensource.org/licenses/mit-license.php
 *
 * @fileoverview SCEditor - A lightweight WYSIWYG BBCode and HTML editor
 * @author Sam Clarke
 */
// SCEditor initializer for NGCMS WYSIWYG plugin
// Attaches SCEditor (BBCode format) to all textareas with class `bb_code`
(function () {
  function onReady(fn) {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", fn, { once: true });
    } else {
      fn();
    }
  }
  onReady(function () {
    if (
      typeof window.sceditor === "undefined" ||
      !window.sceditor ||
      typeof window.sceditor.create !== "function"
    ) {
      // SCEditor library not loaded – nothing to do
      return;
    }
    // Use CDN theme style inside the editor iframe to ensure proper styling
    var themeStyleUrl =
      "https://cdn.jsdelivr.net/npm/sceditor@3/minified/themes/default.min.css";
    var nodes = document.querySelectorAll("textarea.bb_code");
    if (!nodes.length) {
      return;
    }
    // Build absolute URL to plugin's emoticons folder based on current script path
    var scriptSrc =
      (document.currentScript && document.currentScript.src) ||
      (function () {
        var scripts = document.getElementsByTagName("script");
        for (var i = scripts.length - 1; i >= 0; i--) {
          var s = scripts[i].src || "";
          if (
            s.indexOf("/plugins/wysiwyg/bb_code/sceditor/sceditor.js") !== -1
          ) {
            return s;
          }
        }
        return "";
      })();
    var pluginBase = scriptSrc
      ? scriptSrc.replace(/sceditor\.js(?:\?.*)?$/, "")
      : "";
    var emoticonsBase = pluginBase
      ? pluginBase + ""
      : "https://cdn.jsdelivr.net/npm/sceditor@3/minified/emoticons/";
    nodes.forEach(function (ta) {
      // Prevent double init
      if (ta.dataset.sceditorInited === "1") {
        return;
      }
      try {
        window.sceditor.create(ta, {
          format: "bbcode",
          style: themeStyleUrl,
          // Point emoticons to plugin folder; fallback to CDN if base cannot be detected
          emoticonsRoot: emoticonsBase,
          // Keep toolbar reasonably compact; defaults are okay, but we can define a sane set
          // toolbar: 'bold,italic,underline,strike|left,center,right|bulletlist,orderedlist|link,unlink,image,quote,code|source',
          emoticonsEnabled: true,
          resizeEnabled: true,
          // Allow pasting plain text by default to avoid messy HTML
          paste: {
            keepHtml: false,
          },
        });
        ta.dataset.sceditorInited = "1";
      } catch (e) {
        // Silent fail to avoid breaking forms
        // console.error('SCEditor init failed:', e);
      }
    });
  });
})();
