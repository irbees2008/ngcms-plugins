// SCEditor initializer for NGCMS WYSIWYG plugin (local assets only)
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
      return;
    }
    // Register/ensure toolbar commands
    try {
      var cmdApi = window.sceditor.command;
      if (
        cmdApi &&
        typeof cmdApi.get === "function" &&
        typeof cmdApi.set === "function"
      ) {
        if (!cmdApi.get("undo")) {
          cmdApi.set("undo", {
            exec: function () {
              try {
                return this.undo();
              } catch (_) {
                return false;
              }
            },
            txtExec: function () {
              try {
                return this.undo();
              } catch (_) {
                return false;
              }
            },
            tooltip: "Undo",
          });
        }
        if (!cmdApi.get("redo")) {
          cmdApi.set("redo", {
            exec: function () {
              try {
                return this.redo();
              } catch (_) {
                return false;
              }
            },
            txtExec: function () {
              try {
                return this.redo();
              } catch (_) {
                return false;
              }
            },
            tooltip: "Redo",
          });
        }
        if (!cmdApi.get("para")) {
          cmdApi.set("para", {
            tooltip: "Paragraph",
            exec: function () {
              this.insert("[p]", "[/p]");
            },
            txtExec: function () {
              this.insert("[p]", "[/p]");
            },
          });
        }
        if (!cmdApi.get("spoiler")) {
          cmdApi.set("spoiler", {
            tooltip: "Spoiler",
            exec: function () {
              this.insert("[spoiler]", "[/spoiler]");
            },
            txtExec: function () {
              this.insert("[spoiler]", "[/spoiler]");
            },
          });
        }
        if (!cmdApi.get("acronym")) {
          cmdApi.set("acronym", {
            tooltip: "Acronym",
            exec: function () {
              try {
                var inst = this;
                var bridge = window.__NG_EDITOR_BRIDGE__;
                if (bridge && typeof bridge.set === "function") {
                  bridge.set(function (payload) {
                    try {
                      var title =
                        payload && payload.title
                          ? String(payload.title).replace(/\]/g, ")").trim()
                          : "";
                      if (!title) return;
                      inst.insert("[acronym=" + title + "]", "[/acronym]");
                    } catch (_) {}
                  });
                }
                if (typeof window.prepareAcronymModal === "function") {
                  try {
                    window.prepareAcronymModal("");
                  } catch (_) {}
                }
                if (typeof window.showModalById === "function") {
                  window.showModalById("modal-insert-acronym");
                }
              } catch (_) {}
            },
            txtExec: function () {
              this.exec();
            },
          });
        }
        if (!cmdApi.get("hide")) {
          cmdApi.set("hide", {
            tooltip: "Hide",
            exec: function () {
              this.insert("[hide]", "[/hide]");
            },
            txtExec: function () {
              this.insert("[hide]", "[/hide]");
            },
          });
        }
        if (!cmdApi.get("media")) {
          cmdApi.set("media", {
            tooltip: "Media",
            exec: function () {
              var inst = this;
              try {
                if (typeof openNgcmsMediaDialog === "function") {
                  return openNgcmsMediaDialog(function (url, w, h, p) {
                    try {
                      var attrs = "";
                      if (w)
                        attrs += " width=" + String(w).replace(/[^0-9]/g, "");
                      if (h)
                        attrs += " height=" + String(h).replace(/[^0-9]/g, "");
                      if (p)
                        attrs += " preview=" + String(p).replace(/\s+/g, "");
                      var open = attrs ? "[media" + attrs + "]" : "[media]";
                      inst.insert(
                        open + String(url).trim() + "[/media]",
                        null,
                        false,
                        true
                      );
                    } catch (_) {}
                  });
                }
                var url = prompt("Media URL", "");
                if (!url) return;
                inst.insert("[media]" + url + "[/media]", null, false, true);
              } catch (_) {}
            },
            txtExec: function () {
              this.exec();
            },
          });
        }
        // Helper: safe insert of [code=lang]..[/code] that preserves raw BBCode
        var __ng_safeInsertCode = function (inst, lang) {
          lang = String(lang || "").trim();
          var o = "[code" + (lang ? "=" + lang : "") + "]";
          var c = "[/code]";
          var wasSrc = false;
          try {
            wasSrc =
              typeof inst.sourceMode === "function"
                ? !!inst.sourceMode()
                : false;
          } catch (_) {
            wasSrc = false;
          }
          try {
            if (!wasSrc && typeof inst.toggleSourceMode === "function") {
              // Switch to Source to insert raw BBCode safely
              inst.toggleSourceMode();
            }
            var nowSrc = false;
            try {
              nowSrc =
                typeof inst.sourceMode === "function"
                  ? !!inst.sourceMode()
                  : false;
            } catch (_) {}
            if (nowSrc) {
              try {
                inst.insertText(o + c);
              } catch (_) {}
            } else {
              try {
                inst.insert(o, c);
              } catch (_) {
                try {
                  inst.insertText(o + c);
                } catch (__) {}
              }
            }
          } finally {
            try {
              if (!wasSrc && typeof inst.toggleSourceMode === "function") {
                inst.toggleSourceMode();
              }
            } catch (_) {}
          }
        };
        if (!cmdApi.get("codebrush")) {
          cmdApi.set("codebrush", {
            tooltip: "Code (language)",
            exec: function () {
              var inst = this;
              try {
                var doInsert = function (lang) {
                  __ng_safeInsertCode(inst, lang);
                };
                if (typeof openNgcmsCodeDialog === "function") {
                  return openNgcmsCodeDialog(function (lang) {
                    doInsert(lang);
                  });
                }
                var l = prompt(
                  "Language (e.g. php, js, sql, xml, css, bash, python, java, csharp, cpp, plain)",
                  ""
                );
                if (l === null) return;
                doInsert(l);
              } catch (_) {}
            },
            txtExec: function () {
              this.exec();
            },
          });
        }
        // Override default 'code' to ensure raw BBCode insert works reliably
        cmdApi.set("code", {
          tooltip: "Code",
          exec: function () {
            try {
              __ng_safeInsertCode(this, "");
            } catch (_) {}
          },
          txtExec: function () {
            try {
              __ng_safeInsertCode(this, "");
            } catch (_) {}
          },
        });
        if (!cmdApi.get("ngimage")) {
          cmdApi.set("ngimage", {
            tooltip: "Insert image (manager)",
            exec: function () {
              var id = "";
              try {
                var src = this && this.textarea ? this.textarea : null;
                if (src && src.id) id = src.id;
              } catch (_) {}
              try {
                var f = document.forms["DATA_tmp_storage"];
                if (f && f.area) f.area.value = id;
              } catch (_) {}
              try {
                window.open(
                  "?mod=images&ifield=" + encodeURIComponent(id),
                  "_Addimage",
                  "height=600,resizable=yes,scrollbars=yes,width=800"
                );
              } catch (_) {}
            },
            txtExec: function () {
              this.exec();
            },
          });
        }
        if (!cmdApi.get("ngfile")) {
          cmdApi.set("ngfile", {
            tooltip: "Insert file (manager)",
            exec: function () {
              var id = "";
              try {
                var src = this && this.textarea ? this.textarea : null;
                if (src && src.id) id = src.id;
              } catch (_) {}
              try {
                var f = document.forms["DATA_tmp_storage"];
                if (f && f.area) f.area.value = id;
              } catch (_) {}
              try {
                window.open(
                  "?mod=files&ifield=" + encodeURIComponent(id),
                  "_Addfile",
                  "height=600,resizable=yes,scrollbars=yes,width=800"
                );
              } catch (_) {}
            },
            txtExec: function () {
              this.exec();
            },
          });
        }
        if (!cmdApi.get("nextpage")) {
          cmdApi.set("nextpage", {
            tooltip: "Next page",
            exec: function () {
              var wasSrc =
                typeof this.sourceMode === "function"
                  ? this.sourceMode()
                  : false;
              if (!wasSrc && typeof this.sourceMode === "function")
                this.sourceMode(true);
              this.insertText("<!--nextpage-->", null);
              if (!wasSrc && typeof this.sourceMode === "function")
                this.sourceMode(false);
            },
            txtExec: function () {
              this.insertText("<!--nextpage-->", null);
            },
          });
        }
        if (!cmdApi.get("more")) {
          cmdApi.set("more", {
            tooltip: "More",
            exec: function () {
              var wasSrc =
                typeof this.sourceMode === "function"
                  ? this.sourceMode()
                  : false;
              if (!wasSrc && typeof this.sourceMode === "function")
                this.sourceMode(true);
              this.insertText("<!--more-->", null);
              if (!wasSrc && typeof this.sourceMode === "function")
                this.sourceMode(false);
            },
            txtExec: function () {
              this.insertText("<!--more-->", null);
            },
          });
        }
      }
    } catch (e) {}
    // Resolve plugin base path
    var scriptSrc =
      (document.currentScript && document.currentScript.src) ||
      (function () {
        var scripts = document.getElementsByTagName("script");
        for (var i = scripts.length - 1; i >= 0; i--) {
          var s = scripts[i].src || "";
          if (s.indexOf("/plugins/wysiwyg/bb_code/sceditor/init.js") !== -1)
            return s;
        }
        return "";
      })();
    if (!scriptSrc) return;
    var pluginBase = scriptSrc.replace(/init\.js(?:\?.*)?$/, "");
    // Lightweight plugin-side modal helpers (no dependency on template)
    try {
      if (typeof window.openNgcmsMediaDialog !== "function") {
        window.openNgcmsMediaDialog = function (onOk) {
          try {
            var overlay = document.createElement("div");
            overlay.style.cssText =
              "position:fixed;inset:0;background:rgba(0,0,0,.3);z-index:2147483000;display:flex;align-items:center;justify-content:center;";
            var box = document.createElement("div");
            box.style.cssText =
              "background:#fff;padding:16px;min-width:320px;max-width:90vw;border-radius:6px;box-shadow:0 4px 18px rgba(0,0,0,.2);font:14px/1.4 Arial,sans-serif;";
            box.innerHTML =
              '<div style="font-weight:600;margin-bottom:8px;">Вставка видео/медиа</div>' +
              '<div style="margin-bottom:8px;"><label>URL<br><input type="text" style="width:100%" id="__ng_media_url"></label></div>' +
              '<div style="display:flex;gap:8px;margin-bottom:8px;">' +
              '<label style="flex:1">Ширина<br><input type="number" min="0" style="width:100%" id="__ng_media_w"></label>' +
              '<label style="flex:1">Высота<br><input type="number" min="0" style="width:100%" id="__ng_media_h"></label>' +
              "</div>" +
              '<div style="margin-bottom:12px;"><label>Превью (URL)<br><input type="text" style="width:100%" id="__ng_media_p"></label></div>' +
              '<div style="text-align:right;display:flex;gap:8px;justify-content:flex-end">' +
              '<button type="button" id="__ng_media_cancel" style="padding:6px 10px;">Отмена</button>' +
              '<button type="button" id="__ng_media_ok" style="padding:6px 10px;background:#1a73e8;color:#fff;border:0;border-radius:4px;">Вставить</button>' +
              "</div>";
            overlay.appendChild(box);
            document.body.appendChild(overlay);
            var urlI = box.querySelector("#__ng_media_url"),
              wI = box.querySelector("#__ng_media_w"),
              hI = box.querySelector("#__ng_media_h"),
              pI = box.querySelector("#__ng_media_p");
            var close = function () {
              try {
                document.body.removeChild(overlay);
              } catch (_) {}
            };
            box.querySelector("#__ng_media_cancel").onclick = close;
            box.querySelector("#__ng_media_ok").onclick = function () {
              var u = (urlI.value || "").trim();
              if (!u) {
                urlI.focus();
                return;
              }
              try {
                onOk && onOk(u, wI.value, hI.value, pI.value);
              } catch (_) {}
              close();
            };
            setTimeout(function () {
              try {
                urlI.focus();
              } catch (_) {}
            }, 0);
          } catch (e) {}
        };
      }
      if (typeof window.openNgcmsCodeDialog !== "function") {
        window.openNgcmsCodeDialog = function (onOk) {
          try {
            var overlay = document.createElement("div");
            overlay.style.cssText =
              "position:fixed;inset:0;background:rgba(0,0,0,.3);z-index:2147483000;display:flex;align-items:center;justify-content:center;";
            var box = document.createElement("div");
            box.style.cssText =
              "background:#fff;padding:16px;min-width:320px;max-width:90vw;border-radius:6px;box-shadow:0 4px 18px rgba(0,0,0,.2);font:14px/1.4 Arial,sans-serif;";
            box.innerHTML =
              '<div style="font-weight:600;margin-bottom:8px;">Вставка кода</div>' +
              '<div style="margin-bottom:12px;"><label>Язык (php, js, sql, xml, css, bash, ...)<br><input type="text" style="width:100%" id="__ng_code_lang"></label></div>' +
              '<div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:12px;">' +
              [
                "php",
                "js",
                "sql",
                "xml",
                "css",
                "bash",
                "python",
                "java",
                "csharp",
                "cpp",
                "plain",
              ]
                .map(function (n) {
                  return (
                    '<button type="button" data-lang="' +
                    n +
                    '" style="padding:4px 8px;border:1px solid #ddd;border-radius:4px;background:#f7f7f7;cursor:pointer;">' +
                    n.toUpperCase() +
                    "</button>"
                  );
                })
                .join("") +
              "</div>" +
              '<div style="text-align:right;display:flex;gap:8px;justify-content:flex-end">' +
              '<button type="button" id="__ng_code_cancel" style="padding:6px 10px;">Отмена</button>' +
              '<button type="button" id="__ng_code_ok" style="padding:6px 10px;background:#1a73e8;color:#fff;border:0;border-radius:4px;">Вставить</button>' +
              "</div>";
            overlay.appendChild(box);
            document.body.appendChild(overlay);
            var langI = box.querySelector("#__ng_code_lang");
            box.querySelectorAll("[data-lang]").forEach(function (b) {
              b.onclick = function () {
                langI.value = this.getAttribute("data-lang") || "";
              };
            });
            var close = function () {
              try {
                document.body.removeChild(overlay);
              } catch (_) {}
            };
            box.querySelector("#__ng_code_cancel").onclick = close;
            box.querySelector("#__ng_code_ok").onclick = function () {
              var v = (langI.value || "").trim();
              try {
                onOk && onOk(v);
              } catch (_) {}
              close();
            };
            setTimeout(function () {
              try {
                langI.focus();
              } catch (_) {}
            }, 0);
          } catch (e) {}
        };
      }
    } catch (e) {}
    // Theme style inside the editor iframe (local)
    var themeStyleUrl = pluginBase + "themes/content/default.css";
    // Emoticons base folder (local). Do NOT append 'emoticons/' here,
    // SCEditor already prefixes each icon with 'emoticons/...'
    // so base must point to the sceditor/ folder.
    var emoticonsBase = pluginBase;
    var nodes = document.querySelectorAll("textarea.bb_code");
    if (!nodes.length) {
      return;
    }
    // Ensure undo/redo buttons are present in toolbar
    var toolbarStr =
      (window.sceditor &&
        window.sceditor.defaultOptions &&
        window.sceditor.defaultOptions.toolbar) ||
      "";
    if (toolbarStr && toolbarStr.indexOf("undo") === -1) {
      if (toolbarStr.indexOf("cut,copy,pastetext") !== -1) {
        toolbarStr = toolbarStr.replace(
          "cut,copy,pastetext",
          "cut,copy,pastetext|undo,redo"
        );
      } else {
        toolbarStr = toolbarStr + "|undo,redo";
      }
    }
    // Replace default image with NGCMS image picker and add NGCMS file picker
    try {
      if (toolbarStr) {
        toolbarStr = toolbarStr.replace(
          /(^|[|,])image([|,]|$)/g,
          function (m, p1, p2) {
            return p1 + "ngimage" + p2;
          }
        );
        if (toolbarStr.indexOf("ngfile") === -1) {
          toolbarStr += (toolbarStr ? "|" : "") + "ngfile";
        }
      }
    } catch (_) {}
    // Append NGCMS custom commands group if not present
    var customGroup = "para,spoiler,acronym,hide,media,codebrush,nextpage,more";
    [
      "para",
      "spoiler",
      "acronym",
      "hide",
      "media",
      "codebrush",
      "nextpage",
      "more",
    ].forEach(function (cmd) {
      if (toolbarStr.indexOf(cmd) === -1) {
        toolbarStr += (toolbarStr ? "|" : "") + customGroup;
        // break addition after first injection
        cmd = null;
      }
    });
    // If default toolbar not available, set an explicit, sensible BBCode toolbar
    var explicitDefaultToolbar =
      "bold,italic,underline,strike|left,center,right,justify|bulletlist,orderedlist|quote,code|link,unlink,ngimage,ngfile,emojis|undo,redo|source|para,spoiler,acronym,hide,media,codebrush,nextpage,more";
    // Keep track of active/focused instance for cases when callers don't pass field id
    window.__ng_sceditor_by_id =
      window.__ng_sceditor_by_id || Object.create(null);
    window.__ng_sceditor_last = window.__ng_sceditor_last || null;
    nodes.forEach(function (ta) {
      if (ta.dataset.sceditorInited === "1") {
        return;
      }
      try {
        window.sceditor.create(ta, {
          format: "bbcode",
          style: themeStyleUrl,
          emoticonsRoot: emoticonsBase,
          // Use Font Awesome 4.7 toolbar icons
          icons: "fontawesome4",
          // Enable only essential plugins (v3 undo + v1compat); others can be added later
          plugins: "undo,v1compat",
          // Приводим к дефолтному тулбару SCEditor + добавляем undo,redo
          toolbar: toolbarStr || explicitDefaultToolbar,
          emoticonsEnabled: true,
          resizeEnabled: true,
          // Provide a small built-in emoji list to avoid requiring EmojiMart
          emojis: [
            "😀",
            "😁",
            "😂",
            "🤣",
            "😊",
            "😍",
            "😘",
            "😎",
            "🤔",
            "😴",
            "😉",
            "🙃",
            "🙂",
            "😇",
            "😅",
            "🤗",
            "🤩",
            "🤨",
            "🤯",
            "😐",
            "👍",
            "👎",
            "🙏",
            "👏",
            "🔥",
            "✨",
            "❤️",
            "💔",
            "🎉",
            "✅",
          ],
          paste: {
            keepHtml: false,
          },
        });
        // Defensive: ensure WYSIWYG mode is active, focus editor, and make body editable
        try {
          var inst = window.sceditor.instance(ta);
          if (inst) {
            try {
              var id = ta && ta.id ? ta.id : null;
              if (id) {
                window.__ng_sceditor_by_id[id] = inst;
              }
              window.__ng_sceditor_last = inst;
              // Track focus to know the last active instance
              var setLast = function () {
                try {
                  window.__ng_sceditor_last = inst;
                } catch (_) {}
              };
              try {
                inst.bind("focus", setLast);
              } catch (_) {}
              try {
                inst.bind("keyup", setLast);
              } catch (_) {}
              try {
                var cont =
                  inst.getContentAreaContainer &&
                  inst.getContentAreaContainer();
                if (cont) {
                  cont.addEventListener("mousedown", setLast, true);
                  cont.addEventListener("mouseup", setLast, true);
                }
              } catch (_) {}
            } catch (_) {}
            // --- Begin: Patch global inserters to target SCEditor when available ---
            try {
              if (!window.__ng_sceditor_patched__) {
                window.__ng_sceditor_patched__ = true;
                var _origInsertext = window.insertext;
                window.insertext = function (open, close, field) {
                  try {
                    var resolveEl = function (f) {
                      try {
                        if (!f) return null;
                        if (f && f.nodeType === 1) return f;
                        if (typeof f === "string")
                          return document.getElementById(f);
                        return document.getElementById(String(f));
                      } catch (_) {
                        return null;
                      }
                    };
                    var el = resolveEl(field) || resolveEl("content");
                    var ed = el
                      ? window.sceditor.instance(el) ||
                        (el.id && window.__ng_sceditor_by_id
                          ? window.__ng_sceditor_by_id[el.id]
                          : null)
                      : null;
                    if (!ed && window.__ng_sceditor_last) {
                      ed = window.__ng_sceditor_last;
                    }
                    if (ed) {
                      var o = String(open || "");
                      var c = String(close || "");
                      var isCodeWrap =
                        /\[code(?:=[^\]]+)?\]/i.test(o) &&
                        /\[\/code\]/i.test(c);
                      try {
                        if (isCodeWrap) {
                          // For brushes path: in WYSIWYG just wrap selection; in Source wrap selection manually
                          var inSrc = false;
                          try {
                            inSrc =
                              typeof ed.sourceMode === "function"
                                ? !!ed.sourceMode()
                                : false;
                          } catch (_) {
                            inSrc = false;
                          }
                          if (inSrc) {
                            var v = "";
                            try {
                              v = ed.getSourceEditorValue
                                ? ed.getSourceEditorValue(false)
                                : ed.val();
                            } catch (_) {
                              v = ed.val();
                            }
                            var cr = null;
                            try {
                              cr = ed.sourceEditorCaret
                                ? ed.sourceEditorCaret()
                                : null;
                            } catch (_) {}
                            if (
                              cr &&
                              typeof cr.start === "number" &&
                              typeof cr.end === "number"
                            ) {
                              var b = v.substring(0, cr.start);
                              var m = v.substring(cr.start, cr.end);
                              var a = v.substring(cr.end);
                              ed.val(b + o + m + c + a, false);
                              try {
                                ed.sourceEditorCaret({
                                  start: (b + o).length,
                                  end: (b + o + m).length,
                                });
                              } catch (_) {}
                            } else {
                              ed.insertText(o + c);
                            }
                          } else {
                            ed.insert(o, c);
                          }
                        } else {
                          ed.insert(o, c);
                        }
                      } catch (_) {}
                      return true;
                    }
                  } catch (e) {}
                  if (typeof _origInsertext === "function") {
                    return _origInsertext.apply(this, arguments);
                  }
                  return false;
                };
                var _origInsertimage = window.insertimage;
                window.insertimage = function (text, area) {
                  try {
                    var resolveEl2 = function (f) {
                      try {
                        if (!f) return null;
                        if (f && f.nodeType === 1) return f;
                        if (typeof f === "string")
                          return document.getElementById(f);
                        return document.getElementById(String(f));
                      } catch (_) {
                        return null;
                      }
                    };
                    var el = resolveEl2(area) || resolveEl2("content");
                    var ed = el
                      ? window.sceditor.instance(el) ||
                        (el.id && window.__ng_sceditor_by_id
                          ? window.__ng_sceditor_by_id[el.id]
                          : null)
                      : null;
                    if (!ed && window.__ng_sceditor_last) {
                      ed = window.__ng_sceditor_last;
                    }
                    if (ed) {
                      ed.insert(String(text || ""), null, false, true);
                      return true;
                    }
                  } catch (e) {}
                  if (typeof _origInsertimage === "function") {
                    return _origInsertimage.apply(this, arguments);
                  }
                  return false;
                };
              }
            } catch (_) {}
            // --- End: Patch global inserters ---
            // (Custom fallback undo stack был удалён для упрощения и стабильности)
            if (typeof inst.sourceMode === "function" && inst.sourceMode()) {
              if (typeof inst.toggleSourceMode === "function") {
                inst.toggleSourceMode();
              }
            }
            if (typeof inst.focus === "function") {
              inst.focus();
            }
            var bodyEl =
              typeof inst.getBody === "function" ? inst.getBody() : null;
            if (bodyEl) {
              try {
                bodyEl.contentEditable = "true";
              } catch (e3) {}
              if (!bodyEl.innerHTML || /^\s*$/.test(bodyEl.innerHTML)) {
                bodyEl.innerHTML = "\u200B";
                setTimeout(function () {
                  try {
                    if (bodyEl.innerHTML === "\u200B") {
                      bodyEl.innerHTML = "";
                    }
                  } catch (_) {}
                }, 0);
              }
              // Ensure designMode on iframe document
              try {
                var container = inst.getContentAreaContainer();
                var iframe = container
                  ? container.querySelector("iframe")
                  : null;
                var doc =
                  iframe && iframe.contentDocument
                    ? iframe.contentDocument
                    : null;
                if (doc && doc.designMode !== "on") {
                  doc.designMode = "on";
                }
              } catch (_d) {}
              // Fallback: also listen keyup to force synthetic input events (ensures undo snapshots)
              try {
                bodyEl.addEventListener("keyup", function () {
                  try {
                    var ev;
                    try {
                      ev = new InputEvent("input", { bubbles: true });
                    } catch (_) {
                      ev = document.createEvent("Event");
                      ev.initEvent("input", true, true);
                    }
                    bodyEl.dispatchEvent(ev);
                  } catch (_) {}
                });
              } catch (_k) {}
            }
            // Focus editor when clicking toolbar buttons for reliable command exec
            try {
              var container2 = inst.getContentAreaContainer();
              var toolbar = container2
                ? container2.previousElementSibling
                : null;
              if (toolbar && toolbar.classList.contains("sceditor-toolbar")) {
                toolbar.addEventListener(
                  "mousedown",
                  function () {
                    try {
                      inst.focus();
                    } catch (_) {}
                  },
                  true
                );
                // After any toolbar click (except undo/redo), schedule synthetic input to store state
                toolbar.addEventListener(
                  "click",
                  function (e) {
                    try {
                      var t = e.target;
                      while (
                        t &&
                        t !== toolbar &&
                        !(t.dataset && t.dataset.sceditorCommand)
                      ) {
                        t = t.parentElement;
                      }
                      var cmd = t && t.dataset ? t.dataset.sceditorCommand : "";
                      if (cmd === "undo" || cmd === "redo") {
                        return;
                      }
                      setTimeout(function () {
                        try {
                          if (!bodyEl) return;
                          var ev2;
                          try {
                            ev2 = new InputEvent("input", { bubbles: true });
                          } catch (_) {
                            ev2 = document.createEvent("Event");
                            ev2.initEvent("input", true, true);
                          }
                          bodyEl.dispatchEvent(ev2);
                        } catch (_) {}
                      }, 0);
                    } catch (_) {}
                  },
                  true
                );
              }
            } catch (_t) {}
          }
        } catch (_e) {}
        // Remove inline width/height from SCEditor container to let CSS control sizing
        try {
          var prev = ta.previousElementSibling;
          // skip non-element nodes
          while (prev && prev.nodeType !== 1) {
            prev = prev.previousSibling;
          }
          if (
            prev &&
            prev.classList &&
            prev.classList.contains("sceditor-container")
          ) {
            prev.style.width = "";
            prev.style.height = "";
            if (!prev.getAttribute("style")) {
              prev.removeAttribute("style");
            }
          }
        } catch (e2) {
          /* ignore */
        }
        ta.dataset.sceditorInited = "1";
      } catch (e) {
        // Silent fail
      }
    });
  });
})();
