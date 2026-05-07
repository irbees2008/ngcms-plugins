// ============================
// Комментарии: AJAX добавление / правка / удаление
// Конфигурация передаётся через window.CommentsConfig из шаблона
// ============================

// Унифицированный вывод уведомлений
function notify(type, msg) {
  var text = String(msg);
  if (window.showToast) {
    var map = {
      error: "error",
      success: "success",
      info: "info",
      warning: "warning",
    };
    showToast(text.replace(/</g, "&lt;"), {
      type: map[type] || "info",
      title: type === "error" ? "Ошибка" : "Комментарий",
    });
    return;
  }
  if (type === "error" && typeof window.show_error === "function") {
    show_error(text);
    return;
  }
  if (typeof window.show_info === "function") {
    show_info(text);
    return;
  }
  (type === "error" ? alert : console.log)(text);
}

// Безопасный парсинг JSON (учёт BOM)
function parseJSONSafe(src) {
  if (src == null) return null;
  try {
    return JSON.parse(src);
  } catch (e1) {
    try {
      return JSON.parse(String(src).replace(/^\uFEFF/, ""));
    } catch (e2) {
      return null;
    }
  }
}

var cajax = new sack();

// Перезагрузка капчи
function reload_captcha() {
  var captc = document.getElementById("img_captcha");
  if (captc) {
    var cfg = window.CommentsConfig || {};
    captc.src = (cfg.captcha_url || "") + "?rand=" + Date.now();
  }
}

// Добавление комментария (AJAX)
function add_comment() {
  var form = document.getElementById("comment");
  if (!form) return false;

  var cfg = window.CommentsConfig || {};

  // Перед отправкой: копируем значение капчи из видимого поля в скрытое
  var captchaContainer = form.querySelector(".ng-advanced-captcha");
  if (captchaContainer) {
    var formId = captchaContainer.getAttribute("data-form-id");
    var captchaInput = document.getElementById("captcha_input_" + formId);
    var answerField = document.getElementById("ng_captcha_answer_" + formId);
    if (captchaInput && answerField) {
      answerField.value = captchaInput.value.trim();
    }
  }

  cajax.onShow("");
  // Для Twig-шаблонов используем cfg.not_logged; для старых шаблонов — проверяем наличие полей в форме
  var sendUserData =
    typeof cfg.not_logged !== "undefined"
      ? !!cfg.not_logged
      : !!(form.elements["name"] && form.elements["mail"]);
  if (sendUserData) {
    cajax.setVar("name", form.name.value);
    cajax.setVar("mail", form.mail.value);
    if (cfg.use_captcha) {
      cajax.setVar("vcode", form.vcode ? form.vcode.value : "");
    }
  }
  cajax.setVar("content", form.content.value);
  cajax.setVar("newsid", form.newsid.value);
  cajax.setVar("module", form.module ? form.module.value : "");
  cajax.setVar("ajax", "1");
  cajax.setVar("json", "1");

  // Добавляем поля ng-advanced-captcha, если они есть
  if (form.ng_captcha_form_id)
    cajax.setVar("ng_captcha_form_id", form.ng_captcha_form_id.value);
  if (form.ng_captcha_answer)
    cajax.setVar("ng_captcha_answer", form.ng_captcha_answer.value);
  if (form.ng_captcha_token)
    cajax.setVar("ng_captcha_token", form.ng_captcha_token.value);
  if (form.ng_captcha_interactions)
    cajax.setVar("ng_captcha_interactions", form.ng_captcha_interactions.value);
  if (form.website) cajax.setVar("website", form.website.value);

  cajax.requestFile = cfg.post_url || "";
  cajax.method = "POST";
  cajax.onComplete = function () {
    try {
      if (cajax.responseStatus[0] != 200) {
        notify("error", "HTTP error: " + cajax.responseStatus[0]);
        return;
      }
      var res = parseJSONSafe(cajax.response);
      if (!res) {
        notify("error", "Ошибка обработки ответа");
        return;
      }
      var nc =
        res.rev && document.getElementById("new_comments_rev")
          ? document.getElementById("new_comments_rev")
          : document.getElementById("new_comments");
      if (res.status) {
        if (res.data) {
          nc.innerHTML += res.data;
        }
        form.content.value = "";
        reload_ng_advanced_captcha(form);
        if (cfg.not_logged && cfg.use_moderation) {
          notify(
            "info",
            "Комментарий отправлен на модерацию и будет опубликован после проверки.",
          );
        } else {
          notify("success", "Комментарий добавлен");
        }
      } else {
        notify("error", res.data || "Ошибка при добавлении комментария");
      }
    } catch (ex) {
      notify("error", "Исключение: " + ex);
    } finally {
      if (cfg.use_captcha) {
        reload_captcha();
      }
    }
  };
  cajax.runAJAX();
  return false;
}

// Обновление ng-advanced-captcha
function reload_ng_advanced_captcha(form) {
  var captchaContainer = form.querySelector(".ng-advanced-captcha");
  if (!captchaContainer) return;
  var formId = captchaContainer.getAttribute("data-form-id");
  if (!formId) return;
  var xhr = new XMLHttpRequest();
  xhr.open(
    "GET",
    "?ng_captcha_generate&form_id=" + encodeURIComponent(formId),
    true,
  );
  xhr.onload = function () {
    if (xhr.status === 200) {
      try {
        var response = JSON.parse(xhr.responseText);
        if (response.status === "success" && response.html) {
          captchaContainer.outerHTML = response.html;
        }
      } catch (e) {
        console.error("Ошибка обновления капчи:", e);
      }
    }
  };
  xhr.send();
}

