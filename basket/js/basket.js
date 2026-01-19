/**
 * Отправка RPC запроса для управления корзиной
 * @param {string} method - Название метода RPC
 * @param {object} params - Параметры запроса
 */
function rpcBasketRequest(method, params) {
  var linkTX = new sack();
  linkTX.requestFile = "/engine/rpc.php";
  linkTX.setVar("json", "1");
  linkTX.setVar("methodName", method);
  linkTX.setVar("params", json_encode(params));
  linkTX.method = "POST";

  linkTX.onComplete = function () {
    linkTX.onHide();

    if (linkTX.responseStatus[0] == 200) {
      var resTX;
      try {
        resTX = eval("(" + linkTX.response + ")");
      } catch (err) {
        if (typeof notify === "function") {
          notify("error", "Ошибка парсинга JSON: " + err.message);
        } else {
          alert("Ошибка парсинга JSON: " + err.message);
        }
        return;
      }

      // Проверка статуса ответа
      if (!resTX["status"]) {
        // Ошибка
        var errorMsg =
          "Ошибка (" + resTX["errorCode"] + "): " + resTX["errorText"];
        if (typeof notify === "function") {
          notify("error", errorMsg);
        } else {
          alert(errorMsg);
        }
      } else {
        // Успех
        if (typeof notify === "function") {
          notify("success", "Товар добавлен в корзину");
        }

        // Обновляем счетчик корзины
        var basketDisplay = document.getElementById("basketTotalDisplay");
        if (basketDisplay && resTX["update"]) {
          basketDisplay.innerHTML = resTX["update"];
        }

        // Обновляем состояние кнопки, если она есть
        var basketBtn = document.getElementById("basket_" + params["id"]);
        if (basketBtn) {
          basketBtn.value = "1";
        }
      }
    } else {
      var httpError = "HTTP ошибка: " + linkTX.responseStatus[0];
      if (typeof notify === "function") {
        notify("error", httpError);
      } else {
        alert(httpError);
      }
    }
  };

  linkTX.onShow();
  linkTX.runAJAX();
}
