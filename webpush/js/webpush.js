/**
 * Web Push Client-side Script
 * Управление подписками на push-уведомления
 */

(async function () {
  "use strict";

  const btn = document.getElementById("webpush-subscribe-btn");
  if (!btn) return;

  const endpoint =
    btn.dataset.endpoint || "/engine/plugins/webpush/endpoint.php";
  const subscribeText = btn.dataset.subscribeText || "Включить уведомления";
  const unsubscribeText =
    btn.dataset.unsubscribeText || "Отключить уведомления";
  const publicKeyFromButton = btn.dataset.publicKey || "";
  const messageEl = document.getElementById("webpush-message");

  /**
   * Показ сообщения
   */
  function showMessage(text, isError = false) {
    if (!messageEl) return;

    messageEl.textContent = text;
    messageEl.className =
      "webpush-message " + (isError ? "webpush-error" : "webpush-success");
    messageEl.style.display = "block";

    setTimeout(() => {
      messageEl.style.display = "none";
    }, 5000);
  }

  /**
   * Конвертация Base64 в Uint8Array
   */
  function urlBase64ToUint8Array(base64String) {
    const padding = "=".repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding)
      .replace(/-/g, "+")
      .replace(/_/g, "/");
    const rawData = atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; ++i) {
      outputArray[i] = rawData.charCodeAt(i);
    }

    return outputArray;
  }

  /**
   * Получение публичного ключа
   */
  async function getPublicKey() {
    // Сначала пробуем взять из data-атрибута
    if (publicKeyFromButton) {
      console.log("Using public key from button data-attribute");
      return publicKeyFromButton;
    }

    // Если нет - запрашиваем с сервера (fallback)
    try {
      console.log("Fetching public key from server...");
      const response = await fetch(`${endpoint}?action=key`, {
        cache: "no-store",
      });

      const data = await response.json();
      console.log(
        "Public key from server:",
        data.publicKey ? "received" : "empty"
      );
      return data.publicKey || "";
    } catch (error) {
      console.error("Failed to get public key:", error);
      return "";
    }
  }

  /**
   * Регистрация Service Worker
   */
  async function registerServiceWorker() {
    if (!("serviceWorker" in navigator)) {
      showMessage("Ваш браузер не поддерживает push-уведомления", true);
      return null;
    }

    try {
      const registration = await navigator.serviceWorker.register(
        "/webpush-sw.js",
        {
          scope: "/",
        }
      );

      // Ждём активации
      if (registration.installing) {
        await new Promise((resolve) => {
          registration.installing.addEventListener("statechange", function () {
            if (this.state === "activated") resolve();
          });
        });
      }

      return registration;
    } catch (error) {
      console.error("Service Worker registration failed:", error);
      showMessage("Ошибка регистрации Service Worker", true);
      return null;
    }
  }

  /**
   * Проверка статуса подписки
   */
  async function checkSubscription() {
    try {
      const registration = await navigator.serviceWorker.getRegistration("/");
      if (!registration) return false;

      const subscription = await registration.pushManager.getSubscription();
      return !!subscription;
    } catch (error) {
      console.error("Failed to check subscription:", error);
      return false;
    }
  }

  /**
   * Подписка на уведомления
   */
  async function subscribe() {
    try {
      // Проверяем HTTPS
      if (location.protocol !== "https:" && location.hostname !== "localhost") {
        showMessage("Push-уведомления работают только по HTTPS", true);
        return;
      }

      // Регистрируем Service Worker
      const registration = await registerServiceWorker();
      if (!registration) return;

      // Запрашиваем разрешение
      const permission = await Notification.requestPermission();

      if (permission !== "granted") {
        showMessage(
          "Необходимо разрешить уведомления в настройках браузера",
          true
        );
        return;
      }

      // Получаем публичный ключ
      const publicKey = await getPublicKey();

      if (!publicKey) {
        showMessage("Ошибка: публичный ключ не настроен", true);
        console.error("VAPID public key is empty");
        return;
      }

      // Подписываемся
      const subscription = await registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array(publicKey),
      });

      // Отправляем подписку на сервер
      const response = await fetch(`${endpoint}?action=subscribe`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(subscription),
      });

      const result = await response.json();

      if (result.ok) {
        updateButton(true);
        showMessage("Вы подписаны на уведомления");
      } else {
        showMessage(
          "Ошибка подписки: " + (result.error || "неизвестная ошибка"),
          true
        );
      }
    } catch (error) {
      console.error("Subscribe error:", error);
      showMessage("Ошибка при подписке: " + error.message, true);
    }
  }

  /**
   * Отписка от уведомлений
   */
  async function unsubscribe() {
    try {
      const registration = await navigator.serviceWorker.getRegistration("/");
      if (!registration) return;

      const subscription = await registration.pushManager.getSubscription();
      if (!subscription) return;

      // Отправляем отписку на сервер
      await fetch(`${endpoint}?action=unsubscribe`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(subscription),
      });

      // Отписываемся локально
      await subscription.unsubscribe();

      updateButton(false);
      showMessage("Вы отписались от уведомлений");
    } catch (error) {
      console.error("Unsubscribe error:", error);
      showMessage("Ошибка при отписке: " + error.message, true);
    }
  }

  /**
   * Обновление вида кнопки
   */
  function updateButton(isSubscribed) {
    const textEl = btn.querySelector(".webpush-btn-text");

    if (isSubscribed) {
      btn.classList.add("webpush-btn-subscribed");
      if (textEl) textEl.textContent = unsubscribeText;
    } else {
      btn.classList.remove("webpush-btn-subscribed");
      if (textEl) textEl.textContent = subscribeText;
    }
  }

  /**
   * Обработчик клика по кнопке
   */
  btn.addEventListener("click", async () => {
    const isSubscribed = await checkSubscription();

    if (isSubscribed) {
      await unsubscribe();
    } else {
      await subscribe();
    }
  });

  /**
   * Инициализация - проверяем текущий статус
   */
  async function init() {
    const isSubscribed = await checkSubscription();
    updateButton(isSubscribed);
  }

  // Запускаем инициализацию после загрузки страницы
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
