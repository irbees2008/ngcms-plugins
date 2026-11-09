(function (sceditor) {
  "use strict";
  sceditor.plugins.undo = function () {
    var base = this;
    var sourceEditor;
    var editor;
    var body;
    var lastInputType = "";
    var charChangedCount = 0;
    var isInPatchedFn = false;
    // If currently restoring a state - ignore events while it's happening
    var isApplying = false;
    // If current selection change event has already been stored
    var isSelectionChangeHandled = false;
    var undoLimit = 50;
    var undoStates = [];
    var redoPosition = 0;
    var lastState;
    function applyState(state) {
      isApplying = true;
      editor.sourceMode(state.sourceMode);
      if (state.sourceMode) {
        editor.val(state.value, false);
        editor.sourceEditorCaret(state.caret);
      } else {
        editor.getBody().innerHTML = state.value;
        if (state.caret) {
          var range = editor.getRangeHelper().selectedRange();
          setRangePositions(range, state.caret);
          editor.getRangeHelper().selectRange(range);
        }
      }
      editor.focus();
      isApplying = false;
    }
    function patch(obj, fn) {
      var origFn = obj[fn];
      obj[fn] = function () {
        var ignore = isInPatchedFn;
        if (
          !ignore &&
          !isApplying &&
          lastState &&
          editor.getRangeHelper().hasSelection()
        ) {
          updateLastState();
        }
        isInPatchedFn = true;
        origFn.apply(this, arguments);
        if (!ignore) {
          isInPatchedFn = false;
          if (!isApplying) {
            storeState();
            lastInputType = "";
          }
        }
      };
    }
    function storeState() {
      // Avoid pushing duplicate consecutive states (same mode + same content)
      try {
        var curSourceMode =
          editor && typeof editor.sourceMode === "function"
            ? editor.sourceMode()
            : false;
        var curValue = curSourceMode
          ? editor && typeof editor.getSourceEditorValue === "function"
            ? editor.getSourceEditorValue(false)
            : ""
          : editor && typeof editor.getBody === "function" && editor.getBody()
          ? editor.getBody().innerHTML
          : "";
        var prev = undoStates.length ? undoStates[undoStates.length - 1] : null;
        if (
          prev &&
          prev.sourceMode === curSourceMode &&
          prev.value === curValue
        ) {
          // Just refresh caret of the last state
          lastState = prev;
          updateLastState();
          return;
        }
      } catch (_) {}
      if (redoPosition) {
        undoStates.length -= redoPosition;
        redoPosition = 0;
      }
      if (undoLimit > 0 && undoStates.length > undoLimit) {
        undoStates.shift();
      }
      lastState = {};
      updateLastState();
      undoStates.push(lastState);
    }
    function updateLastState() {
      var sourceMode = editor.sourceMode();
      lastState.caret = sourceMode
        ? editor.sourceEditorCaret()
        : getRangePositions(editor.getRangeHelper().selectedRange());
      lastState.sourceMode = sourceMode;
      lastState.value = sourceMode
        ? editor.getSourceEditorValue(false)
        : editor.getBody().innerHTML;
    }
    base.init = function () {
      editor = this;
      undoLimit = editor.undoLimit || undoLimit;
      editor.addShortcut("ctrl+z", base.undo);
      editor.addShortcut("ctrl+shift+z", base.redo);
      editor.addShortcut("ctrl+y", base.redo);
      // Ensure toolbar commands exist so buttons can render when included in the toolbar string
      if (editor && editor.commands) {
        if (!editor.commands.undo) {
          editor.commands.undo = {
            exec: function () {
              return base.undo();
            },
            txtExec: function () {
              return base.undo();
            },
            tooltip: "Undo",
          };
        }
        if (!editor.commands.redo) {
          editor.commands.redo = {
            exec: function () {
              return base.redo();
            },
            txtExec: function () {
              return base.redo();
            },
            tooltip: "Redo",
          };
        }
      }
    };
    function documentSelectionChangeHandler() {
      if (sourceEditor === document.activeElement) {
        base.signalSelectionchangedEvent();
      }
    }
    base.signalReady = function () {
      var contentContainer = editor.getContentAreaContainer();
      // Resolve source editor element (textarea) safely
      sourceEditor = null;
      if (contentContainer) {
        var next = contentContainer.nextSibling;
        while (next && next.nodeType !== 1) {
          next = next.nextSibling;
        }
        if (next && next.tagName === "TEXTAREA") {
          sourceEditor = next;
        } else {
          var parent = contentContainer.parentNode || contentContainer;
          if (parent && parent.querySelector) {
            var ta = parent.querySelector("textarea");
            if (ta) {
              sourceEditor = ta;
            }
          }
        }
      }
      body = editor.getBody();
      // Store initial state
      storeState();
      // Patch methods that allow inserting content into the editor programmatically
      patch(editor, "setWysiwygEditorValue");
      patch(editor, "setSourceEditorValue");
      patch(editor, "sourceEditorInsertText");
      // Also patch common mutation APIs used by toolbar commands and inserts
      if (typeof editor.execCommand === "function") {
        patch(editor, "execCommand");
      }
      if (typeof editor.insertText === "function") {
        patch(editor, "insertText");
      }
      if (typeof editor.insert === "function") {
        patch(editor, "insert");
      }
      if (typeof editor.val === "function") {
        patch(editor, "val");
      }
      patch(editor.getRangeHelper(), "insertNode");
      patch(editor, "toggleSourceMode");
      function beforeInputHandler(e) {
        if (e.inputType === "historyUndo") {
          base.undo();
          e.preventDefault();
        } else if (e.inputType === "historyRedo") {
          base.redo();
          e.preventDefault();
        }
      }
      function inputHandler(e) {
        // Proxy input events to plugin signal to ensure states are stored
        try {
          base.signalInputEvent(e || {});
        } catch (_) {}
      }
      if (body && typeof body.addEventListener === "function") {
        body.addEventListener("beforeinput", beforeInputHandler);
        body.addEventListener("input", inputHandler);
      }
      if (sourceEditor && typeof sourceEditor.addEventListener === "function") {
        sourceEditor.addEventListener("beforeinput", beforeInputHandler);
        sourceEditor.addEventListener("input", inputHandler);
      }
      function compositionHandler() {
        lastInputType = "";
        storeState();
      }
      if (body && typeof body.addEventListener === "function") {
        body.addEventListener("compositionend", compositionHandler);
      }
      if (sourceEditor && typeof sourceEditor.addEventListener === "function") {
        sourceEditor.addEventListener("compositionend", compositionHandler);
      }
      document.addEventListener(
        "selectionchange",
        documentSelectionChangeHandler
      );
      // Extra safety: save a snapshot on any valuechanged signal, in case input events
      // are lost in this environment
      try {
        if (editor && typeof editor.valueChanged === "function") {
          editor.valueChanged(function () {
            if (!isApplying) {
              storeState();
            }
          });
        }
      } catch (_) {}
    };
    base.destroy = function () {
      document.removeEventListener(
        "selectionchange",
        documentSelectionChangeHandler
      );
    };
    base.undo = function () {
      lastState = null;
      if (redoPosition < undoStates.length - 1) {
        redoPosition++;
        applyState(undoStates[undoStates.length - 1 - redoPosition]);
      }
      return false;
    };
    base.redo = function () {
      if (redoPosition > 0) {
        redoPosition--;
        applyState(undoStates[undoStates.length - 1 - redoPosition]);
      }
      return false;
    };
    base.signalSelectionchangedEvent = function () {
      if (isApplying || isSelectionChangeHandled) {
        isSelectionChangeHandled = false;
        return;
      }
      if (lastState) {
        updateLastState();
      }
      lastInputType = "";
    };
    base.signalInputEvent = function (e) {
      var inputType = e && e.inputType;
      isSelectionChangeHandled = true;
      // If composing text, wait for compositionend
      if (e && e.isComposing) {
        return;
      }
      // Fallback for environments where inputType is missing:
      // treat any input as a state boundary so undo/redo work predictably
      if (!inputType) {
        lastInputType = "sce-misc";
        charChangedCount = 0;
        storeState();
        return;
      }
      switch (e.inputType) {
        case "deleteContentBackward":
          if (
            lastState &&
            lastInputType === inputType &&
            charChangedCount < 20
          ) {
            updateLastState();
          } else {
            storeState();
            charChangedCount = 0;
          }
          lastInputType = inputType;
          break;
        case "insertText":
          charChangedCount += e.data ? e.data.length : 1;
          if (
            lastState &&
            lastInputType === inputType &&
            charChangedCount < 20 &&
            !/\s$/.test(e.data)
          ) {
            updateLastState();
          } else {
            storeState();
            charChangedCount = 0;
          }
          lastInputType = inputType;
          break;
        default:
          lastInputType = "sce-misc";
          charChangedCount = 0;
          storeState();
          break;
      }
    };
    function getRangePositions(range) {
      if (!range) {
        return;
      }
      if (body && typeof body.normalize === "function") {
        body.normalize();
      }
      return {
        startPositions: nodeToPositions(
          range.startContainer,
          range.startOffset
        ),
        endPositions: nodeToPositions(range.endContainer, range.endOffset),
      };
    }
    function setRangePositions(range, positions) {
      if (
        !positions ||
        !positions.startPositions ||
        !positions.endPositions ||
        !positions.startPositions.length ||
        !positions.endPositions.length
      ) {
        return;
      }
      var startPositions = positions.startPositions;
      var endPositions = positions.endPositions;
      var bodyNode =
        editor && typeof editor.getBody === "function"
          ? editor.getBody()
          : body;
      if (!bodyNode) {
        return;
      }
      try {
        var startNode = positionsToNode(bodyNode, startPositions);
        var endNode = positionsToNode(bodyNode, endPositions);
        var isValidNode = function (n) {
          return (
            n && (n.nodeType === 1 || n.nodeType === 3 || n.nodeType === 11)
          );
        };
        // Helper to clamp offset to node bounds
        var clampOffset = function (node, offset) {
          if (!node) return 0;
          if (node.nodeType === 3) {
            // text
            var len = (node.nodeValue || "").length;
            if (offset == null) offset = len;
            return Math.max(0, Math.min(offset, len));
          }
          if (node.nodeType === 1 || node.nodeType === 11) {
            // element or fragment
            var count = node.childNodes ? node.childNodes.length : 0;
            if (offset == null) offset = count;
            return Math.max(0, Math.min(offset, count));
          }
          return 0;
        };
        if (!isValidNode(startNode) || !isValidNode(endNode)) {
          var endOffset = bodyNode.childNodes ? bodyNode.childNodes.length : 0;
          range.setStart(bodyNode, endOffset);
          range.setEnd(bodyNode, endOffset);
        } else {
          var startOffset = clampOffset(startNode, startPositions[0]);
          var endOffset = clampOffset(endNode, endPositions[0]);
          range.setStart(startNode, startOffset);
          range.setEnd(endNode, endOffset);
        }
      } catch (e) {
        // Fallback: place caret at end of body silently
        try {
          var endFallback = bodyNode.childNodes
            ? bodyNode.childNodes.length
            : 0;
          range.setStart(bodyNode, endFallback);
          range.setEnd(bodyNode, endFallback);
        } catch (_e) {
          /* ignore */
        }
      }
    }
    function nodeToPositions(container, offset) {
      var positions = [offset];
      var node = container;
      var root =
        editor && typeof editor.getBody === "function"
          ? editor.getBody()
          : null;
      while (node && node !== root) {
        positions.push(nodeIndex(node));
        node = node.parentNode;
      }
      return positions;
    }
    function nodeIndex(node) {
      var i = 0;
      while ((node = node.previousSibling)) {
        i++;
      }
      return i;
    }
    function positionsToNode(node, positions) {
      if (!positions || !positions.length) {
        return node;
      }
      for (var i = positions.length - 1; node && i > 0; i--) {
        var idx = positions[i];
        if (
          !node.childNodes ||
          idx == null ||
          idx < 0 ||
          idx >= node.childNodes.length
        ) {
          return node;
        }
        node = node.childNodes[idx];
      }
      return node;
    }
  };
})(sceditor);
