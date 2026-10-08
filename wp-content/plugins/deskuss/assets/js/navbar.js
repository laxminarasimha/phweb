/**
 * Lightweight Sidebar Navigation
 * Replaces PixelAdmin's PxNav + PxNavbar + PxFooter
 * Provides same jQuery plugins: $.fn.pxNav, $.fn.pxNavbar, $.fn.pxFooter
 */

(function ($) {
  'use strict';

  var CLS_NAV = 'px-nav';
  var CLS_CONTENT = 'px-nav-content';
  var CLS_ITEM = 'px-nav-item';
  var CLS_DROPDOWN = 'px-nav-dropdown';
  var CLS_DROPDOWN_MENU = 'px-nav-dropdown-menu';
  var CLS_OPEN = 'px-open';
  var CLS_SHOW = 'px-show';
  var CLS_EXPAND = 'px-nav-expand';
  var CLS_DIMMER = 'px-nav-dimmer';
  var CLS_TOGGLE = 'px-nav-toggle';
  var CLS_SCROLL_AREA = 'px-nav-scrollable-area';

  function Sidebar(element) {
    this.element = element;
    this.$element = $(element);
    this.$content = this.$element.find('.' + CLS_CONTENT);
    this._setupScrollbar();
    this._setupDimmer();
    this._bindEvents();
    this._autoOpenActive();
  }

  Sidebar.prototype._setupScrollbar = function () {
    // Wrap content in scrollable area for perfect-scrollbar
    if (!this.$content.parent().hasClass(CLS_SCROLL_AREA)) {
      this.$content.wrap('<div class="' + CLS_SCROLL_AREA + '"></div>');
    }
    this.$scrollArea = this.$content.parent();
    if ($.fn.perfectScrollbar) {
      this.$scrollArea.perfectScrollbar({ suppressScrollX: true });
    }
  };

  Sidebar.prototype._setupDimmer = function () {
    var $parent = this.$element.parent();
    if (!$parent.find('> .' + CLS_DIMMER).length) {
      $parent.append('<div class="' + CLS_DIMMER + '"></div>');
    }
    this.$dimmer = $parent.find('> .' + CLS_DIMMER);
  };

  Sidebar.prototype._bindEvents = function () {
    var self = this;

    this.$element.on('click.px-nav', '.' + CLS_TOGGLE, function (e) {
      e.preventDefault();
      self.toggle();
    });

    this.$element.on('click.px-nav', '.' + CLS_DROPDOWN + ' > a', function (e) {
      var $dropdown = $(this).parent();
      var $menu = $dropdown.children('.' + CLS_DROPDOWN_MENU);
      if ($menu.length) {
        e.preventDefault();
        self.toggleDropdown($dropdown[0]);
      }
    });

    this.$dimmer.on('click.px-nav', function () {
      self.collapse();
    });

    $(window).on('resize.px-nav', function () {
      if ($(window).width() >= 992 && self.$element.hasClass(CLS_EXPAND)) {
        self.collapse();
      }
      if ($.fn.perfectScrollbar) {
        self.$scrollArea.perfectScrollbar('update');
      }
    });
  };

  Sidebar.prototype.toggle = function () {
    this[this.$element.hasClass(CLS_EXPAND) ? 'collapse' : 'expand']();
  };

  Sidebar.prototype.expand = function () {
    this.$element.parent().find('> .' + CLS_EXPAND).not(this.element).each(function () {
      $(this).removeClass(CLS_EXPAND);
    });
    this.$element.addClass(CLS_EXPAND);
    this.$dimmer.addClass(CLS_SHOW);
    if ($.fn.perfectScrollbar) {
      this.$scrollArea.perfectScrollbar('update');
    }
  };

  Sidebar.prototype.collapse = function () {
    this.$element.removeClass(CLS_EXPAND);
    this.$dimmer.removeClass(CLS_SHOW);
    this.closeAllDropdowns();
  };

  Sidebar.prototype.toggleDropdown = function (el) {
    var $el = $(el);
    if ($el.hasClass(CLS_OPEN)) {
      this.closeDropdown(el);
    } else {
      this.openDropdown(el);
    }
  };

  Sidebar.prototype.openDropdown = function (el) {
    var $el = $(el);
    if ($el.hasClass(CLS_OPEN)) return;
    $el.siblings('.' + CLS_DROPDOWN + '.' + CLS_OPEN).removeClass(CLS_OPEN);
    $el.addClass(CLS_OPEN);
    if ($.fn.perfectScrollbar) {
      this.$scrollArea.perfectScrollbar('update');
    }
  };

  Sidebar.prototype.closeDropdown = function (el) {
    $(el).removeClass(CLS_OPEN);
  };

  Sidebar.prototype.closeAllDropdowns = function () {
    this.$element.find('.' + CLS_DROPDOWN + '.' + CLS_OPEN).removeClass(CLS_OPEN);
  };

  Sidebar.prototype._autoOpenActive = function () {
    this.$element.find('.' + CLS_DROPDOWN_MENU + ' .' + CLS_ITEM + '.active').each(function () {
      $(this).closest('.' + CLS_DROPDOWN).addClass(CLS_OPEN);
    });
  };

  // jQuery plugin
  $.fn.pxNav = function () {
    return this.each(function () {
      var $this = $(this);
      if (!$this.data('px.nav')) {
        $this.data('px.nav', new Sidebar(this));
      }
    });
  };

  // Auto-init on data-toggle
  $(document).on('click.px-nav-data-api', '[data-toggle="px-nav"]', function (e) {
    e.preventDefault();
    var $target = $($(this).data('target'));
    if (!$target.length) {
      $target = $(this).closest('.' + CLS_NAV);
    }
    if ($target.length) {
      $target.pxNav();
      var sidebar = $target.data('px.nav');
      if (sidebar) sidebar.toggle();
    }
  });

})(jQuery);

