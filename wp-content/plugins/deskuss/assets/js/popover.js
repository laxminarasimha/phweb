// Extensions / Popover
// --------------------------------------------------

(function ($) {
  'use strict';

  if (!$.fn.popover) {
    throw new Error('popover.js required.');
  }

  var STATE_PARAM = 'data-state';
  var STYLE_PARAM = 'data-style';

  var bsPopoverGetOptions = $.fn.popover.Constructor.prototype.getOptions;
  var bsPopoverSetContent = $.fn.popover.Constructor.prototype.setContent;

  $.fn.popover.Constructor.prototype.getOptions = function (options) {
    var result = bsPopoverGetOptions.call(this, options);
    var _isRtl = $('html').attr('dir') === 'rtl';

    if (_isRtl && result.placement === 'left') {
      result.placement = 'right';
    } else if (_isRtl && result.placement === 'right') {
      result.placement = 'left';
    }

    return result;
  };

  $.fn.popover.Constructor.prototype.setContent = function () {
    var $element = this.$element;
    var $tip = $(this.tip());
    var state = $element.attr(STATE_PARAM);
    var style = ($element.attr(STYLE_PARAM) || '').toLowerCase().split(' ');

    if (state) {
      $tip.addClass('popover-' + state.replace(/[^a-z0-9_-]/ig, ''));
    }

    if (style.indexOf('dark') !== -1) {
      $tip.addClass('popover-dark');
    }

    if (style.indexOf('colorful') !== -1) {
      $tip.addClass('popover-colorful');
    }

    bsPopoverSetContent.call(this);
  };
})(jQuery);