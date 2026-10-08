var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

// Plugins / PxFooter
// --------------------------------------------------

var PxFooter = function ($) {
  'use strict';

  /**
   * ------------------------------------------------------------------------
   * Constants
   * ------------------------------------------------------------------------
   */

  var NAME = 'pxFooter';
  var DATA_KEY = 'px.footer';
  var EVENT_KEY = '.' + DATA_KEY;
  var JQUERY_NO_CONFLICT = $.fn[NAME];

  var ClassName = {
    CONTENT: 'px-content',
    BOTTOM: 'px-footer-bottom',
    FIXED: 'px-footer-fixed'
  };

  var Event = {
    RESIZE: 'resize' + EVENT_KEY,
    SCROLL: 'scroll' + EVENT_KEY,
    NAV_EXPANDED: 'expanded.px.nav',
    NAV_COLLAPSED: 'collapsed.px.nav',
    DROPDOWN_OPENED: 'dropdown-opened.px.nav',
    DROPDOWN_CLOSED: 'dropdown-closed.px.nav'
  };

  /**
   * ------------------------------------------------------------------------
   * Class Definition
   * ------------------------------------------------------------------------
   */

  var Footer = function () {
    function Footer(element) {
      _classCallCheck(this, Footer);

      this.uniqueId = pxUtil.generateUniqueId();
      this.element = element;
      this.parent = element.parentNode;

      this._setListeners();

      this.update();
    }

    // getters

    _createClass(Footer, [{
      key: 'update',


      // public

      value: function update() {
        if (this.parent === document.body) {
          this._curScreenSize = window.PixelAdmin.getScreenSize();
          this._updateBodyMinHeight();
        }

        var content = $(this.parent).find('> .' + ClassName.CONTENT)[0];

        if (!pxUtil.hasClass(this.element, ClassName.BOTTOM) && !pxUtil.hasClass(this.element, ClassName.FIXED)) {
          content.style.paddingBottom = content.setAttribute('style', (content.getAttribute('style') || '').replace(/\s*padding-bottom:\s*\d+px\s*;?/i));
          return;
        }

        $(this.parent).find('> .' + ClassName.CONTENT)[0].style.paddingBottom = $(this.element).outerHeight() + 20 + 'px';
      }
    }, {
      key: 'destroy',
      value: function destroy() {
        this._unsetListeners();

        $(this.element).removeData(DATA_KEY);
      }

      // private

    }, {
      key: '_updateBodyMinHeight',
      value: function _updateBodyMinHeight() {
        if (document.body.style.minHeight) {
          document.body.style.minHeight = null;
        }

        if (this._curScreenSize !== 'lg' && this._curScreenSize !== 'xl' || !pxUtil.hasClass(this.element, ClassName.BOTTOM) || $(document.body).height() >= document.body.scrollHeight) {
          return;
        }

        document.body.style.minHeight = document.body.scrollHeight + 'px';
      }
    }, {
      key: '_setListeners',
      value: function _setListeners() {
        $(window).on(this.constructor.Event.RESIZE + '.' + this.uniqueId, $.proxy(this.update, this)).on(this.constructor.Event.SCROLL + '.' + this.uniqueId, $.proxy(this._updateBodyMinHeight, this)).on(this.constructor.Event.NAV_EXPANDED + '.' + this.uniqueId + ' ' + this.constructor.Event.NAV_COLLAPSED + '.' + this.uniqueId, '.px-nav', $.proxy(this._updateBodyMinHeight, this));

        if (this.parent === document.body) {
          $('.px-nav').on(this.constructor.Event.DROPDOWN_OPENED + '.' + this.uniqueId + ' ' + this.constructor.Event.DROPDOWN_CLOSED + '.' + this.uniqueId, '.px-nav-dropdown', $.proxy(this._updateBodyMinHeight, this));
        }
      }
    }, {
      key: '_unsetListeners',
      value: function _unsetListeners() {
        $(window).off(this.constructor.Event.RESIZE + '.' + this.uniqueId + ' ' + this.constructor.Event.SCROLL + '.' + this.uniqueId).off(this.constructor.Event.NAV_EXPANDED + '.' + this.uniqueId + ' ' + this.constructor.Event.NAV_COLLAPSED + '.' + this.uniqueId);

        $('.px-nav').off(this.constructor.Event.DROPDOWN_OPENED + '.' + this.uniqueId + ' ' + this.constructor.Event.DROPDOWN_CLOSED + '.' + this.uniqueId);
      }

      // static

    }], [{
      key: '_jQueryInterface',
      value: function _jQueryInterface(method) {
        return this.each(function () {
          var data = $(this).data(DATA_KEY);

          if (!data) {
            data = new Footer(this);
            $(this).data(DATA_KEY, data);
          }

          if (typeof method === 'string') {
            if (!data[method]) {
              throw new Error('No method named "' + method + '"');
            }
            data[method]();
          }
        });
      }
    }, {
      key: 'NAME',
      get: function get() {
        return NAME;
      }
    }, {
      key: 'DATA_KEY',
      get: function get() {
        return DATA_KEY;
      }
    }, {
      key: 'Event',
      get: function get() {
        return Event;
      }
    }, {
      key: 'EVENT_KEY',
      get: function get() {
        return EVENT_KEY;
      }
    }]);

    return Footer;
  }();

  /**
   * ------------------------------------------------------------------------
   * jQuery
   * ------------------------------------------------------------------------
   */

  $.fn[NAME] = Footer._jQueryInterface;
  $.fn[NAME].Constructor = Footer;
  $.fn[NAME].noConflict = function () {
    $.fn[NAME] = JQUERY_NO_CONFLICT;
    return Footer._jQueryInterface;
  };

  return Footer;
}(jQuery);