/**
 * Navbar plugin (replaces PxNavbar)
 * Bootstrap 3 handles collapse; we just add scrollbar support
 */
(function ($) {
  'use strict';

  function Navbar(element) {
    this.element = element;
    this.$element = $(element);
    this.$collapse = this.$element.find('.navbar-collapse');
    this._bindEvents();
  }

  Navbar.prototype._bindEvents = function () {
    var self = this;
    this.$element.on('shown.bs.collapse', function () {
      self._enableScrollbar();
    }).on('hidden.bs.collapse', function () {
      self._disableScrollbar();
    });
  };

  Navbar.prototype._enableScrollbar = function () {
    if (!$.fn.perfectScrollbar) return;
    var $inner = this.$collapse.find('.px-navbar-collapse-inner');
    if (!$inner.length) {
      $inner = $('<div class="px-navbar-collapse-inner"></div>').append(this.$collapse.children());
      this.$collapse.append($inner);
    }
    $inner.perfectScrollbar({ suppressScrollX: true });
  };

  Navbar.prototype._disableScrollbar = function () {
    if (!$.fn.perfectScrollbar) return;
    var $inner = this.$collapse.find('.px-navbar-collapse-inner');
    if ($inner.length) {
      $inner.perfectScrollbar('destroy');
      $inner.children().appendTo(this.$collapse);
      $inner.remove();
    }
  };

  $.fn.pxNavbar = function () {
    return this.each(function () {
      var $this = $(this);
      if (!$this.data('px.navbar')) {
        $this.data('px.navbar', new Navbar(this));
      }
    });
  };

  // Auto-init
  $(document).on('click.px-navbar-data-api', '.px-navbar [data-toggle="collapse"]', function () {
    var $navbar = $(this).closest('.px-navbar');
    if ($navbar.length) {
      $navbar.pxNavbar();
    }
  });

})(jQuery);

/**
 * Footer plugin (replaces PxFooter)
 */
(function ($) {
  'use strict';

  function Footer(element) {
    this.element = element;
    this.$element = $(element);
    this._bindEvents();
    this.update();
  }

  Footer.prototype._bindEvents = function () {
    var self = this;
    $(window).on('resize.px-footer', function () {
      self.update();
    });
  };

  Footer.prototype.update = function () {
    if (!this.$element.hasClass('px-footer-bottom') && !this.$element.hasClass('px-footer-fixed')) {
      return;
    }
    var $content = this.$element.parent().find('> .px-content');
    if ($content.length) {
      $content.css('padding-bottom', this.$element.outerHeight() + 20 + 'px');
    }
  };

  $.fn.pxFooter = function () {
    return this.each(function () {
      var $this = $(this);
      if (!$this.data('px.footer')) {
        $this.data('px.footer', new Footer(this));
      }
    });
  };

})(jQuery);
