/**
 * Web Push Service Worker
 * Обработка push-уведомлений
 */

"use strict";

/**
 * Обработка входящего push-уведомления
 */
self.addEventListener("push", (event) => {
  let data = {};

  try {
    data = event.data ? event.data.json() : {};
  } catch (error) {
    console.error("Failed to parse push data:", error);
  }

  const title = data.title || "Уведомление";
  const options = {
    body: data.body || "",
    icon: data.icon || undefined,
    badge: data.badge || undefined,
    data: {
      url: data.url || "/",
    },
    requireInteraction: false,
    vibrate: [200, 100, 200],
  };

  event.waitUntil(self.registration.showNotification(title, options));
});

/**
 * Обработка клика по уведомлению
 */
self.addEventListener("notificationclick", (event) => {
  event.notification.close();

  const url = (event.notification.data && event.notification.data.url) || "/";

  event.waitUntil(
    clients
      .matchAll({
        type: "window",
        includeUncontrolled: true,
      })
      .then((clientList) => {
        // Проверяем, есть ли уже открытое окно с этим URL
        for (const client of clientList) {
          if (client.url === url && "focus" in client) {
            return client.focus();
          }
        }

        // Если нет, открываем новое окно
        if (clients.openWindow) {
          return clients.openWindow(url);
        }
      })
  );
});

/**
 * Обработка закрытия уведомления
 */
self.addEventListener("notificationclose", (event) => {
  console.log("Notification closed:", event.notification.tag);
});

/**
 * Активация Service Worker
 */
self.addEventListener("activate", (event) => {
  console.log("Service Worker activated");
  event.waitUntil(clients.claim());
});

/**
 * Установка Service Worker
 */
self.addEventListener("install", (event) => {
  console.log("Service Worker installed");
  self.skipWaiting();
});
