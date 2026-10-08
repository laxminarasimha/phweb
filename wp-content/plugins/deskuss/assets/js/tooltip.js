// Extensions / Tooltip
// --------------------------------------------------

(function ($) {
  'use strict';

  if (!$.fn.tooltip) {
    throw new Error('tooltip.js required.');
  }

  var STATE_PARAM = 'data-state';

  var bsTooltipGetOptions = $.fn.tooltip.Constructor.prototype.getOptions;
  var bsTooltipSetContent = $.fn.tooltip.Constructor.prototype.setContent;

  $.fn.tooltip.Constructor.prototype.getOptions = function (options) {
    var result = bsTooltipGetOptions.call(this, options);
    var _isRtl = $('html').attr('dir') === 'rtl';

    if (_isRtl && result.placement === 'left') {
      result.placement = 'right';
    } else if (_isRtl && result.placement === 'right') {
      result.placement = 'left';
    }

    return result;
  };

  $.fn.tooltip.Constructor.prototype.setContent = function () {
    var state = this.$element.attr(STATE_PARAM);

    if (state) {
      $(this.tip()).addClass('tooltip-' + state.replace(/[^a-z0-9_-]/ig, ''));
    }

    bsTooltipSetContent.call(this);
  };
})(jQuery);