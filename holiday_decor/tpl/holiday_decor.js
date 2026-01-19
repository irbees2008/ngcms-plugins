(function () {
  function el(tag, attrs) {
    var e = document.createElement(tag);
    if (attrs) {
      for (var k in attrs) {
        if (k === "class") e.className = attrs[k];
        else e.setAttribute(k, attrs[k]);
      }
    }
    return e;
  }
  function init() {
    var cfg = window.holidayDecorConfig || {
      garland: true,
      mode: "sprite",
      snow: true,
      snowCount: 150,
      snowSpeed: 1.5,
      showSwitch: true,
      fireworks: false,
      cursorSnow: false,
      jqSnowfall: { enabled: false, flakeCount: 400 },
      bigSnow: false,
      fallingStars: false,
      countdownSanta: false,
      countdownBanner: false,
    };
    // Sprite mode: insert #gir and animate frames via class switching
    if (cfg.garland && cfg.mode === "sprite") {
      var gir = document.getElementById("gir");
      if (!gir) {
        gir = el("div", { id: "gir", class: "gir_1" });
        document.body.appendChild(gir);
      }
      function girTick() {
        var n = document.getElementById("gir");
        if (!n) return;
        var c = n.className;
        if (c === "gir_1") {
          n.className = "gir_2";
        } else if (c === "gir_2") {
          n.className = "gir_3";
        } else {
          n.className = "gir_1";
        }
      }
      setInterval(girTick, 500);
    }
    // Modern mode: dynamic bulbs row
    if (cfg.garland && cfg.mode === "modern") {
      var garland = el("div", { id: "garland", class: "garland-container" });
      document.body.appendChild(garland);
      createGarland(garland);
    }
    // Lightrope mode: build UL with LI bulbs
    if (cfg.garland && cfg.mode === "lightrope") {
      var rope = document.querySelector("ul.lightrope");
      if (!rope) {
        rope = document.createElement("ul");
        rope.className = "lightrope";
        // create 42 bulbs
        for (var i = 0; i < 42; i++) {
          rope.appendChild(document.createElement("li"));
        }
        document.body.appendChild(rope);
      }
    }
    var switchContainer;
    if (cfg.showSwitch && cfg.garland && cfg.mode === "modern") {
      switchContainer = el("div", {
        id: "switchContainer",
        class: "switch-container",
      });
      var cord = el("div", { class: "cord" });
      var sw = el("div", { id: "switch", class: "switch" });
      sw.textContent = "🔆";
      var label = el("div");
      label.style.marginTop = "10px";
      label.style.fontSize = "14px";
      label.textContent = "Гирлянда";
      switchContainer.appendChild(cord);
      switchContainer.appendChild(sw);
      switchContainer.appendChild(label);
      document.body.appendChild(switchContainer);
    }
    var snowEnabled = !!cfg.snow;
    var snowContainer;
    if (cfg.snow) {
      snowContainer = el("div", { id: "snow-container" });
      document.body.appendChild(snowContainer);
    }
    var snowflakes = [],
      snowSpeed = cfg.snowSpeed || 1.5,
      animationFrameId = null,
      garlandEnabled = true;
    function createGarland(garland) {
      var colors = [
        "#ff0000",
        "#00ff00",
        "#0000ff",
        "#ffff00",
        "#ff00ff",
        "#00ffff",
      ];
      for (var i = 0; i < 25; i++) {
        var light = el("div", { class: "light" });
        var color = colors[Math.floor(Math.random() * colors.length)];
        light.style.backgroundColor = color;
        light.style.animationDelay = Math.random() * 1 + "s";
        light.style.animationDuration = 0.5 + Math.random() * 1 + "s";
        garland.appendChild(light);
      }
    }
    function toggleGarland() {
      garlandEnabled = !garlandEnabled;
      var lights = document.querySelectorAll(".light");
      for (var i = 0; i < lights.length; i++) {
        var light = lights[i];
        light.style.animationPlayState = garlandEnabled ? "running" : "paused";
        light.style.opacity = garlandEnabled ? "1" : "0.1";
      }
      var s = document.getElementById("switch");
      if (s) {
        s.textContent = garlandEnabled ? "🔆" : "🌙";
        s.classList.toggle("on", garlandEnabled);
      }
    }
    function createSnowflakes(count) {
      if (!snowContainer) return;
      snowContainer.innerHTML = "";
      snowflakes = [];
      count = Math.max(0, Math.min(1000, parseInt(count || 150, 10)));
      for (var i = 0; i < count; i++) {
        var snowflake = el("div", { class: "snowflake" });
        var size = Math.random() * 4 + 2;
        snowflake.style.width = size + "px";
        snowflake.style.height = size + "px";
        var startX = Math.random() * 100;
        var startY = Math.random() * -100;
        snowflake.style.left = startX + "%";
        snowflake.style.top = startY + "px";
        snowflake.style.opacity = (Math.random() * 0.5 + 0.5).toFixed(2);
        snowContainer.appendChild(snowflake);
        snowflakes.push({
          element: snowflake,
          x: startX,
          y: startY,
          speed: (Math.random() * 0.5 + 0.5) * snowSpeed,
          sway: Math.random() * 0.5 - 0.25,
          size: size,
        });
      }
    }
    function animateSnow() {
      if (!snowEnabled || !snowContainer) return;
      for (var i = 0; i < snowflakes.length; i++) {
        var s = snowflakes[i];
        s.y += s.speed;
        s.x += Math.sin(s.y * 0.01) * s.sway;
        if (s.y > window.innerHeight) {
          s.y = -s.size;
          s.x = Math.random() * 100;
        }
        s.element.style.transform = "translate(" + s.x + "vw, " + s.y + "px)";
      }
      animationFrameId = requestAnimationFrame(animateSnow);
    }
    function toggleSnow() {
      snowEnabled = !snowEnabled;
      if (snowEnabled) {
        if (!animationFrameId) {
          animateSnow();
        }
        if (snowContainer) {
          snowContainer.style.opacity = "1";
        }
      } else {
        if (animationFrameId) {
          cancelAnimationFrame(animationFrameId);
          animationFrameId = null;
        }
        if (snowContainer) {
          snowContainer.style.opacity = "0";
        }
      }
    }
    function changeSnowSpeed(speed) {
      snowSpeed = parseFloat(speed || 1.5);
      for (var i = 0; i < snowflakes.length; i++) {
        var s = snowflakes[i];
        s.speed = (Math.random() * 0.5 + 0.5) * snowSpeed;
      }
    }
    if (cfg.snow) {
      createSnowflakes(cfg.snowCount);
      animateSnow();
      var sd1 = el("div", { class: "snowdrift" }),
        sd2 = el("div", { class: "snowdrift top" });
      document.body.appendChild(sd1);
      document.body.appendChild(sd2);
    }
    if (switchContainer) {
      switchContainer.addEventListener("click", toggleGarland);
    }
    // ===== Fireworks (ASCII style) =====
    if (cfg.fireworks) {
      (function () {
        var bits = 80,
          speed = 33,
          bangs = 5,
          colours = ["#03f", "#f03", "#0e0", "93f", "#0cf", "#f93", "#f0c"],
          bangheight = [],
          intensity = [],
          colour = [],
          Xpos = [],
          Ypos = [],
          dX = [],
          dY = [],
          stars = [],
          decay = [],
          swide = 800,
          shigh = 600,
          boddie;
        function createDiv(content, fontSize) {
          var d = document.createElement("div");
          d.style.font = fontSize + "px monospace";
          d.style.position = "fixed";
          d.style.backgroundColor = "transparent";
          d.style.zIndex = 10002;
          d.textContent = content;
          return d;
        }
        function write_fire(i) {
          stars[i + "r"] = createDiv("|", 12);
          document.body.appendChild(stars[i + "r"]);
          for (var k = bits * i; k < bits + bits * i; k++) {
            stars[k] = createDiv("*", 13);
            document.body.appendChild(stars[k]);
          }
        }
        function launch(i) {
          colour[i] = Math.floor(Math.random() * colours.length);
          Xpos[i + "r"] = window.innerWidth * 0.5;
          Ypos[i + "r"] = window.innerHeight - 5;
          bangheight[i] = Math.round(
            (0.5 + Math.random()) * window.innerHeight * 0.4
          );
          dX[i + "r"] =
            ((Math.random() - 0.5) * window.innerWidth) / bangheight[i];
          var s = stars[i + "r"];
          s.style.color = colours[colour[i]];
        }
        function bang(i) {
          var hidden = 0;
          for (var k = bits * i; k < bits + bits * i; k++) {
            var s = stars[k];
            s.style.left = Xpos[k] + "px";
            s.style.top = Ypos[k] + "px";
            if (decay[k]) {
              decay[k]--;
            } else {
              hidden++;
            }
            if (decay[k] === 15) {
              s.style.fontSize = "7px";
            } else if (decay[k] === 7) {
              s.style.fontSize = "2px";
            } else if (decay[k] === 1) {
              s.style.visibility = "hidden";
            }
            Xpos[k] += dX[k];
            Ypos[k] += dY[k] += 1.25 / intensity[i];
          }
          if (hidden !== bits) {
            setTimeout(function () {
              bang(i);
            }, speed);
          }
        }
        function stepthrough(i) {
          var ox = Xpos[i + "r"],
            oy = Ypos[i + "r"];
          Xpos[i + "r"] += dX[i + "r"];
          Ypos[i + "r"] -= 4;
          if (Ypos[i + "r"] < bangheight[i]) {
            var ci = Math.floor(Math.random() * 3 * colours.length);
            intensity[i] = 5 + Math.random() * 4;
            for (var k = bits * i; k < bits + bits * i; k++) {
              Xpos[k] = Xpos[i + "r"];
              Ypos[k] = Ypos[i + "r"];
              dY[k] = (Math.random() - 0.5) * intensity[i];
              dX[k] =
                (Math.random() - 0.5) * (intensity[i] - Math.abs(dY[k])) * 1.25;
              decay[k] = 16 + Math.floor(Math.random() * 16);
              var s = stars[k];
              s.style.color = colours[k % colours.length];
              s.style.fontSize = "13px";
              s.style.visibility = "visible";
            }
            bang(i);
            launch(i);
          }
          stars[i + "r"].style.left = ox + "px";
          stars[i + "r"].style.top = oy + "px";
        }
        function initF() {
          for (var i = 0; i < bangs; i++) {
            write_fire(i);
            launch(i);
            setInterval(
              (function (ii) {
                return function () {
                  stepthrough(ii);
                };
              })(i),
              speed
            );
          }
        }
        window.addEventListener("load", initF);
      })();
    }
    // ===== Cursor snow (trails) =====
    if (cfg.cursorSnow) {
      (function () {
        var colour = "blue",
          sparkles = 100,
          x = 400,
          ox = 400,
          y = 300,
          oy = 300,
          swide = 800,
          shigh = 600,
          sleft = 0,
          sdown = 0,
          tiny = [],
          star = [],
          starv = [],
          starx = [],
          stary = [],
          tinyx = [],
          tinyy = [],
          tinyv = [];
        function cd(h, w) {
          var d = document.createElement("div");
          d.style.position = "absolute";
          d.style.height = h + "px";
          d.style.width = w + "px";
          d.style.overflow = "hidden";
          d.style.backgroundColor = colour;
          d.style.zIndex = 10002;
          return d;
        }
        function set_w() {
          swide = window.innerWidth;
          shigh = window.innerHeight;
        }
        function set_s() {
          sdown =
            window.pageYOffset ||
            document.documentElement.scrollTop ||
            document.body.scrollTop ||
            0;
          sleft =
            window.pageXOffset ||
            document.documentElement.scrollLeft ||
            document.body.scrollLeft ||
            0;
        }
        function mouse(e) {
          set_s();
          y = e ? e.pageY : event.y + sdown;
          x = e ? e.pageX : event.x + sleft;
        }
        function updS(i) {
          if (--starv[i] == 25) {
            star[i].style.clip = "rect(1px,4px,4px,1px)";
          }
          if (starv[i]) {
            stary[i] += 1 + Math.random() * 3;
            if (stary[i] < shigh + sdown) {
              star[i].style.top = stary[i] + "px";
              starx[i] += ((i % 5) - 2) / 5;
              star[i].style.left = starx[i] + "px";
            } else {
              star[i].style.visibility = "hidden";
              starv[i] = 0;
              return;
            }
          } else {
            tinyv[i] = 50;
            tiny[i].style.top = (tinyy[i] = stary[i]) + "px";
            tiny[i].style.left = (tinyx[i] = starx[i]) + "px";
            tiny[i].style.width = "2px";
            tiny[i].style.height = "2px";
            star[i].style.visibility = "hidden";
            tiny[i].style.visibility = "visible";
          }
        }
        function updT(i) {
          if (--tinyv[i] == 25) {
            tiny[i].style.width = "1px";
            tiny[i].style.height = "1px";
          }
          if (tinyv[i]) {
            tinyy[i] += 1 + Math.random() * 3;
            if (tinyy[i] < shigh + sdown) {
              tiny[i].style.top = tinyy[i] + "px";
              tinyx[i] += ((i % 5) - 2) / 5;
              tiny[i].style.left = tinyx[i] + "px";
            } else {
              tiny[i].style.visibility = "hidden";
              tinyv[i] = 0;
              return;
            }
          } else {
            tiny[i].style.visibility = "hidden";
          }
        }
        function sparkle() {
          if (x != ox || y != oy) {
            ox = x;
            oy = y;
            for (var c = 0; c < sparkles; c++) {
              if (!starv[c]) {
                star[c].style.left = (starx[c] = x) + "px";
                star[c].style.top = (stary[c] = y) + "px";
                star[c].style.clip = "rect(0px,5px,5px,0px)";
                star[c].style.visibility = "visible";
                starv[c] = 50;
                break;
              }
            }
          }
          for (var c = 0; c < sparkles; c++) {
            if (starv[c]) updS(c);
            if (tinyv[c]) updT(c);
          }
          requestAnimationFrame(sparkle);
        }
        function initCS() {
          for (var i = 0; i < sparkles; i++) {
            var t = cd(3, 3);
            t.style.visibility = "hidden";
            document.body.appendChild((tiny[i] = t));
            starv[i] = 0;
            tinyv[i] = 0;
            var s = cd(5, 5);
            s.style.backgroundColor = "transparent";
            s.style.visibility = "hidden";
            var rl = cd(1, 5),
              rd = cd(5, 1);
            s.appendChild(rl);
            s.appendChild(rd);
            rl.style.top = "3px";
            rl.style.left = "0px";
            rd.style.top = "0px";
            rd.style.left = "3px";
            document.body.appendChild((star[i] = s));
          }
          set_w();
          sparkle();
        }
        window.addEventListener("load", initCS);
        document.addEventListener("mousemove", mouse);
        window.addEventListener("resize", set_w);
      })();
    }
    // ===== Big snowflakes (large ❄ symbols) =====
    if (cfg.bigSnow) {
      (function () {
        var snowmax = 35,
          snowcolor = [
            "#AAAACC",
            "#DDDDFF",
            "#CCCCDD",
            "#F3F3F3",
            "#F0FFFF",
            "#FFFFFF",
            "#EFF5FF",
          ],
          snowtype = ["Arial Black", "Arial Narrow", "Times", "Comic Sans MS"],
          snowletter = "❄",
          sinkspeed = 0.6,
          snowmaxsize = 40,
          snowminsize = 8,
          snowingzone = 1,
          snow = [],
          marginbottom,
          marginright,
          timer,
          x_mv = [],
          crds = [],
          lftrght = [];
        function rand(r) {
          return Math.floor(r * Math.random());
        }
        function inits() {
          marginbottom = window.innerHeight;
          marginright = window.innerWidth;
          var range = snowmaxsize - snowminsize;
          var cont = document.createElement("div");
          cont.style.position = "fixed";
          cont.style.top = "0";
          cont.style.left = "0";
          cont.style.zIndex = 10001;
          document.body.appendChild(cont);
          for (var i = 0; i <= snowmax; i++) {
            crds[i] = 0;
            lftrght[i] = Math.random() * 15;
            x_mv[i] = 0.03 + Math.random() / 10;
            snow[i] = document.createElement("span");
            snow[i].id = "s" + i;
            snow[i].style.position = "absolute";
            snow[i].style.top = -snowmaxsize + "px";
            cont.appendChild(snow[i]);
            snow[i].style.fontFamily = snowtype[rand(snowtype.length)];
            snow[i].size = rand(range) + snowminsize;
            snow[i].style.fontSize = snow[i].size + "px";
            snow[i].style.color = snowcolor[rand(snowcolor.length)];
            snow[i].sink = (sinkspeed * snow[i].size) / 5;
            snow[i].posx = rand(marginright - snow[i].size);
            snow[i].posy = rand(
              2 * marginbottom - marginbottom - 2 * snow[i].size
            );
            snow[i].style.left = snow[i].posx + "px";
            snow[i].style.top = snow[i].posy + "px";
            snow[i].textContent = snowletter;
          }
          moves();
        }
        function moves() {
          for (var i = 0; i <= snowmax; i++) {
            crds[i] += x_mv[i];
            snow[i].posy += snow[i].sink;
            snow[i].style.left =
              snow[i].posx + lftrght[i] * Math.sin(crds[i]) + "px";
            snow[i].style.top = snow[i].posy + "px";
            if (snow[i].posy >= marginbottom - 2 * snow[i].size) {
              snow[i].posx = Math.floor(
                Math.random() * (marginright - snow[i].size)
              );
              snow[i].posy = 0;
            }
          }
          timer = setTimeout(moves, 50);
        }
        window.addEventListener("load", inits);
      })();
    }
    // ===== Falling stars (top layer) =====
    if (cfg.fallingStars) {
      (function () {
        var style = document.createElement("style");
        style.textContent =
          "body{margin:0} .star{position:fixed;width:40px;height:40px;background:url(https://mycrib.ru/wp-content/uploads/2025/01/coloredstars.gif) no-repeat center/cover;z-index:10000;pointer-events:none}";
        document.head.appendChild(style);
        var no = 15,
          stars = [],
          doc_width = innerWidth,
          doc_height = innerHeight;
        for (var i = 0; i < no; i++) {
          var st = document.createElement("div");
          st.className = "star";
          document.body.appendChild(st);
          stars.push({
            element: st,
            x: Math.random() * doc_width,
            y: Math.random() * doc_height,
            amplitude: Math.random() * 20,
            stepX: 0.01 + Math.random() / 20,
            stepY: 0.3 + Math.random() / 2,
          });
        }
        function anim() {
          doc_width = innerWidth;
          doc_height = document.body.scrollHeight;
          stars.forEach(function (s) {
            s.y += s.stepY;
            if (s.y > doc_height - 50) {
              s.x = Math.random() * (doc_width - s.amplitude - 30);
              s.y = 0;
              s.stepX = 0.01 + Math.random() / 20;
              s.stepY = 0.3 + Math.random() / 2;
            }
            s.x += s.stepX;
            s.element.style.top = s.y + "px";
            s.element.style.left = s.x + s.amplitude * Math.sin(s.stepX) + "px";
          });
          requestAnimationFrame(anim);
        }
        anim();
      })();
    }
    // ===== Santa countdown (small widget) =====
    if (cfg.countdownSanta) {
      (function () {
        var st = document.createElement("style");
        st.textContent =
          ".hd-santa{background:url(https://mycrib.ru/wp-content/uploads/2025/01/santas-countdown-background.png) no-repeat 0 0/cover;width:180px;height:191px;position:fixed;right:10px;bottom:10px;z-index:10001;display:flex;justify-content:center;align-items:center}.hd-santa .countdown{font:14px Arial;color:#3b4edf;position:absolute;bottom:25px;left:20px}";
        document.head.appendChild(st);
        var box = document.createElement("div");
        box.className = "hd-santa";
        var cnt = document.createElement("div");
        cnt.className = "countdown";
        cnt.id = "hd_countdown";
        box.appendChild(cnt);
        document.body.appendChild(box);
        function days() {
          var t = new Date(),
            ny = new Date("January 1, " + (t.getFullYear() + 1)),
            d = Math.ceil((ny.getTime() - t.getTime()) / 86400000);
          return d;
        }
        function upd() {
          var d = days();
          var el = document.getElementById("hd_countdown");
          if (!el) return;
          el.textContent =
            d > 1
              ? d + " дней до Нового года!"
              : d === 1
              ? "1 день до Нового года!"
              : "С Новым годом!";
        }
        upd();
      })();
    }
    // ===== Banner countdown (wide bar) =====
    if (cfg.countdownBanner) {
      (function () {
        var st = document.createElement("style");
        st.textContent =
          ".hd-countdown-container{background:url(https://mycrib.ru/wp-content/uploads/2025/01/bg_new.png) repeat-x 0 0/auto 100%;width:100%;padding:20px 0;text-align:center;color:#fff;position:fixed;left:0;bottom:0;z-index:10001}.hd-count-title{font-size:24px;margin-bottom:10px}.hd-count-timer{display:flex;justify-content:center;gap:10px}.hd-count-item{display:flex;flex-direction:column;align-items:center}.hd-count-digits{display:flex;gap:5px}.hd-count-square{background:#fff;color:#333;font-size:24px;font-weight:bold;width:40px;height:40px;display:flex;justify-content:center;align-items:center;border-radius:5px}.hd-count-label{font-size:14px;color:#fff;margin-top:5px}@media(max-width:480px){.hd-count-title{font-size:18px}.hd-count-timer{gap:6px}.hd-count-square{width:30px;height:30px;font-size:18px}}";
        document.head.appendChild(st);
        var c = document.createElement("div");
        c.className = "hd-countdown-container";
        var html =
          '<div class="hd-count-title">До нового года осталось...</div>' +
          '<div class="hd-count-timer" id="hd-timer">' +
          '<div class="hd-count-item"><div class="hd-count-digits" id="hd-days"><div class="hd-count-square">0</div><div class="hd-count-square">0</div><div class="hd-count-square">0</div></div><div class="hd-count-label">Дней</div></div>' +
          '<div class="hd-count-item"><div class="hd-count-digits" id="hd-hours"><div class="hd-count-square">0</div><div class="hd-count-square">0</div></div><div class="hd-count-label">Часов</div></div>' +
          '<div class="hd-count-item"><div class="hd-count-digits" id="hd-mins"><div class="hd-count-square">0</div><div class="hd-count-square">0</div></div><div class="hd-count-label">Минут</div></div>';
        if (cfg.countdownShowSeconds) {
          html +=
            '<div class="hd-count-item"><div class="hd-count-digits" id="hd-secs"><div class="hd-count-square">0</div><div class="hd-count-square">0</div></div><div class="hd-count-label">Секунд</div></div>';
        }
        html += "</div>";
        c.innerHTML = html;
        document.body.appendChild(c);
        var intervalId = null;
        var desiredMs = 60000;
        function schedule(ms) {
          if (intervalId) {
            clearInterval(intervalId);
            intervalId = null;
          }
          desiredMs = ms;
          intervalId = setInterval(upd, ms);
        }
        function upd() {
          var now = new Date(),
            ny = new Date("January 1, " + (now.getFullYear() + 1)),
            left = ny - now,
            d = Math.floor(left / 86400000),
            h = Math.floor((left % 86400000) / 3600000),
            m = Math.floor((left % 3600000) / 60000),
            s = Math.floor((left % 60000) / 1000);
          function set(id, val, len) {
            var digs = String(val).padStart(len, "0").split("");
            var nodes = document.getElementById(id).children;
            for (var i = 0; i < len; i++) {
              nodes[i].textContent = digs[i];
            }
          }
          set("hd-days", d, 3);
          set("hd-hours", h, 2);
          set("hd-mins", m, 2);
          if (cfg.countdownShowSeconds) {
            var secsNode = document.getElementById("hd-secs");
            if (secsNode) set("hd-secs", s, 2);
          }
          // CPU-friendly scheduling: per minute if days>0 or seconds disabled; per second if today
          var wantMs = cfg.countdownShowSeconds && d === 0 ? 1000 : 60000;
          if (wantMs !== desiredMs && !document.hidden) {
            schedule(wantMs);
          }
          // Auto-collapse after New Year
          if (left <= 0) {
            var title = c.querySelector(".hd-count-title");
            if (title) title.textContent = "С Новым годом!";
            var timer = document.getElementById("hd-timer");
            if (timer) timer.style.display = "none";
            setTimeout(function () {
              if (c && c.parentNode) {
                c.parentNode.removeChild(c);
              }
            }, 10000);
            if (intervalId) {
              clearInterval(intervalId);
              intervalId = null;
            }
          }
        }
        // Initial scheduling based on settings
        schedule(cfg.countdownShowSeconds ? 1000 : 60000);
        // Pause when tab hidden, resume when visible
        document.addEventListener("visibilitychange", function () {
          if (document.hidden) {
            if (intervalId) {
              clearInterval(intervalId);
              intervalId = null;
            }
          } else {
            upd();
            schedule(desiredMs);
          }
        });
        upd();
      })();
    }
    window.HolidayDecor = {
      toggleGarland: toggleGarland,
      toggleSnow: toggleSnow,
      changeSnowSpeed: changeSnowSpeed,
    };
    window.addEventListener("resize", function () {
      if (cfg.snow) {
        createSnowflakes(cfg.snowCount);
      }
    });
  }
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
