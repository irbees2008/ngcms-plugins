/* SCEditor icon pack: Font Awesome 4.7 */
(function (sceditor) {
  if (!sceditor || !("icons" in sceditor)) return;
  function createIcon(className, title) {
    try {
      var i = document.createElement("i");
      i.className = "fa " + className;
      if (title) i.title = title;
      return i;
    } catch (e) {
      return null;
    }
  }
  // Map SCEditor command names to Font Awesome 4.7 icon classes
  var FA = {
    // text styles
    bold: "fa-bold",
    italic: "fa-italic",
    underline: "fa-underline",
    strike: "fa-strikethrough",
    subscript: "fa-subscript",
    superscript: "fa-superscript",
    // align
    left: "fa-align-left",
    center: "fa-align-center",
    right: "fa-align-right",
    justify: "fa-align-justify",
    // font controls
    font: "fa-font",
    size: "fa-text-height",
    color: "fa-tint",
    removeformat: "fa-eraser",
    // clipboard
    cut: "fa-scissors",
    copy: "fa-files-o",
    paste: "fa-clipboard",
    pastetext: "fa-clipboard",
    // lists/indent
    bulletlist: "fa-list-ul",
    orderedlist: "fa-list-ol",
    indent: "fa-indent",
    outdent: "fa-outdent",
    // table/hline/code/quote
    table: "fa-table",
    horizontalrule: "fa-minus",
    code: "fa-code",
    quote: "fa-quote-right",
    // links/media
    image: "fa-picture-o",
    email: "fa-envelope-o",
    link: "fa-link",
    unlink: "fa-chain-broken",
    emoticon: "fa-smile-o",
    emojis: "fa-smile-o",
    youtube: "fa-youtube-play",
    // date/time
    date: "fa-calendar",
    time: "fa-clock-o",
    // direction
    ltr: "fa-long-arrow-right",
    rtl: "fa-long-arrow-left",
    // misc
    print: "fa-print",
    maximize: "fa-arrows-alt",
    source: "fa-code",
    undo: "fa-undo",
    redo: "fa-repeat",
    // resizer grip
    grip: "fa-arrows",
    // Custom NGCMS commands
    para: "fa-paragraph",
    spoiler: "fa-list-alt",
    acronym: "fa-tags",
    hide: "fa-shield",
    media: "fa-play-circle",
    codebrush: "fa-code",
    nextpage: "fa-files-o",
    more: "fa-ellipsis-h",
    // NGCMS helpers
    ngimage: "fa-file-image-o",
    ngfile: "fa-file-text-o",
  };
  sceditor.icons.fontawesome4 = function () {
    this.create = function (name) {
      var cls = FA[name];
      if (!cls) return null;
      return createIcon(cls, name);
    };
    // Optional: react to RTL changes if needed (not required for FA)
    this.rtl = function () {};
    this.update = function () {};
  };
})(window.sceditor);
