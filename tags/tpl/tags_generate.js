(function () {
  "use strict";

  function notify(message, type) {
    if (window.$ && typeof window.$.notify === "function") {
      window.$.notify({ message: message }, { type: type });
      return;
    }
    if (type === "danger") {
      window.alert(message);
    } else {
      console.info(message);
    }
  }

  function getNewsContent() {
    var ids = [
      "ng_news_content_short",
      "ng_news_content_full",
      "ng_news_content",
    ];
    var parts = [];
    ids.forEach(function (id) {
      var field = document.getElementById(id);
      if (field && field.value.trim()) {
        parts.push(field.value.trim());
      }
    });
    return parts.join("\n");
  }

  function getCategories() {
    var categories = [];
    var primary = document.querySelector("#catmenu option:checked");
    if (primary && primary.value !== "0" && primary.textContent.trim()) {
      categories.push(primary.textContent.trim());
    }
    document
      .querySelectorAll('input[type="checkbox"][name^="category_"]:checked')
      .forEach(function (checkbox) {
        var label = checkbox.closest("label");
        if (label) {
          var name = label.textContent.trim();
          if (name && categories.indexOf(name) === -1) {
            categories.push(name);
          }
        }
      });
    return categories.join(", ");
  }

  window.generateNewsTags = function (button) {
    var form = document.getElementById("postForm");
    var tagsField = document.getElementById("pTags");
    var titleField = form && form.querySelector('[name="title"]');
    var content = getNewsContent();
    if (
      !form ||
      !tagsField ||
      !titleField ||
      !titleField.value.trim() ||
      !content
    ) {
      notify(
        "Заполните заголовок и содержание новости перед генерацией тегов",
        "warning",
      );
      return;
    }

    var status = document.getElementById("tagsGenerateStatus");
    var icon = button.querySelector(".fa");
    var originalIconClass = icon ? icon.className : "";
    button.disabled = true;
    if (icon) {
      icon.className = "fa fa-spinner fa-spin";
    }
    if (status) {
      status.textContent = "Генерация...";
    }

    var params = {
      mode: button.getAttribute("data-tags-mode"),
      news_id: button.getAttribute("data-news-id"),
      token: button.getAttribute("data-news-token"),
      title: titleField.value,
      content: content,
      categories: getCategories(),
    };
    fetch(button.getAttribute("data-tags-rpc"), {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
      },
      body:
        "methodName=" +
        encodeURIComponent("plugin.tags.generate") +
        "&params=" +
        encodeURIComponent(JSON.stringify(params)),
    })
      .then(function (response) {
        if (!response.ok) {
          throw new Error("HTTP " + response.status);
        }
        return response.json();
      })
      .then(function (result) {
        if (!result || result.status !== 1 || !Array.isArray(result.tags)) {
          throw new Error(
            result && result.errorText
              ? result.errorText
              : "Не удалось получить теги",
          );
        }
        var existing = tagsField.value
          .split(",")
          .map(function (tag) {
            return tag.trim();
          })
          .filter(Boolean);
        var seen = Object.create(null);
        existing.forEach(function (tag) {
          seen[tag.toLocaleLowerCase()] = true;
        });
        var addedCount = 0;
        result.tags.forEach(function (tag) {
          var normalized = tag.trim().toLocaleLowerCase();
          if (normalized && !seen[normalized]) {
            existing.push(tag.trim());
            seen[normalized] = true;
            addedCount++;
          }
        });
        tagsField.value = existing.join(", ");
        if (status) {
          status.textContent = "Добавлено тегов: " + addedCount;
        }
      })
      .catch(function (error) {
        if (status) {
          status.textContent = "";
        }
        notify("Ошибка генерации тегов: " + error.message, "danger");
      })
      .finally(function () {
        button.disabled = false;
        if (icon) {
          icon.className = originalIconClass;
        }
      });
  };
})();
