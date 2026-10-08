var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

// Plugins / PxFile
// --------------------------------------------------

var PxFile = function ($) {
  'use strict';

  /**
   * ------------------------------------------------------------------------
   * Constants
   * ------------------------------------------------------------------------
   */

  var NAME = 'pxFile';
  var DATA_KEY = 'px.file';
  var EVENT_KEY = '.' + DATA_KEY;
  var JQUERY_NO_CONFLICT = $.fn[NAME];

  var ClassName = {
    BROWSE: 'px-file-browse',
    CLEAR: 'px-file-clear',
    HAS_VALUE: 'px-file-has-value'
  };

  var Event = {
    CLICK: 'click' + EVENT_KEY,
    CHANGE: 'change' + EVENT_KEY
  };

  /**
   * ------------------------------------------------------------------------
   * Class Definition
   * ------------------------------------------------------------------------
   */

  var FileCls = function () {
    function FileCls(element) {
      _classCallCheck(this, FileCls);

      this.element = element;
      this.input = $(element).find('.custom-file-input')[0];
      this.control = $(element).find('.custom-file-control')[0];
      this.placeholder = this.control.innerHTML;

      this._checkElement();
      this._checkInput();
      this._checkControl();
      this._setListeners();

      this.update();
    }

    // getters

    _createClass(FileCls, [{
      key: 'browse',


      // public

      value: function browse() {
        $(this.input).trigger('click');
      }
    }, {
      key: 'clear',
      value: function clear() {
        $(this.input).wrap('<form>').parent().on('reset', function (e) {
          e.stopPropagation();
        }).trigger('reset');

        $(this.input).unwrap();
        this.update();
      }
    }, {
      key: 'update',
      value: function update() {
        var value = (this.input.value || '').replace(/\\/g, '/').split('/').pop();

        // Set control placeholder or value
        if (value) {
          $(this.control).text(value);
        } else {
          this.control.innerHTML = this.placeholder;
        }

        pxUtil[value ? 'addClass' : 'removeClass'](this.element, ClassName.HAS_VALUE);
      }
    }, {
      key: 'destroy',
      value: function destroy() {
        this._unsetListeners();

        $(this.element).removeData(DATA_KEY);
      }

      // private

    }, {
      key: '_checkElement',
      value: function _checkElement() {
        if (!pxUtil.hasClass(this.element, 'custom-file')) {
          throw new Error(NAME + ' plugin must be called on a custom file input wrapper.');
        }
      }
    }, {
      key: '_checkInput',
      value: function _checkInput() {
        if (!this.input) {
          throw new Error('File input is not found.');
        }
      }
    }, {
      key: '_checkControl',
      value: function _checkControl() {
        if (!this.control) {
          throw new Error('.custom-file-control element is not found.');
        }
      }
    }, {
      key: '_rejectEvent',
      value: function _rejectEvent(e) {
        if (!e) {
          return;
        }
        e.stopPropagation();
        e.preventDefault();
      }
    }, {
      key: '_setListeners',
      value: function _setListeners() {
        var _this = this;

        $(this.element).find('.' + ClassName.BROWSE).on(this.constructor.Event.CLICK, function (e) {
          _this._rejectEvent(e);
          _this.browse();
          $(_this.input).trigger('focus');
        });

        $(this.element).find('.' + ClassName.CLEAR).on(this.constructor.Event.CLICK, function (e) {
          _this._rejectEvent(e);
          _this.clear();
          $(_this.input).trigger('focus');
        });

        $(this.input).on(this.constructor.Event.CHANGE, $.proxy(this.update, this));
      }
    }, {
      key: '_unsetListeners',
      value: function _unsetListeners() {
        $(this.element).find('.' + ClassName.BROWSE).off(EVENT_KEY);
        $(this.element).find('.' + ClassName.CLEAR).off(EVENT_KEY);
        $(this.input).off(EVENT_KEY);
      }

      // static

    }], [{
      key: '_jQueryInterface',
      value: function _jQueryInterface(method) {
        return this.each(function () {
          var data = $(this).data(DATA_KEY);

          if (!data) {
            data = new FileCls(this);
            $(this).data(DATA_KEY, data);
          }

          if (typeof method === 'string') {
            if (!data[method]) {
              throw new Error('No method named "' + method + '".');
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

    return FileCls;
  }();

  /**
   * ------------------------------------------------------------------------
   * jQuery
   * ------------------------------------------------------------------------
   */

  $.fn[NAME] = FileCls._jQueryInterface;
  $.fn[NAME].Constructor = FileCls;
  $.fn[NAME].noConflict = function () {
    $.fn[NAME] = JQUERY_NO_CONFLICT;
    return FileCls._jQueryInterface;
  };

  return FileCls;
}(jQuery);