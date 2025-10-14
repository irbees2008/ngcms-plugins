/**
 * SyntaxHighlighter
 * http://alexgorbatchev.com/
 *
 * SyntaxHighlighter is donationware. If you are using it, please donate.
 * http://alexgorbatchev.com/wiki/SyntaxHighlighter:Donate
 *
 * @version
 * 2.1.382 (June 24 2010)
 *
 * @copyright
 * Copyright (C) 2004-2009 Alex Gorbatchev.
 *
 * @license
 * This file is part of SyntaxHighlighter.
 *
 * SyntaxHighlighter is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * SyntaxHighlighter is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with SyntaxHighlighter.  If not, see <http://www.gnu.org/copyleft/lesser.html>.
 */
if (!window.SyntaxHighlighter)
{
    var SyntaxHighlighter = function ()
    {
        var sh = {
          defaults: {
            "class-name": "",
            "first-line": 1,
            "pad-line-numbers": true,
            highlight: null,
            "smart-tabs": true,
            "tab-size": 4,
            gutter: true,
            toolbar: true,
            collapse: false,
            "auto-links": true,
            light: false,
            "wrap-lines": true,
            "html-script": false,
          },
          config: {
            useScriptTags: true,
            clipboardSwf: null,
            toolbarItemWidth: 16,
            toolbarItemHeight: 16,
            bloggerMode: false,
            stripBrs: false,
            tagName: "pre",
            strings: {
              expandSource: "show source",
              viewSource: "Посмотреть код",
              copyToClipboard: "Скопировать в буфер",
              copyToClipboardConfirmation: "Код скопирован в буфер ",
              print: "print",
              alert: "",
            },
            debug: false,
          },
          vars: {
            discoveredBrushes: null,
            spaceWidth: null,
            printFrame: null,
            highlighters: {},
          },
          brushes: {},
          regexLib: {
            multiLineCComments: /\/\*[\s\S]*?\*\//gm,
            singleLineCComments: /\/\/.*$/gm,
            singleLinePerlComments: /#.*$/gm,
            doubleQuotedString: /"([^\\"\n]|\\.)*"/g,
            singleQuotedString: /'([^\\'\n]|\\.)*'/g,
            multiLineDoubleQuotedString: /"([^\\"]|\\.)*"/g,
            multiLineSingleQuotedString: /'([^\\']|\\.)*'/g,
            xmlComments: /(<|<)!--[\s\S]*?--(>|>)/gm,
            url: /<\w+:\/\/[\w-.\/?%&=@:;]*>|\w+:\/\/[\w-.\/?%&=@:;]*/g,
            phpScriptTags: {
              left: /(<|<)\?=?/g,
              right: /\?(>|>)/g,
            },
            aspScriptTags: {
              left: /(<|<)%=?/g,
              right: /%(>|>)/g,
            },
            scriptScriptTags: {
              left: /(<|<)\s*script.*?(>|>)/gi,
              right: /(<|<)\/\s*script\s*(>|>)/gi,
            },
          },
          toolbar: {
            create: function (_2) {
              var _3 = document.createElement("DIV"),
                _4 = sh.toolbar.items;
              _3.className = "toolbar";
              for (var _5 in _4) {
                var _6 = _4[_5],
                  _7 = new _6(_2),
                  _8 = _7.create();
                _2.toolbarCommands[_5] = _7;
                if (_8 == null) {
                  continue;
                }
                if (typeof _8 == "string") {
                  _8 = sh.toolbar.createButton(_8, _2.id, _5);
                }
                _8.className += "item " + _5;
                _3.appendChild(_8);
              }
              return _3;
            },
            createButton: function (_9, _a, _b) {
              var a = document.createElement("a"),
                _d = a.style,
                _e = sh.config,
                _f = _e.toolbarItemWidth,
                _10 = _e.toolbarItemHeight;
              a.href = "#" + _b;
              a.title = _9;
              a.highlighterId = _a;
              a.commandName = _b;
              a.innerHTML = _9;
              if (isNaN(_f) == false) {
                _d.width = _f + "px";
              }
              if (isNaN(_10) == false) {
                _d.height = _10 + "px";
              }
              a.onclick = function (e) {
                try {
                  sh.toolbar.executeCommand(
                    this,
                    e || window.event,
                    this.highlighterId,
                    this.commandName
                  );
                } catch (e) {
                  sh.utils.alert(e.message);
                }
                return false;
              };
              return a;
            },
            executeCommand: function (_12, _13, _14, _15, _16) {
              var _17 = sh.vars.highlighters[_14],
                _18;
              if (_17 == null || (_18 = _17.toolbarCommands[_15]) == null) {
                return null;
              }
              return _18.execute(_12, _13, _16);
            },
            items: {
              expandSource: function (_19) {
                this.create = function () {
                  if (_19.getParam("collapse") != true) {
                    return;
                  }
                  return sh.config.strings.expandSource;
                };
                this.execute = function (_1a, _1b, _1c) {
                  var div = _19.div;
                  _1a.parentNode.removeChild(_1a);
                  div.className = div.className.replace("collapsed", "");
                };
              },
              viewSource: function (_1e) {
                this.create = function () {
                  return sh.config.strings.viewSource;
                };
                this.execute = function (_1f, _20, _21) {
                  var _22 = sh.utils
                      .fixInputString(_1e.originalCode)
                      .replace(/</g, "<"),
                    wnd = sh.utils.popup(
                      "",
                      "_blank",
                      750,
                      400,
                      "location=0, resizable=1, menubar=0, scrollbars=1"
                    );
                  _22 = sh.utils.unindent(_22);
                  wnd.document.write("<pre>" + _22 + "</pre>");
                  wnd.document.close();
                };
              },
              copyToClipboard: function (_24) {
                this.create = function () {
                  // Проверяем поддержку Clipboard API или возможность использовать document.execCommand
                  if (!navigator.clipboard && !document.execCommand) {
                    return null; // Не показываем кнопку, если копирование невозможно
                  }
                  return sh.config.strings.copyToClipboard;
                };
                this.execute = function (_33, _34, _35) {
                  var _37 = sh.utils.unindent(
                    sh.utils
                      .fixInputString(_24.originalCode)
                      .replace(/</g, "<")
                      .replace(/>/g, ">")
                      .replace(/&amp;/g, "&")
                  );
                  // Функция для fallback копирования
                  var copyFallback = function (text) {
                    var textArea = document.createElement("textarea");
                    textArea.value = text;
                    textArea.style.position = "fixed";
                    textArea.style.opacity = "0";
                    textArea.style.left = "-9999px";
                    textArea.style.top = "0";
                    document.body.appendChild(textArea);
                    textArea.focus();
                    textArea.select();
                    try {
                      var successful = document.execCommand("copy");
                      document.body.removeChild(textArea);
                      return successful;
                    } catch (err) {
                      document.body.removeChild(textArea);
                      throw err;
                    }
                  };
                  // Современный Clipboard API (предпочтительный метод)
                  if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard
                      .writeText(_37)
                      .then(function () {
                        sh.utils.alert(
                          sh.config.strings.copyToClipboardConfirmation
                        );
                      })
                      .catch(function (err) {
                        // Пробуем fallback метод при ошибке
                        try {
                          if (copyFallback(_37)) {
                            sh.utils.alert(
                              sh.config.strings.copyToClipboardConfirmation
                            );
                          } else {
                            sh.utils.alert("Не удалось скопировать текст");
                          }
                        } catch (fallbackErr) {
                          sh.utils.alert("Ошибка копирования: " + fallbackErr);
                        }
                      });
                  }
                  // Старый метод для браузеров без Clipboard API
                  else if (document.execCommand) {
                    try {
                      if (copyFallback(_37)) {
                        sh.utils.alert(
                          sh.config.strings.copyToClipboardConfirmation
                        );
                      } else {
                        sh.utils.alert("Не удалось скопировать текст");
                      }
                    } catch (err) {
                      sh.utils.alert("Ошибка копирования: " + err);
                    }
                  }
                  // Если оба метода недоступны
                  else {
                    sh.utils.alert(
                      "Копирование не поддерживается вашим браузером"
                    );
                  }
                };
              },
              about: function (_42) {
                this.create = function () {
                  return sh.config.strings.help;
                };
                this.execute = function (_43, _44) {
                  var wnd = sh.utils.popup(
                      "",
                      "_blank",
                      500,
                      250,
                      "scrollbars=0"
                    ),
                    doc = wnd.document;
                  doc.write(sh.config.strings.aboutDialog);
                  doc.close();
                  wnd.focus();
                };
              },
            },
          },
          utils: {
            indexOf: function (_47, _48, _49) {
              _49 = Math.max(_49 || 0, 0);
              for (var i = _49; i < _47.length; i++) {
                if (_47[i] == _48) {
                  return i;
                }
              }
              return -1;
            },
            guid: function (_4b) {
              return _4b + Math.round(Math.random() * 1000000).toString();
            },
            merge: function (_4c, _4d) {
              var _4e = {},
                _4f;
              for (_4f in _4c) {
                _4e[_4f] = _4c[_4f];
              }
              for (_4f in _4d) {
                _4e[_4f] = _4d[_4f];
              }
              return _4e;
            },
            toBoolean: function (_50) {
              switch (_50) {
                case "true":
                  return true;
                case "false":
                  return false;
              }
              return _50;
            },
            popup: function (url, _52, _53, _54, _55) {
              var x = (screen.width - _53) / 2,
                y = (screen.height - _54) / 2;
              _55 +=
                ", left=" +
                x +
                ", top=" +
                y +
                ", width=" +
                _53 +
                ", height=" +
                _54;
              _55 = _55.replace(/^,/, "");
              var win = window.open(url, _52, _55);
              win.focus();
              return win;
            },
            addEvent: function (obj, _5a, _5b) {
              if (obj.attachEvent) {
                obj["e" + _5a + _5b] = _5b;
                obj[_5a + _5b] = function () {
                  obj["e" + _5a + _5b](window.event);
                };
                obj.attachEvent("on" + _5a, obj[_5a + _5b]);
              } else {
                obj.addEventListener(_5a, _5b, false);
              }
            },
            alert: function (str) {
              alert(sh.config.strings.alert + str);
            },
            findBrush: function (_5d, _5e) {
              var _5f = sh.vars.discoveredBrushes,
                _60 = null;
              if (_5f == null) {
                _5f = {};
                for (var _61 in sh.brushes) {
                  var _62 = sh.brushes[_61].aliases;
                  if (_62 == null) {
                    continue;
                  }
                  sh.brushes[_61].name = _61.toLowerCase();
                  for (var i = 0; i < _62.length; i++) {
                    _5f[_62[i]] = _61;
                  }
                }
                sh.vars.discoveredBrushes = _5f;
              }
              _60 = sh.brushes[_5f[_5d]];
              if (_60 == null && _5e != false) {
                sh.utils.alert(sh.config.strings.noBrush + _5d);
              }
              return _60;
            },
            eachLine: function (str, _65) {
              var _66 = str.split("\n");
              for (var i = 0; i < _66.length; i++) {
                _66[i] = _65(_66[i]);
              }
              return _66.join("\n");
            },
            trimFirstAndLastLines: function (str) {
              return str.replace(/^[ ]*[\n]+|[\n]*[ ]*$/g, "");
            },
            parseParams: function (str) {
              var _6a,
                _6b = {},
                _6c = new XRegExp("^\\[(?<values>(.*?))\\]$"),
                _6d = new XRegExp(
                  "(?<name>[\\w-]+)" +
                    "\\s*:\\s*" +
                    "(?<value>" +
                    "[\\w-%#]+|" +
                    "\\[.*?\\]|" +
                    '".*?"|' +
                    "'.*?'" +
                    ")\\s*;?",
                  "g"
                );
              while ((_6a = _6d.exec(str)) != null) {
                var _6e = _6a.value.replace(/^['"]|['"]$/g, "");
                if (_6e != null && _6c.test(_6e)) {
                  var m = _6c.exec(_6e);
                  _6e = m.values.length > 0 ? m.values.split(/\s*,\s*/) : [];
                }
                _6b[_6a.name] = _6e;
              }
              return _6b;
            },
            decorate: function (str, css) {
              if (str == null || str.length == 0 || str == "\n") {
                return str;
              }
              str = str.replace(/</g, "<");
              str = str.replace(/ {2,}/g, function (m) {
                var _73 = "";
                for (var i = 0; i < m.length - 1; i++) {
                  _73 += "&nbsp;";
                }
                return _73 + " ";
              });
              if (css != null) {
                str = sh.utils.eachLine(str, function (_75) {
                  if (_75.length == 0) {
                    return "";
                  }
                  var _76 = "";
                  _75 = _75.replace(/^(&nbsp;| )+/, function (s) {
                    _76 = s;
                    return "";
                  });
                  if (_75.length == 0) {
                    return _76;
                  }
                  return _76 + '<code class="' + css + '">' + _75 + "</code>";
                });
              }
              return str;
            },
            padNumber: function (_78, _79) {
              var _7a = _78.toString();
              while (_7a.length < _79) {
                _7a = "0" + _7a;
              }
              return _7a;
            },
            measureSpace: function () {
              var _7b = document.createElement("div"),
                _7c,
                _7d = 0,
                _7e = document.body,
                id = sh.utils.guid("measureSpace"),
                _80 = '<div class="',
                _81 = "</div>",
                _82 = "</span>";
              _7b.innerHTML =
                _80 +
                'syntaxhighlighter">' +
                _80 +
                'lines">' +
                _80 +
                'line">' +
                _80 +
                "content" +
                '"><span class="block"><span id="' +
                id +
                '">&nbsp;' +
                _82 +
                _82 +
                _81 +
                _81 +
                _81 +
                _81;
              _7e.appendChild(_7b);
              _7c = document.getElementById(id);
              if (/opera/i.test(navigator.userAgent)) {
                var _83 = window.getComputedStyle(_7c, null);
                _7d = parseInt(_83.getPropertyValue("width"));
              } else {
                _7d = _7c.offsetWidth;
              }
              _7e.removeChild(_7b);
              return _7d;
            },
            processTabs: function (_84, _85) {
              var tab = "";
              for (var i = 0; i < _85; i++) {
                tab += " ";
              }
              return _84.replace(/\t/g, tab);
            },
            processSmartTabs: function (_88, _89) {
              var _8a = _88.split("\n"),
                tab = "\t",
                _8c = "";
              for (var i = 0; i < 50; i++) {
                _8c += "                    ";
              }
              function insertSpaces(_8e, pos, _90) {
                return (
                  _8e.substr(0, pos) +
                  _8c.substr(0, _90) +
                  _8e.substr(pos + 1, _8e.length)
                );
              }
              _88 = sh.utils.eachLine(_88, function (_91) {
                if (_91.indexOf(tab) == -1) {
                  return _91;
                }
                var pos = 0;
                while ((pos = _91.indexOf(tab)) != -1) {
                  var _93 = _89 - (pos % _89);
                  _91 = insertSpaces(_91, pos, _93);
                }
                return _91;
              });
              return _88;
            },
            fixInputString: function (str) {
              var br = /<br\s*\/?>|<br\s*\/?>/gi;
              if (sh.config.bloggerMode == true) {
                str = str.replace(br, "\n");
              }
              if (sh.config.stripBrs == true) {
                str = str.replace(br, "\n");
              }
              return str;
            },
            trim: function (str) {
              return str.replace(/^\s+|\s+$/g, "");
            },
            unindent: function (str) {
              var _98 = sh.utils.fixInputString(str).split("\n"),
                _99 = new Array(),
                _9a = /^\s*/,
                min = 1000;
              for (var i = 0; i < _98.length && min > 0; i++) {
                var _9d = _98[i];
                if (sh.utils.trim(_9d).length == 0) {
                  continue;
                }
                var _9e = _9a.exec(_9d);
                if (_9e == null) {
                  return str;
                }
                min = Math.min(_9e[0].length, min);
              }
              if (min > 0) {
                for (var i = 0; i < _98.length; i++) {
                  _98[i] = _98[i].substr(min);
                }
              }
              return _98.join("\n");
            },
            matchesSortCallback: function (m1, m2) {
              if (m1.index < m2.index) {
                return -1;
              } else {
                if (m1.index > m2.index) {
                  return 1;
                } else {
                  if (m1.length < m2.length) {
                    return -1;
                  } else {
                    if (m1.length > m2.length) {
                      return 1;
                    }
                  }
                }
              }
              return 0;
            },
            getMatches: function (_a1, _a2) {
              function defaultAdd(_a3, _a4) {
                return [new sh.Match(_a3[0], _a3.index, _a4.css)];
              }
              var _a5 = 0,
                _a6 = null,
                _a7 = [],
                _a8 = _a2.func ? _a2.func : defaultAdd;
              while ((_a6 = _a2.regex.exec(_a1)) != null) {
                _a7 = _a7.concat(_a8(_a6, _a2));
              }
              return _a7;
            },
            processUrls: function (_a9) {
              var lt = "<",
                gt = ">";
              return _a9.replace(sh.regexLib.url, function (m) {
                var _ad = "",
                  _ae = "";
                if (m.indexOf(lt) == 0) {
                  _ae = lt;
                  m = m.substring(lt.length);
                }
                if (m.indexOf(gt) == m.length - gt.length) {
                  m = m.substring(0, m.length - gt.length);
                  _ad = gt;
                }
                return _ae + '<a href="' + m + '">' + m + "</a>" + _ad;
              });
            },
            getSyntaxHighlighterScriptTags: function () {
              var _af = document.getElementsByTagName("script"),
                _b0 = [];
              for (var i = 0; i < _af.length; i++) {
                if (_af[i].type == "syntaxhighlighter") {
                  _b0.push(_af[i]);
                }
              }
              return _b0;
            },
            stripCData: function (_b2) {
              var _b3 = "<![CDATA[",
                _b4 = "]]>",
                _b5 = sh.utils.trim(_b2),
                _b6 = false;
              if (_b5.indexOf(_b3) == 0) {
                _b5 = _b5.substring(_b3.length);
                _b6 = true;
              }
              if (_b5.indexOf(_b4) == _b5.length - _b4.length) {
                _b5 = _b5.substring(0, _b5.length - _b4.length);
                _b6 = true;
              }
              return _b6 ? _b5 : _b2;
            },
          },
          highlight: function (_b7, _b8) {
            function toArray(_b9) {
              var _ba = [];
              for (var i = 0; i < _b9.length; i++) {
                _ba.push(_b9[i]);
              }
              return _ba;
            }
            var _bc = _b8
                ? [_b8]
                : toArray(document.getElementsByTagName(sh.config.tagName)),
              _bd = "innerHTML",
              _be = null,
              _bf = sh.config;
            if (_bf.useScriptTags) {
              _bc = _bc.concat(sh.utils.getSyntaxHighlighterScriptTags());
            }
            if (_bc.length === 0) {
              return;
            }
            for (var i = 0; i < _bc.length; i++) {
              var _c1 = _bc[i],
                _c2 = sh.utils.parseParams(_c1.className),
                _c3,
                _c4,
                _c5;
              _c2 = sh.utils.merge(_b7, _c2);
              _c3 = _c2["brush"];
              if (_c3 == null) {
                continue;
              }
              if (
                _c2["html-script"] == "true" ||
                sh.defaults["html-script"] == true
              ) {
                _be = new sh.HtmlScript(_c3);
                _c3 = "htmlscript";
              } else {
                var _c6 = sh.utils.findBrush(_c3);
                if (_c6) {
                  _c3 = _c6.name;
                  _be = new _c6();
                } else {
                  continue;
                }
              }
              _c4 = _c1[_bd];
              if (_bf.useScriptTags) {
                _c4 = sh.utils.stripCData(_c4);
              }
              _c2["brush-name"] = _c3;
              _be.highlight(_c4, _c2);
              _c5 = _be.div;
              if (sh.config.debug) {
                _c5 = document.createElement("textarea");
                _c5.value = _be.div.innerHTML;
                _c5.style.width = "70em";
                _c5.style.height = "30em";
              }
              _c1.parentNode.replaceChild(_c5, _c1);
            }
          },
          all: function (_c7) {
            sh.utils.addEvent(window, "load", function () {
              sh.highlight(_c7);
            });
          },
        };
        sh.Match = function (_c8, _c9, css)
        {
            this.value = _c8;
            this.index = _c9;
            this.length = _c8.length;
            this.css = css;
            this.brushName = null;
        };
        sh.Match.prototype.toString = function ()
        {
            return this.value;
        };
        sh.HtmlScript = function (_cb)
        {
            var _cc = sh.utils.findBrush(_cb), _cd, _ce = new sh.brushes.Xml(), _cf = null;
            if (_cc == null) {
                return
            }
            _cd = new _cc();
            this.xmlBrush = _ce;
            if (_cd.htmlScript == null) {
                sh.utils.alert(sh.config.strings.brushNotHtmlScript + _cb);
                return
            }
            _ce.regexList.push({
                regex : _cd.htmlScript.code, func : process
            });
            function offsetMatches(_d0, _d1)
            {
                for (var j = 0; j < _d0.length; j++) {
                    _d0[j].index += _d1
                }
            };
            function process(_d3, _d4)
            {
                var _d5 = _d3.code, _d6 = [], _d7 = _cd.regexList, _d8 = _d3.index + _d3.left.length,
                _d9 = _cd.htmlScript, _da;
                for (var i = 0; i < _d7.length; i++) {
                    _da = sh.utils.getMatches(_d5, _d7[i]);
                    offsetMatches(_da, _d8);
                    _d6 = _d6.concat(_da)
                }
                if (_d9.left != null && _d3.left != null)
                {
                    _da = sh.utils.getMatches(_d3.left, _d9.left);
                    offsetMatches(_da, _d3.index);
                    _d6 = _d6.concat(_da)
                }
                if (_d9.right != null && _d3.right != null)
                {
                    _da = sh.utils.getMatches(_d3.right, _d9.right);
                    offsetMatches(_da, _d3.index + _d3[0].lastIndexOf(_d3.right));
                    _d6 = _d6.concat(_da)
                }
                for (var j = 0; j < _d6.length; j++) {
                    _d6[j].brushName = _cc.name
                }
                return _d6;
            }
        };
        sh.HtmlScript.prototype.highlight = function (_dd, _de)
        {
            this.xmlBrush.highlight(_dd, _de);
            this.div = this.xmlBrush.div;
        };
        sh.Highlighter = function () {};
        sh.Highlighter.prototype =
        {
            getParam : function (_df, _e0)
            {
                var _e1 = this.params[_df];
                return sh.utils.toBoolean(_e1 == null ? _e0 : _e1);
            },
            create : function (_e2)
            {
                return document.createElement(_e2);
            },
            findMatches : function (_e3, _e4)
            {
                var _e5 = [];
                if (_e3 != null)
                {
                    for (var i = 0; i < _e3.length; i++) {
                        if (typeof (_e3[i]) == "object") {
                            _e5 = _e5.concat(sh.utils.getMatches(_e4, _e3[i]));
                        }
                    }
                }
                return _e5.sort(sh.utils.matchesSortCallback);
            },
            removeNestedMatches : function ()
            {
                var _e7 = this.matches;
                for (var i = 0; i < _e7.length; i++)
                {
                    if (_e7[i] === null) {
                        continue
                    }
                    var _e9 = _e7[i], _ea = _e9.index + _e9.length;
                    for (var j = i + 1; j < _e7.length && _e7[i] !== null; j++)
                    {
                        var _ec = _e7[j];
                        if (_ec === null) {
                            continue
                        }
                        else
                        {
                            if (_ec.index > _ea) {
                                break
                            }
                            else
                            {
                                if (_ec.index == _e9.index && _ec.length > _e9.length) {
                                    this.matches[i] = null
                                }
                                else {
                                    if (_ec.index >= _e9.index && _ec.index < _ea) {
                                        this.matches[j] = null;
                                    }
                                }
                            }
                        }
                    }
                }
            },
            createDisplayLines : function (_ed)
            {
                var _ee = _ed.split("\n"), _ef = parseInt(this.getParam("first-line")), _f0 = this.getParam("pad-line-numbers"),
                _f1 = this.getParam("highlight", []), _f2 = this.getParam("gutter");
                _ed = "";
                if (_f0 == true) {
                    _f0 = (_ef + _ee.length - 1).toString().length
                }
                else {
                    if (isNaN(_f0) == true) {
                        _f0 = 0;
                    }
                }
                for (var i = 0; i < _ee.length; i++)
                {
                    var _f4 = _ee[i], _f5 = /^(&nbsp;|\s)+/.exec(_f4), _f6 = "alt" + (i % 2 == 0 ? 1 : 2),
                    _f7 = sh.utils.padNumber(_ef + i, _f0), _f8 = sh.utils.indexOf(_f1, (_ef + i).toString()) !=- 1,
                    _f9 = null;
                    if (_f5 != null) {
                        _f9 = _f5[0].toString();
                        _f4 = _f4.substr(_f9.length)
                    }
                    _f4 = sh.utils.trim(_f4);
                    if (_f4.length == 0) {
                        _f4 = "&nbsp;"
                    }
                    if (_f8) {
                        _f6 += " highlighted"
                    }
                    _ed += "<div class=\"line " + _f6 + "\">" + "<table>" + "<tr>" + (_f2 ? "<td class=\"number\"><code>" + _f7 + "</code></td>" : "") + "<td class=\"content\">" + (_f9 != null ? "<code class=\"spaces\">" + _f9.replace(" ",
                    "&nbsp;") + "</code>" : "") + _f4 + "</td>" + "</tr>" + "</table>" + "</div>"
                }
                return _ed;
            },
            processMatches : function (_fa, _fb)
            {
                var pos = 0, _fd = "", _fe = sh.utils.decorate, _ff = this.getParam("brush-name", "");
                function getBrushNameCss(_100)
                {
                    var _101 = _100 ? (_100.brushName || _ff) : _ff;
                    return _101 ? _101 + " " : "";
                };
                for (var i = 0; i < _fb.length; i++)
                {
                    var _103 = _fb[i], _104;
                    if (_103 === null || _103.length === 0) {
                        continue
                    }
                    _104 = getBrushNameCss(_103);
                    _fd += _fe(_fa.substr(pos, _103.index - pos), _104 + "plain") + _fe(_103.value, _104 + _103.css);
                    pos = _103.index + _103.length
                }
                _fd += _fe(_fa.substr(pos), getBrushNameCss() + "plain");
                return _fd;
            },
            highlight : function (code, _106)
            {
                var conf = sh.config, vars = sh.vars, div, _10a, _10b, _10c = "important";
                this.params = {};
                this.div = null;
                this.lines = null;
                this.code = null;
                this.bar = null;
                this.toolbarCommands = {};
                this.id = sh.utils.guid("highlighter_");
                vars.highlighters[this.id] = this;
                if (code === null) {
                    code = ""
                }
                this.params = sh.utils.merge(sh.defaults, _106 || {});
                if (this.getParam("light") == true) {
                    this.params.toolbar = this.params.gutter = false
                }
                this.div = div = this.create("DIV");
                this.lines = this.create("DIV");
                this.lines.className = "lines";
                className = "syntaxhighlighter";
                div.id = this.id;
                if (this.getParam("collapse")) {
                    className += " collapsed"
                }
                if (this.getParam("gutter") == false) {
                    className += " nogutter"
                }
                if (this.getParam("wrap-lines") == false) {
                    this.lines.className += " no-wrap"
                }
                className += " " + this.getParam("class-name");
                className += " " + this.getParam("brush-name");
                div.className = className;
                this.originalCode = code;
                this.code = sh.utils.trimFirstAndLastLines(code).replace(/\r/g, " ");
                _10b = this.getParam("tab-size");
                this.code = this.getParam("smart-tabs") == true ? sh.utils.processSmartTabs(this.code,
                _10b) : sh.utils.processTabs(this.code, _10b);
                this.code = sh.utils.unindent(this.code);
                if (this.getParam("toolbar"))
                {
                    this.bar = this.create("DIV");
                    this.bar.className = "bar";
                    this.bar.appendChild(sh.toolbar.create(this));
                    div.appendChild(this.bar);
                    var bar = this.bar;
                    function hide()
                    {
                        bar.className = bar.className.replace("show", "");
                    };
                    div.onmouseover = function ()
                    {
                        hide();
                        bar.className += " show"
                    };
                    div.onmouseout = function ()
                    {
                        hide()
                    }
                }
                div.appendChild(this.lines);
                this.matches = this.findMatches(this.regexList, this.code);
                this.removeNestedMatches();
                code = this.processMatches(this.code, this.matches);
                code = this.createDisplayLines(sh.utils.trim(code));
                if (this.getParam("auto-links")) {
                    code = sh.utils.processUrls(code)
                }
                this.lines.innerHTML = code;
            },
            getKeywords : function (str)
            {
                str = str.replace(/^\s+|\s+$/g, "").replace(/\s+/g, "|");
                return "\\b(?:" + str + ")\\b";
            },
            forHtmlScript : function (_10f)
            {
                this.htmlScript =
                {
                    left : {
                        regex : _10f.left, css : "script"
                    },
                    right : {
                        regex : _10f.right, css : "script"
                    },
                    code : new XRegExp("(?<left>" + _10f.left.source + ")" + "(?<code>.*?)" + "(?<right>" + _10f.right.source + ")",
                    "sgi")
                }
            }
        };
        return sh
    }
    ()
}
if (!window.XRegExp)
{
    (function ()
    {
        var real =
        {
            exec : RegExp.prototype.exec, match : String.prototype.match, replace : String.prototype.replace,
            split : String.prototype.split
        },
        lib = {
            part : /(?:[^\\([#\s.]+|\\(?!k<[\w$]+>|[pP]{[^}]+})[\S\s]?|\((?=\?(?!#|<[\w$]+>)))+|(\()(?:\?(?:(#)[^)]*\)|<([$\w]+)>))?|\\(?:k<([\w$]+)>|[pP]{([^}]+)})|(\[\^?)|([\S\s])/g,
            replaceVar : /(?:[^$]+|\$(?![1-9$&`']|{[$\w]+}))+|\$(?:([1-9]\d*|[$&`'])|{([$\w]+)})/g, extended : /^(?:\s+|#.*)+/,
            quantifier : /^(?:[?*+]|{\d+(?:,\d*)?})/, classLeft : /&&\[\^?/g, classRight : /]/g
        },
        _112 = function (_113, item, from)
        {
            for (var i = from || 0; i < _113.length; i++) {
                if (_113[i] === item) {
                    return i;
                }
            }
            return - 1;
        },
        _117 = /()??/.exec("")[1] !== undefined, _118 = {};
        XRegExp = function (_119, _11a)
        {
            if (_119 instanceof RegExp)
            {
                if (_11a !== undefined) {
                    throw TypeError("can't supply flags when constructing one RegExp from another")
                }
                return _119.addFlags()
            }
            var _11a = _11a || "", _11b = _11a.indexOf("s") > -1, _11c = _11a.indexOf("x") > -1, _11d = false,
            _11e = [], _11f = [], part = lib.part, _121, cc, len, _124, _125;
            part.lastIndex = 0;
            while (_121 = real.exec.call(part, _119))
            {
                if (_121[2]) {
                    if (!lib.quantifier.test(_119.slice(part.lastIndex))) {
                        _11f.push("(?:)")
                    }
                }
                else
                {
                    if (_121[1]) {
                        _11e.push(_121[3] || null);
                        if (_121[3]) {
                            _11d = true
                        }
                        _11f.push("(")
                    }
                    else
                    {
                        if (_121[4])
                        {
                            _124 = _112(_11e, _121[4]);
                            _11f.push(_124 > -1 ? "\\" + (_124 + 1) + (isNaN(_119.charAt(part.lastIndex)) ? "" : "(?:)") : _121[0])
                        }
                        else
                        {
                            if (_121[5]) {
                                _11f.push(_118.unicode ? _118.unicode.get(_121[5], _121[0].charAt(1) === "P") : _121[0])
                            }
                            else
                            {
                                if (_121[6])
                                {
                                    if (_119.charAt(part.lastIndex) === "]") {
                                        _11f.push(_121[6] === "[" ? "(?!)" : "[\\S\\s]");
                                        part.lastIndex++
                                    }
                                    else
                                    {
                                        cc = XRegExp.matchRecursive("&&" + _119.slice(_121.index), lib.classLeft,
                                        lib.classRight, "", {
                                            escapeChar : "\\"
                                        })[0];
                                        _11f.push(_121[6] + cc + "]");
                                        part.lastIndex += cc.length + 1
                                    }
                                }
                                else
                                {
                                    if (_121[7])
                                    {
                                        if (_11b && _121[7] === ".") {
                                            _11f.push("[\\S\\s]")
                                        }
                                        else
                                        {
                                            if (_11c && lib.extended.test(_121[7]))
                                            {
                                                len = real.exec.call(lib.extended, _119.slice(part.lastIndex - 1))[0].length;
                                                if (!lib.quantifier.test(_119.slice(part.lastIndex - 1 + len))) {
                                                    _11f.push("(?:)")
                                                }
                                                part.lastIndex += len - 1
                                            }
                                            else {
                                                _11f.push(_121[7])
                                            }
                                        }
                                    }
                                    else {
                                        _11f.push(_121[0])
                                    }
                                }
                            }
                        }
                    }
                }
            }
            _125 = RegExp(_11f.join(""), real.replace.call(_11a, /[sx]+/g, ""));
            _125._x = {
                source : _119, captureNames : _11d ? _11e : null
            };
            return _125;
        };
        XRegExp.addPlugin = function (name, o)
        {
            _118[name] = o;
        };
        RegExp.prototype.exec = function (str)
        {
            var _129 = real.exec.call(this, str), name, i, r2;
            if (_129)
            {
                if (_117 && _129.length > 1)
                {
                    r2 = new RegExp("^" + this.source + "$(?!\\s)", this.getNativeFlags());
                    real.replace.call(_129[0], r2, function ()
                    {
                        for (i = 1; i < arguments.length - 2; i++) {
                            if (arguments[i] === undefined) {
                                _129[i] = undefined;
                            }
                        }
                    })
                }
                if (this._x && this._x.captureNames)
                {
                    for (i = 1; i < _129.length; i++) {
                        name = this._x.captureNames[i - 1];
                        if (name) {
                            _129[name] = _129[i];
                        }
                    }
                }
                if (this.global && this.lastIndex > (_129.index + _129[0].length)) {
                    this.lastIndex--
                }
            }
            return _129;
        }
    })()
}
RegExp.prototype.getNativeFlags = function ()
{
    return (this.global ? "g" : "") + (this.ignoreCase ? "i" : "") + (this.multiline ? "m" : "") + (this.extended ? "x" : "") + (this.sticky ? "y" : "");
};
RegExp.prototype.addFlags = function (_12d)
{
    var _12e = new XRegExp(this.source, (_12d || "") + this.getNativeFlags());
    if (this._x)
    {
        _12e._x =
        {
            source : this._x.source, captureNames : this._x.captureNames ? this._x.captureNames.slice(0) : null
        }
    }
    return _12e;
};
RegExp.prototype.call = function (_12f, str)
{
    return this.exec(str);
};
RegExp.prototype.apply = function (_131, args)
{
    return this.exec(args[0]);
};
XRegExp.cache = function (_133, _134)
{
    var key = "/" + _133 + "/" + (_134 || "");
    return XRegExp.cache[key] || (XRegExp.cache[key] = new XRegExp(_133, _134));
};
XRegExp.escape = function (str)
{
    return str.replace(/[-[\]{}()*+?.\\^$|,#\s]/g, "\\$&");
};
XRegExp.matchRecursive = function (str, left, _139, _13a, _13b)
{
    var _13b = _13b || {}, _13c = _13b.escapeChar, vN = _13b.valueNames, _13a = _13a || "", _13e = _13a.indexOf("g") > -1,
    _13f = _13a.indexOf("i") > -1, _140 = _13a.indexOf("m") > -1, _141 = _13a.indexOf("y") > -1, _13a = _13a.replace(/y/g,
    ""), left = left instanceof RegExp ? (left.global ? left : left.addFlags("g")) : new XRegExp(left,
    "g" + _13a), _139 = _139 instanceof RegExp ? (_139.global ? _139 : _139.addFlags("g")) : new XRegExp(_139,
    "g" + _13a), _142 = [], _143 = 0, _144 = 0, _145 = 0, _146 = 0, _147, _148, _149, _14a, _14b, esc;
    if (_13c)
    {
        if (_13c.length > 1) {
            throw SyntaxError("can't supply more than one escape character")
        }
        if (_140) {
            throw TypeError("can't supply escape character when using the multiline flag")
        }
        _14b = XRegExp.escape(_13c);
        esc = new RegExp("^(?:" + _14b + "[\\S\\s]|(?:(?!" + left.source + "|" + _139.source + ")[^" + _14b + "])+)+",
        _13f ? "i" : "")
    }
    while (true)
    {
        left.lastIndex = _139.lastIndex = _145 + (_13c ? (esc.exec(str.slice(_145)) || [""])[0].length : 0);
        _149 = left.exec(str);
        _14a = _139.exec(str);
        if (_149 && _14a) {
            if (_149.index <= _14a.index) {
                _14a = null
            }
            else {
                _149 = null;
            }
        }
        if (_149 || _14a) {
            _144 = (_149 || _14a).index;
            _145 = (_149 ? left : _139).lastIndex
        }
        else {
            if (!_143) {
                break
            }
        }
        if (_141 && !_143 && _144 > _146) {
            break
        }
        if (_149) {
            if (!_143++) {
                _147 = _144;
                _148 = _145;
            }
        }
        else
        {
            if (_14a && _143)
            {
                if (!--_143)
                {
                    if (vN)
                    {
                        if (vN[0] && _147 > _146) {
                            _142.push([vN[0], str.slice(_146, _147), _146, _147])
                        }
                        if (vN[1]) {
                            _142.push([vN[1], str.slice(_147, _148), _147, _148])
                        }
                        if (vN[2]) {
                            _142.push([vN[2], str.slice(_148, _144), _148, _144])
                        }
                        if (vN[3]) {
                            _142.push([vN[3], str.slice(_144, _145), _144, _145])
                        }
                    }
                    else {
                        _142.push(str.slice(_148, _144))
                    }
                    _146 = _145;
                    if (!_13e) {
                        break
                    }
                }
            }
            else
            {
                left.lastIndex = _139.lastIndex = 0;
                throw Error("subject data contains unbalanced delimiters")
            }
        }
        if (_144 === _145) {
            _145++
        }
    }
    if (_13e && !_141 && vN && vN[0] && str.length > _146) {
        _142.push([vN[0], str.slice(_146), _146, str.length])
    }
    left.lastIndex = _139.lastIndex = 0;
    return _142;
};
