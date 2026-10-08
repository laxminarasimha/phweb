/**
 * Lightweight replacement for PixelAdmin's pxUtil + window.PixelAdmin
 * Keeps pxUtil for backward compatibility, replaces heavy PixelAdmin global
 */

// Object.keys polyfill for IE
(function () {
  'use strict';
  if (!Object.keys) {
    Object.keys = (function () {
      var hasOwn = Object.prototype.hasOwnProperty,
          hasDontEnumBug = !({ toString: null }).propertyIsEnumerable('toString'),
          dontEnums = ['toString', 'toLocaleString', 'valueOf', 'hasOwnProperty', 'isPrototypeOf', 'propertyIsEnumerable', 'constructor'],
          dontEnumsLength = dontEnums.length;
      return function (obj) {
        if (typeof obj !== 'object' && (typeof obj !== 'function' || obj === null)) {
          throw new TypeError('Object.keys called on non-object');
        }
        var result = [], prop, i;
        for (prop in obj) { if (hasOwn.call(obj, prop)) result.push(prop); }
        if (hasDontEnumBug) {
          for (i = 0; i < dontEnumsLength; i++) {
            if (hasOwn.call(obj, dontEnums[i])) result.push(dontEnums[i]);
          }
        }
        return result;
      };
    })();
  }
})();

/**
 * pxUtil - class manipulation utilities (backward compatible)
 */
var pxUtil = (function () {
  'use strict';
  var classListSupported = 'classList' in document.documentElement;

  function forEach(items, fn) {
    var arr = Object.prototype.toString.call(items) === '[object Array]' ? items : items.split(' ');
    for (var i = 0; i < arr.length; i++) fn(arr[i], i);
  }

  var _hasClass = classListSupported
    ? function (el, c) { return el.classList.contains(c); }
    : function (el, c) { return new RegExp('(?:^|\\s)' + c + '(?:\\s|$)').test(el.className); };

  var _addClass = classListSupported
    ? function (el, c) { el.classList.add(c); }
    : function (el, c) { if (!_hasClass(el, c)) el.className += (el.className ? ' ' : '') + c; };

  var _removeClass = classListSupported
    ? function (el, c) { el.classList.remove(c); }
    : function (el, c) {
        if (!_hasClass(el, c)) return;
        el.className = el.className.replace(new RegExp('(?:^' + c + '\\s+)|(?:^\\s*' + c + '\\s*$)|(?:\\s+' + c + '$)', 'g'), '').replace(new RegExp('\\s+' + c + '\\s+', 'g'), ' ');
      };

  var _toggleClass = classListSupported
    ? function (el, c) { el.classList.toggle(c); }
    : function (el, c) { return (_hasClass(el, c) ? _removeClass : _addClass)(el, c); };

  return {
    generateUniqueId: function () {
      var s = (Math.floor(Math.random() * 25) + 10).toString(36) + '_';
      s += new Date().getTime().toString(36) + '_';
      do { s += Math.floor(Math.random() * 35).toString(36); } while (s.length < 32);
      return s;
    },
    escapeRegExp: function (str) { return str.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&"); },
    hexToRgba: function (color, opacity) {
      var hex = color.replace('#', '');
      return 'rgba(' + parseInt(hex.substring(0, 2), 16) + ', ' + parseInt(hex.substring(2, 4), 16) + ', ' + parseInt(hex.substring(4, 6), 16) + ', ' + opacity + ')';
    },
    triggerResizeEvent: function () {
      var event;
      if (document.createEvent) {
        event = document.createEvent('HTMLEvents');
        event.initEvent('resize', true, true);
        window.dispatchEvent(event);
      } else if (document.createEventObject) {
        event = document.createEventObject();
        event.eventType = 'resize';
        window.fireEvent('onresize', event);
      }
    },
    hasClass: function (el, c) { return _hasClass(el, c); },
    addClass: function (el, cs) { forEach(cs, function (c) { _addClass(el, c); }); },
    removeClass: function (el, cs) { forEach(cs, function (c) { _removeClass(el, c); }); },
    toggleClass: function (el, cs) { forEach(cs, function (c) { _toggleClass(el, c); }); }
  };
})();

/**
 * Minimal PixelAdmin-compatible global
 */
window.PixelAdmin = {
  isRtl: document.documentElement.getAttribute('dir') === 'rtl',
  isMobile: /iphone|ipad|ipod|android|blackberry|mini|windows\sce|palm/i.test(navigator.userAgent.toLowerCase()),
  isLocalStorageSupported: typeof window.Storage !== 'undefined',
  options: { resizeDelay: 100, storageKeyPrefix: 'px_s_', cookieKeyPrefix: 'px_c_' },
  getScreenSize: function () {
    var w = window.innerWidth || document.documentElement.clientWidth;
    if (w < 768) return 'xs';
    if (w < 992) return 'sm';
    if (w < 1200) return 'md';
    return 'lg';
  },
  storage: {
    _prefix: function (k) { return window.PixelAdmin.options.storageKeyPrefix + k; },
    set: function (k, v) { try { window.localStorage.setItem(this._prefix(k), v); } catch (e) {} },
    get: function (k) { try { return window.localStorage.getItem(this._prefix(k)); } catch (e) { return null; } }
  },
  cookies: {
    _prefix: function (k) { return window.PixelAdmin.options.cookieKeyPrefix + k; },
    set: function (k, v) { document.cookie = encodeURIComponent(this._prefix(k)) + '=' + encodeURIComponent(v); },
    get: function (k) {
      var cookie = ';' + document.cookie + ';';
      var escapedKey = pxUtil.escapeRegExp(encodeURIComponent(this._prefix(k)));
      var found = cookie.match(new RegExp(';\\s*' + escapedKey + '\\s*=\\s*([^;]+)\\s*;'));
      return found ? decodeURIComponent(found[1]) : null;
    }
  }
};
