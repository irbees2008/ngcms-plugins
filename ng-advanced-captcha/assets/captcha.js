// Advanced Captcha JavaScript
(function () {
  "use strict";

  // Хранилище взаимодействий пользователя
  const interactions = [];

  // Функция записи взаимодействия
  function recordInteraction(type, data = {}) {
    interactions.push({
      type: type,
      time: Date.now(),
      ...data,
    });
  }

  // Простая капча (math, question)
  window.initSimpleCaptcha = function (formId) {
    const input = document.getElementById("captcha_input_" + formId);
    const answerField = document.getElementById("ng_captcha_answer_" + formId);

    if (!input || !answerField) return;

    // Запись взаимодействий
    input.addEventListener("focus", () => recordInteraction("focus"));
    input.addEventListener("input", () => recordInteraction("input"));
    input.addEventListener("keypress", (e) =>
      recordInteraction("keypress", { key: e.key }),
    );

    // Копируем значение в скрытое поле при отправке
    const form = input.closest("form");
    if (form) {
      form.addEventListener("submit", function (e) {
        answerField.value = input.value.trim();

        // Сохраняем взаимодействия
        const interactionsField = document.getElementById(
          "ng_captcha_interactions_" + formId,
        );
        if (interactionsField) {
          interactionsField.value = JSON.stringify(interactions);
        }
      });
    }
  };

  // Текстовая капча с canvas
  window.initTextCaptcha = function (formId, text) {
    const canvas = document.getElementById("captcha_canvas_" + formId);
    const input = document.getElementById("captcha_input_" + formId);
    const answerField = document.getElementById("ng_captcha_answer_" + formId);

    if (!canvas || !input || !answerField) return;

    // Рисуем искаженный текст
    function drawCaptcha() {
      const ctx = canvas.getContext("2d");
      ctx.clearRect(0, 0, canvas.width, canvas.height);

      // Фон
      ctx.fillStyle = "#ffffff";
      ctx.fillRect(0, 0, canvas.width, canvas.height);

      // Шум
      for (let i = 0; i < 50; i++) {
        ctx.fillStyle = "rgba(0,0,0," + Math.random() * 0.1 + ")";
        ctx.fillRect(Math.random() * 200, Math.random() * 60, 2, 2);
      }

      // Линии помех
      ctx.strokeStyle = "rgba(0,0,0,0.2)";
      ctx.lineWidth = 1;
      for (let i = 0; i < 5; i++) {
        ctx.beginPath();
        ctx.moveTo(Math.random() * 200, Math.random() * 60);
        ctx.lineTo(Math.random() * 200, Math.random() * 60);
        ctx.stroke();
      }

      // Текст
      ctx.font = "bold 32px Arial";
      ctx.textBaseline = "middle";

      const chars = text.split("");
      let x = 20;

      chars.forEach((char, index) => {
        ctx.save();

        // Случайный цвет
        const colors = ["#2c3e50", "#e74c3c", "#3498db", "#2ecc71", "#f39c12"];
        ctx.fillStyle = colors[Math.floor(Math.random() * colors.length)];

        // Случайный поворот
        const angle = (Math.random() - 0.5) * 0.4;
        ctx.translate(x, 30);
        ctx.rotate(angle);

        // Рисуем символ
        ctx.fillText(char, 0, 0);
        ctx.restore();

        x += 25;
      });
    }

    drawCaptcha();

    // Обработка ввода
    input.addEventListener("focus", () => recordInteraction("focus"));
    input.addEventListener("input", () => recordInteraction("input"));

    // Копируем значение при отправке
    const form = input.closest("form");
    if (form) {
      form.addEventListener("submit", function (e) {
        answerField.value = input.value.trim();

        const interactionsField = document.getElementById(
          "ng_captcha_interactions_" + formId,
        );
        if (interactionsField) {
          interactionsField.value = JSON.stringify(interactions);
        }
      });
    }
  };

  // Обновление капчи
  window.refreshCaptcha = function (formId) {
    // Здесь можно сделать AJAX запрос для новой капчи
    location.reload();
  };

  // Checkbox капча
  window.initCheckboxCaptcha = function (formId) {
    const checkbox = document.getElementById("captcha_checkbox_" + formId);
    const answerField = document.getElementById("ng_captcha_answer_" + formId);
    const tokenField = document.getElementById("ng_captcha_token_" + formId);
    const interactionsField = document.getElementById(
      "ng_captcha_interactions_" + formId,
    );

    if (!checkbox || !answerField || !tokenField) return;

    let startTime = Date.now();

    // Отслеживание движений мыши
    checkbox.parentElement.addEventListener("mousemove", function (e) {
      recordInteraction("mousemove", {
        x: e.clientX,
        y: e.clientY,
      });
    });

    checkbox.addEventListener("mouseenter", () =>
      recordInteraction("mouseenter"),
    );
    checkbox.addEventListener("mouseleave", () =>
      recordInteraction("mouseleave"),
    );

    checkbox.addEventListener("change", function () {
      if (this.checked) {
        const duration = Date.now() - startTime;
        recordInteraction("check", { duration: duration });

        // Генерируем токен на основе времени и взаимодействий
        const token = generateToken(formId, duration);
        tokenField.value = token;
        answerField.value = "checked";
        interactionsField.value = JSON.stringify(interactions);
      } else {
        answerField.value = "";
        tokenField.value = "";
      }
    });

    // Проверка при отправке формы
    const form = checkbox.closest("form");
    if (form) {
      form.addEventListener("submit", function (e) {
        if (!checkbox.checked) {
          e.preventDefault();
          alert("Пожалуйста, подтвердите, что вы не робот");
          return false;
        }
      });
    }
  };

  // Slider капча
  window.initSliderCaptcha = function (formId) {
    const slider = document.getElementById("captcha_slider_" + formId);
    const track = slider.parentElement;
    const feedback = document.getElementById("captcha_feedback_" + formId);
    const answerField = document.getElementById("ng_captcha_answer_" + formId);
    const tokenField = document.getElementById("ng_captcha_token_" + formId);
    const interactionsField = document.getElementById(
      "ng_captcha_interactions_" + formId,
    );

    if (!slider || !track || !feedback) return;

    let isDragging = false;
    let startX = 0;
    let startTime = Date.now();
    let currentX = 0;

    function startDrag(e) {
      isDragging = true;
      startX = e.type === "mousedown" ? e.clientX : e.touches[0].clientX;
      slider.style.transition = "none";
      recordInteraction("dragstart", { x: startX });
    }

    function drag(e) {
      if (!isDragging) return;

      e.preventDefault();
      const clientX = e.type === "mousemove" ? e.clientX : e.touches[0].clientX;
      const diff = clientX - startX;
      const maxMove = track.offsetWidth - slider.offsetWidth;

      currentX = Math.max(0, Math.min(diff, maxMove));
      slider.style.left = currentX + "px";

      recordInteraction("drag", { x: clientX, position: currentX });

      // Обратная связь
      const progress = (currentX / maxMove) * 100;
      if (progress > 95) {
        feedback.textContent = "✓ Готово!";
        feedback.className = "captcha-slider-feedback success";
      } else {
        feedback.textContent = Math.floor(progress) + "%";
        feedback.className = "captcha-slider-feedback";
      }
    }

    function endDrag() {
      if (!isDragging) return;
      isDragging = false;

      const maxMove = track.offsetWidth - slider.offsetWidth;
      const progress = (currentX / maxMove) * 100;
      const duration = Date.now() - startTime;

      recordInteraction("dragend", {
        position: currentX,
        progress: progress,
        duration: duration,
      });

      if (progress > 95) {
        // Успешно
        slider.style.left = maxMove + "px";
        feedback.textContent = "✓ Проверка пройдена!";
        feedback.className = "captcha-slider-feedback success";

        const token = generateToken(formId, duration);
        tokenField.value = token;
        answerField.value = "completed";
        interactionsField.value = JSON.stringify(interactions);
      } else {
        // Вернуть назад
        slider.style.transition = "left 0.3s";
        slider.style.left = "0px";
        feedback.textContent = "Попробуйте еще раз";
        feedback.className = "captcha-slider-feedback error";

        setTimeout(() => {
          feedback.textContent = "";
        }, 2000);
      }
    }

    // События мыши
    slider.addEventListener("mousedown", startDrag);
    document.addEventListener("mousemove", drag);
    document.addEventListener("mouseup", endDrag);

    // События касания
    slider.addEventListener("touchstart", startDrag);
    document.addEventListener("touchmove", drag);
    document.addEventListener("touchend", endDrag);

    // Проверка при отправке формы
    const form = slider.closest("form");
    if (form) {
      form.addEventListener("submit", function (e) {
        if (!answerField.value) {
          e.preventDefault();
          alert("Пожалуйста, переместите ползунок до конца");
          return false;
        }
      });
    }
  };

  // Генерация токена
  function generateToken(formId, duration) {
    // Простой токен на основе времени и взаимодействий
    const data = formId + duration + interactions.length + Date.now();
    return simpleHash(data);
  }

  // Простая хэш-функция
  function simpleHash(str) {
    let hash = 0;
    for (let i = 0; i < str.length; i++) {
      const char = str.charCodeAt(i);
      hash = (hash << 5) - hash + char;
      hash = hash & hash;
    }
    return Math.abs(hash).toString(36);
  }
})();