// Цитирование
function quote(author) {
  var textarea = document.getElementById("content");
  if (textarea) {
    var quoteText = "[quote]" + author + ", [/quote]\n";
    textarea.value += quoteText;
    textarea.focus();
    if (textarea.setSelectionRange) {
      var pos = textarea.value.length;
      textarea.setSelectionRange(pos, pos);
    }
  }
  var form = document.getElementById("comment");
  if (form) {
    form.scrollIntoView({ behavior: "smooth" });
  }
}

// ============================
// Удаление / редактирование комментариев
// ============================

var original_comment_content = {};

// Удаление комментария
function delete_comment(comment_id, token) {
  if (!confirm("Удалить комментарий?")) return false;
  var cfg = window.CommentsConfig || {};
  var dajax = new sack();
  dajax.setVar("id", comment_id);
  dajax.setVar("uT", token);
  dajax.setVar("ajax", "1");
  dajax.requestFile = cfg.delete_url || "";
  dajax.method = "GET";
  dajax.onComplete = function () {
    if (dajax.responseStatus[0] == 200) {
      var result = null;
      try {
        result = JSON.parse(dajax.response);
      } catch (e) {
        if (typeof show_error === "function")
          show_error("Ошибка обработки ответа: " + dajax.response);
        return;
      }
      if (result && result.status) {
        var el = document.getElementById("comment" + comment_id);
        if (el) {
          el.style.display = "none";
        }
        if (result.data) {
          if (result.data.indexOf("<") !== -1) {
            document.body.insertAdjacentHTML("beforeend", result.data);
          } else {
            notify("success", result.data);
          }
        } else {
          notify("success", "Комментарий удалён");
        }
      } else if (result && result.data) {
        if (result.data.indexOf("<") !== -1) {
          document.body.insertAdjacentHTML("beforeend", result.data);
        } else {
          notify("error", result.data);
        }
      }
    } else {
      if (typeof show_error === "function")
        show_error("HTTP error. Code: " + dajax.responseStatus[0]);
    }
  };
  dajax.runAJAX();
}

// Редактирование комментария
function edit_comment(comment_id) {
  var comment_text_div = document.getElementById("comment_text_" + comment_id);
  if (!comment_text_div) return;
  original_comment_content[comment_id] = comment_text_div.innerHTML;
  var cfg = window.CommentsConfig || {};
  var eajax = new sack();
  eajax.setVar("id", comment_id);
  eajax.setVar("action", "get");
  eajax.setVar("ajax", "1");
  eajax.requestFile = cfg.edit_url || "";
  eajax.method = "GET";
  eajax.onComplete = function () {
    if (eajax.responseStatus[0] == 200) {
      try {
        var result = parseJSONSafe(eajax.response);
        if (!result) {
          if (typeof show_error === "function")
            show_error("Ошибка обработки ответа: " + eajax.response);
          return;
        }
        if (result["status"] == 1) {
          var edit_form =
            '<textarea id="edit_textarea_' +
            comment_id +
            '" style="width:100%; height:100px;">' +
            result["text"] +
            "</textarea><br/>" +
            '<button onclick="save_comment(' +
            comment_id +
            '); return false;">Сохранить</button> ' +
            '<button onclick="cancel_edit(' +
            comment_id +
            '); return false;">Отмена</button>';
          comment_text_div.innerHTML = edit_form;
        } else if (result["data"]) {
          if (result["data"].indexOf("<") !== -1) {
            document.body.insertAdjacentHTML("beforeend", result["data"]);
          } else {
            notify("error", result["data"]);
          }
        }
      } catch (err) {
        if (typeof show_error === "function")
          show_error("Ошибка обработки ответа: " + eajax.response);
      }
    }
  };
  eajax.runAJAX();
}

// Сохранение отредактированного комментария
function save_comment(comment_id) {
  var textarea = document.getElementById("edit_textarea_" + comment_id);
  if (!textarea) return;
  var cfg = window.CommentsConfig || {};
  var sajax = new sack();
  sajax.setVar("id", comment_id);
  sajax.setVar("text", textarea.value);
  sajax.setVar("action", "save");
  sajax.setVar("ajax", "1");
  sajax.requestFile = cfg.edit_url || "";
  sajax.method = "POST";
  sajax.onComplete = function () {
    if (sajax.responseStatus[0] == 200) {
      try {
        var result = parseJSONSafe(sajax.response);
        if (!result) {
          if (typeof show_error === "function")
            show_error("Ошибка обработки ответа: " + sajax.response);
          return;
        }
        if (result["status"] == 1) {
          var comment_text_div = document.getElementById(
            "comment_text_" + comment_id,
          );
          comment_text_div.innerHTML = result["html"];
          if (result["data"]) {
            if (result["data"].indexOf("<") !== -1) {
              document.body.insertAdjacentHTML("beforeend", result["data"]);
            } else {
              notify("success", result["data"]);
            }
          } else {
            notify("success", "Комментарий обновлён");
          }
        } else if (result["data"]) {
          if (result["data"].indexOf("<") !== -1) {
            document.body.insertAdjacentHTML("beforeend", result["data"]);
          } else {
            notify("error", result["data"]);
          }
        }
      } catch (err) {
        if (typeof show_error === "function")
          show_error("Ошибка обработки ответа: " + sajax.response);
      }
    }
  };
  sajax.runAJAX();
}

// Отмена редактирования
function cancel_edit(comment_id) {
  var comment_text_div = document.getElementById("comment_text_" + comment_id);
  if (comment_text_div && original_comment_content[comment_id]) {
    comment_text_div.innerHTML = original_comment_content[comment_id];
    delete original_comment_content[comment_id];
  }
}
