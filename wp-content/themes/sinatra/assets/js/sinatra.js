if (!Element.prototype.matches) {
  Element.prototype.matches = Element.prototype.msMatchesSelector || Element.prototype.webkitMatchesSelector;
}
if (!Element.prototype.closest) {
  Element.prototype.closest = function(s) {
    var el = this;
    do {
      if (el.matches(s)) return el;
      el = el.parentElement || el.parentNode;
    } while (el !== null && el.nodeType === 1);
    return null;
  };
}
if (window.NodeList && !NodeList.prototype.forEach) {
  NodeList.prototype.forEach = Array.prototype.forEach;
}
(function() {
  var trim = function(s) {
    return s.replace(/^\s+|\s+$/g, "");
  }, regExp = function(name) {
    return new RegExp("(^|\\s+)" + name + "(\\s+|$)");
  }, forEach = function(list, fn, scope) {
    for (var i = 0; i < list.length; i++) {
      fn.call(scope, list[i]);
    }
  };
  function ClassList(element) {
    this.element = element;
  }
  ClassList.prototype = {
    add: function() {
      forEach(
        arguments,
        function(name) {
          if (!this.contains(name)) {
            this.element.className = trim(this.element.className + " " + name);
          }
        },
        this
      );
    },
    remove: function() {
      forEach(
        arguments,
        function(name) {
          this.element.className = trim(this.element.className.replace(regExp(name), " "));
        },
        this
      );
    },
    toggle: function(name) {
      return this.contains(name) ? (this.remove(name), false) : (this.add(name), true);
    },
    contains: function(name) {
      return regExp(name).test(this.element.className);
    },
    item: function(i) {
      return this.element.className.split(/\s+/)[i] || null;
    },
    // bonus
    replace: function(oldName, newName) {
      this.remove(oldName), this.add(newName);
    }
  };
  if (!("classList" in Element.prototype)) {
    Object.defineProperty(Element.prototype, "classList", {
      get: function() {
        return new ClassList(this);
      }
    });
  }
  if (window.DOMTokenList && !DOMTokenList.prototype.replace) {
    DOMTokenList.prototype.replace = ClassList.prototype.replace;
  }
})();
var sinatraGetIndex = function(el) {
  var i = 0;
  while (el = el.previousElementSibling) {
    i++;
  }
  return i;
};
var sinatraSlideUp = (target, duration = 500) => {
  target.style.transitionProperty = "height, margin, padding";
  target.style.transitionDuration = duration + "ms";
  target.style.boxSizing = "border-box";
  target.style.height = target.offsetHeight + "px";
  target.offsetHeight;
  target.style.overflow = "hidden";
  target.style.height = 0;
  target.style.paddingTop = 0;
  target.style.paddingBottom = 0;
  target.style.marginTop = 0;
  target.style.marginBottom = 0;
  window.setTimeout(() => {
    target.style.display = null;
    target.style.removeProperty("height");
    target.style.removeProperty("padding-top");
    target.style.removeProperty("padding-bottom");
    target.style.removeProperty("margin-top");
    target.style.removeProperty("margin-bottom");
    target.style.removeProperty("overflow");
    target.style.removeProperty("transition-duration");
    target.style.removeProperty("transition-property");
  }, duration);
};
var sinatraSlideDown = (target, duration = 500) => {
  target.style.removeProperty("display");
  let display = window.getComputedStyle(target).display;
  if (display === "none") {
    display = "block";
  }
  target.style.display = display;
  let height = target.offsetHeight;
  target.style.overflow = "hidden";
  target.style.height = 0;
  target.style.paddingTop = 0;
  target.style.paddingBottom = 0;
  target.style.marginTop = 0;
  target.style.marginBottom = 0;
  target.offsetHeight;
  target.style.boxSizing = "border-box";
  target.style.transitionProperty = "height, margin, padding";
  target.style.transitionDuration = duration + "ms";
  target.style.height = height + "px";
  target.style.removeProperty("padding-top");
  target.style.removeProperty("padding-bottom");
  target.style.removeProperty("margin-top");
  target.style.removeProperty("margin-bottom");
  window.setTimeout(() => {
    target.style.removeProperty("height");
    target.style.removeProperty("overflow");
    target.style.removeProperty("transition-duration");
    target.style.removeProperty("transition-property");
  }, duration);
};
var sinatraScrollTo = (function() {
  var defaults = {
    tolerance: 0,
    duration: 800,
    easing: "easeOutQuart",
    container: window,
    callback: function callback() {
    }
  };
  function easeOutQuart(t, b, c, d) {
    t /= d;
    t--;
    return -c * (t * t * t * t - 1) + b;
  }
  function mergeObject(obj1, obj2) {
    var obj3 = {};
    Object.keys(obj1).forEach(function(propertyName) {
      obj3[propertyName] = obj1[propertyName];
    });
    Object.keys(obj2).forEach(function(propertyName) {
      obj3[propertyName] = obj2[propertyName];
    });
    return obj3;
  }
  function kebabCase(val) {
    return val.replace(/([A-Z])/g, function($1) {
      return "-" + $1.toLowerCase();
    });
  }
  function countScrollTop(container) {
    if (container instanceof HTMLElement) {
      return container.scrollTop;
    }
    return container.pageYOffset;
  }
  function sinatraScrollTo2() {
    var options = arguments.length > 0 && arguments[0] !== void 0 ? arguments[0] : {};
    var easeFunctions = arguments.length > 1 && arguments[1] !== void 0 ? arguments[1] : {};
    this.options = mergeObject(defaults, options);
    this.easeFunctions = mergeObject(
      {
        easeOutQuart
      },
      easeFunctions
    );
  }
  sinatraScrollTo2.prototype.registerTrigger = function(dom, callback) {
    var _this = this;
    if (!dom) {
      return;
    }
    var href = dom.getAttribute("href") || dom.getAttribute("data-target");
    var target = href && href !== "#" ? document.getElementById(href.substring(1)) : document.body;
    var options = mergeObject(this.options, _getOptionsFromTriggerDom(dom, this.options));
    if (typeof callback === "function") {
      options.callback = callback;
    }
    var listener = function listener2(e) {
      e.preventDefault();
      _this.move(target, options);
    };
    dom.addEventListener("click", listener, false);
    return function() {
      return dom.removeEventListener("click", listener, false);
    };
  };
  sinatraScrollTo2.prototype.move = function(target) {
    var _this2 = this;
    var options = arguments.length > 1 && arguments[1] !== void 0 ? arguments[1] : {};
    if (target !== 0 && !target) {
      return;
    }
    options = mergeObject(this.options, options);
    var distance = typeof target === "number" ? target : target.getBoundingClientRect().top;
    var from = countScrollTop(options.container);
    var startTime = null;
    var lastYOffset;
    distance -= options.tolerance;
    var loop = function loop2(currentTime) {
      var currentYOffset = countScrollTop(_this2.options.container);
      if (!startTime) {
        startTime = currentTime - 1;
      }
      var timeElapsed = currentTime - startTime;
      if (lastYOffset) {
        if (distance > 0 && lastYOffset > currentYOffset || distance < 0 && lastYOffset < currentYOffset) {
          return options.callback(target);
        }
      }
      lastYOffset = currentYOffset;
      var val = _this2.easeFunctions[options.easing](timeElapsed, from, distance, options.duration);
      options.container.scroll(0, val);
      if (timeElapsed < options.duration) {
        window.requestAnimationFrame(loop2);
      } else {
        options.container.scroll(0, distance + from);
        options.callback(target);
      }
    };
    window.requestAnimationFrame(loop);
  };
  sinatraScrollTo2.prototype.addEaseFunction = function(name, fn) {
    this.easeFunctions[name] = fn;
  };
  function _getOptionsFromTriggerDom(dom, options) {
    var domOptions = {};
    Object.keys(options).forEach(function(key) {
      var value = dom.getAttribute("data-mt-".concat(kebabCase(key)));
      if (value) {
        domOptions[key] = isNaN(value) ? value : parseInt(value, 10);
      }
    });
    return domOptions;
  }
  return sinatraScrollTo2;
})();
var sinatraGetParents = (elem, selector) => {
  if (!Element.prototype.matches) {
    Element.prototype.matches = Element.prototype.matchesSelector || Element.prototype.mozMatchesSelector || Element.prototype.msMatchesSelector || Element.prototype.oMatchesSelector || Element.prototype.webkitMatchesSelector || function(s) {
      var matches = (this.document || this.ownerDocument).querySelectorAll(s), i = matches.length;
      while (--i >= 0 && matches.item(i) !== this) {
      }
      return i > -1;
    };
  }
  var parents = [];
  for (; elem && elem !== document; elem = elem.parentNode) {
    if (selector) {
      if (elem.matches(selector)) {
        parents.push(elem);
      }
    } else {
      parents.push(elem);
    }
  }
  return parents;
};
(function() {
  if (typeof window.CustomEvent === "function") return false;
  function CustomEvent2(event, params) {
    params = params || { bubbles: false, cancelable: false, detail: void 0 };
    var evt = document.createEvent("CustomEvent");
    evt.initCustomEvent(event, params.bubbles, params.cancelable, params.detail);
    return evt;
  }
  CustomEvent2.prototype = window.Event.prototype;
  window.CustomEvent = CustomEvent2;
})();
var sinatraTriggerEvent = function(el, typeArg) {
  var customEventInit = arguments.length > 2 && arguments[2] !== void 0 ? arguments[2] : {};
  var event = new CustomEvent(typeArg, customEventInit);
  el.dispatchEvent(event);
};
(function() {
  var sinatraScrollButton = document.querySelector("#si-scroll-top");
  var pageWrapper = document.getElementById("page");
  var sinatraSmartSubmenus = () => {
    if (document.body.classList.contains("sinatra-is-mobile")) {
      return;
    }
    var el, elPosRight, elPosLeft, winRight;
    winRight = window.innerWidth;
    document.querySelectorAll(".sub-menu").forEach((item) => {
      item.style.visibility = "visible";
      const rect = item.getBoundingClientRect();
      elPosLeft = rect.left + window.pageXOffset;
      elPosRight = elPosLeft + rect.width;
      item.removeAttribute("style");
      if (elPosRight > winRight) {
        item.closest("li").classList.add("opens-left");
      } else if (elPosLeft < 0) {
        item.closest("li").classList.add("opens-right");
      }
    });
  };
  var sinatraDebounce = (fn) => {
    var timeout;
    return function() {
      var context = this;
      var args = arguments;
      if (timeout) {
        window.cancelAnimationFrame(timeout);
      }
      timeout = window.requestAnimationFrame(function() {
        fn.apply(this, args);
      });
    };
  };
  var sinatraScrollTopButton = () => {
    if (null === sinatraScrollButton) {
      return;
    }
    if (window.pageYOffset > 450 || document.documentElement.scrollTop > 450) {
      sinatraScrollButton.classList.add("si-visible");
    } else {
      sinatraScrollButton.classList.remove("si-visible");
    }
  };
  var sinatraStickyHeader = () => {
    if (!sinatra_vars["sticky-header"]["enabled"]) {
      return;
    }
    var header = document.getElementById("sinatra-header");
    var headerInner = document.getElementById("sinatra-header-inner");
    var wpadminbar = document.getElementById("wpadminbar");
    if (document.body.classList.contains("sinatra-header-layout-3")) {
      header = document.querySelector("#sinatra-header .si-nav-container");
      headerInner = document.querySelector("#sinatra-header .si-nav-container .si-container");
    }
    if (window.outerWidth <= sinatra_vars["responsive-breakpoint"]) {
      var header = document.getElementById("sinatra-header");
      var headerInner = document.getElementById("sinatra-header-inner");
    }
    if (null === header || null === headerInner) {
      return;
    }
    var stickyPosition = header.getBoundingClientRect().top;
    var sticky = stickyPosition - tolerance <= 0;
    var tolerance;
    var stickyPlaceholder;
    if (null === wpadminbar) {
      tolerance = 0;
    } else if (window.outerWidth <= 600) {
      tolerance = 0;
    } else {
      tolerance = wpadminbar.getBoundingClientRect().height;
    }
    var checkPosition = function() {
      if (null === wpadminbar) {
        tolerance = 0;
      } else if (window.outerWidth <= 600) {
        tolerance = 0;
      } else {
        tolerance = wpadminbar.getBoundingClientRect().height;
      }
      stickyPosition = header.getBoundingClientRect().top;
      sticky = stickyPosition - tolerance <= 0;
      maybeStickHeader();
    };
    var maybeStickHeader = function() {
      let hideOn = sinatra_vars["sticky-header"]["hide_on"];
      if (hideOn.includes("desktop") && window.innerWidth >= 992) {
        sticky = false;
      }
      if (hideOn.includes("tablet") && window.innerWidth >= 481 && window.innerWidth < 992) {
        sticky = false;
      }
      if (hideOn.includes("mobile") && window.innerWidth < 481) {
        sticky = false;
      }
      if (sticky) {
        if (!document.body.classList.contains("si-sticky-header")) {
          stickyPlaceholder = document.createElement("div");
          stickyPlaceholder.setAttribute("id", "si-sticky-placeholder");
          stickyPlaceholder.style.height = headerInner.getBoundingClientRect().height + "px";
          header.appendChild(stickyPlaceholder);
          document.body.classList.add("si-sticky-header");
          document.body.style.setProperty("--si-sticky-h-offset", header.offsetHeight + 20 + "px");
        }
      } else {
        if (document.body.classList.contains("si-sticky-header")) {
          document.body.classList.remove("si-sticky-header");
          document.getElementById("si-sticky-placeholder").remove();
        }
        document.body.style.removeProperty("--si-sticky-h-offset");
      }
    };
    if ("true" !== header.getAttribute("data-scroll-listener")) {
      window.addEventListener("scroll", function() {
        sinatraDebounce(checkPosition());
      });
      header.setAttribute("data-scroll-listener", "true");
    }
    if ("true" !== header.getAttribute("data-resize-listener")) {
      window.addEventListener("resize", function() {
        sinatraDebounce(checkPosition());
      });
      header.setAttribute("data-resize-listener", "true");
    }
    sinatraTriggerEvent(window, "scroll");
  };
  var sinatraSmoothScroll = () => {
    const scrollTo = new sinatraScrollTo({
      tolerance: null === document.getElementById("wpadminbar") ? 0 : document.getElementById("wpadminbar").getBoundingClientRect().height
    });
    const scrollTriggers = document.getElementsByClassName("si-smooth-scroll");
    for (var i = 0; i < scrollTriggers.length; i++) {
      scrollTo.registerTrigger(scrollTriggers[i]);
    }
  };
  var sinatraMenuAccessibility = () => {
    if (!document.body.classList.contains("si-menu-accessibility")) {
      return;
    }
    document.querySelectorAll(".sinatra-nav").forEach((menu) => {
      menu.querySelectorAll("ul").forEach((subMenu) => {
        subMenu.parentNode.setAttribute("aria-haspopup", "true");
      });
      menu.querySelectorAll("a").forEach((link) => {
        link.addEventListener("focus", sinatraMenuFocus, true);
        link.addEventListener("blur", sinatraMenuFocus, true);
      });
    });
  };
  function sinatraMenuFocus() {
    var self = this;
    while (!self.classList.contains("sinatra-nav")) {
      if ("li" === self.tagName.toLowerCase()) {
        if (!self.classList.contains("hovered")) {
          self.classList.add("hovered");
        } else {
          self.classList.remove("hovered");
        }
      }
      self = self.parentElement;
    }
  }
  var sinatraKeyboardFocus = () => {
    document.body.addEventListener("keydown", function(e) {
      document.body.classList.add("using-keyboard");
    });
    document.body.addEventListener("mousedown", function(e) {
      document.body.classList.remove("using-keyboard");
    });
  };
  var sinatraCalcScreenWidth = () => {
    document.body.style.setProperty("--si-screen-width", document.body.clientWidth + "px");
  };
  var sinatraDropdownDelay = () => {
    var hoverTimer = null;
    document.querySelectorAll(".sinatra-nav .menu-item-has-children").forEach((item) => {
      item.addEventListener("mouseenter", function() {
        document.querySelectorAll(".menu-item-has-children").forEach((subitem) => {
          subitem.classList.remove("hovered");
        });
      });
    });
    document.querySelectorAll(".sinatra-nav .menu-item-has-children").forEach((item) => {
      item.addEventListener("mouseleave", function() {
        item.classList.add("hovered");
        if (null !== hoverTimer) {
          clearTimeout(hoverTimer);
          hoverTimer = null;
        }
        hoverTimer = setTimeout(() => {
          item.classList.remove("hovered");
          item.querySelectorAll(".menu-item-has-children").forEach((childItem) => {
            childItem.classList.remove("hovered");
          });
        }, 700);
      });
    });
  };
  var sinatraCartDropdownDelay = () => {
    var hoverTimer = null;
    document.querySelectorAll(".si-header-widget__cart .si-widget-wrapper").forEach((item) => {
      item.addEventListener("mouseenter", function() {
        item.classList.remove("dropdown-visible");
      });
    });
    document.querySelectorAll(".si-header-widget__cart .si-widget-wrapper").forEach((item) => {
      item.addEventListener("mouseleave", function() {
        item.classList.add("dropdown-visible");
        if (null !== hoverTimer) {
          clearTimeout(hoverTimer);
          hoverTimer = null;
        }
        hoverTimer = setTimeout(() => {
          item.classList.remove("dropdown-visible");
        }, 700);
      });
    });
  };
  var sinatraHeaderSearch = () => {
    var searchButton = document.querySelectorAll(".si-search");
    if (0 === searchButton.length) {
      return;
    }
    searchButton.forEach((item) => {
      item.addEventListener("click", (e) => {
        e.preventDefault();
        if (item.classList.contains("sinatra-active")) {
          close_search(item);
        } else {
          show_search(item);
        }
      });
    });
    var show_search = function(item) {
      document.body.classList.add("si-search-visible");
      setTimeout(function() {
        item.classList.add("sinatra-active");
        if (null !== item.nextElementSibling && null !== item.nextElementSibling.querySelector("input")) {
          item.nextElementSibling.querySelector("input").focus();
          item.nextElementSibling.querySelector("input").select();
        }
      }, 100);
      document.addEventListener("keydown", esc_close_search);
      pageWrapper.addEventListener("click", outside_close_search);
    };
    var close_search = function(item) {
      document.body.classList.remove("si-search-visible");
      item.classList.remove("sinatra-active");
      document.removeEventListener("keydown", esc_close_search);
      pageWrapper.removeEventListener("click", outside_close_search);
    };
    var esc_close_search = function(e) {
      if (e.keyCode == 27) {
        document.querySelectorAll(".si-search").forEach((item) => {
          close_search(item);
        });
      }
    };
    var outside_close_search = function(e) {
      if (null === e.target.closest(".si-search-container") && null === e.target.closest(".si-search")) {
        document.querySelectorAll(".si-search").forEach((item) => {
          close_search(item);
        });
      }
    };
  };
  var sinatraMobileMenu = () => {
    var page = pageWrapper, nav = document.querySelector("#sinatra-header-inner .sinatra-nav"), current;
    document.querySelectorAll(".si-mobile-nav > button").forEach((item) => {
      item.addEventListener(
        "click",
        function(e) {
          e.preventDefault();
          if (document.body.parentNode.classList.contains("is-mobile-menu-active")) {
            close_menu();
          } else {
            show_menu();
          }
        },
        false
      );
    });
    var show_menu = function(e) {
      document.body.parentNode.classList.add("is-mobile-menu-active");
      document.addEventListener("keyup", esc_close_menu);
      if (null !== page) {
        page.addEventListener("click", outside_close_menu);
      }
      document.querySelectorAll("#sinatra-header .sinatra-nav").forEach((item) => {
        item.addEventListener("click", submenu_toggle);
      });
      sinatraSlideDown(nav, 350);
    };
    var close_menu = function(e) {
      document.body.parentNode.classList.remove("is-mobile-menu-active");
      document.removeEventListener("keyup", esc_close_menu);
      if (null !== page) {
        page.removeEventListener("click", outside_close_menu);
      }
      document.querySelectorAll("#sinatra-header .sinatra-nav > ul > .si-open").forEach((item) => {
        submenu_display_toggle(item);
      });
      nav.style.display = null;
      nav.querySelectorAll(".hovered").forEach((li) => {
        li.classList.remove("hovered");
      });
      if (document.body.classList.contains("sinatra-is-mobile")) {
        document.querySelectorAll("#sinatra-header .sinatra-nav").forEach((item) => {
          item.removeEventListener("click", submenu_toggle);
        });
        sinatraSlideUp(nav, 250);
      }
    };
    var outside_close_menu = function(e) {
      if (null === e.target.closest(".si-hamburger") && null === e.target.closest(".site-navigation")) {
        close_menu();
      }
    };
    var esc_close_menu = function(e) {
      if (e.keyCode == 27) {
        close_menu();
      }
    };
    var submenu_toggle = function(e) {
      if (e.target.parentElement.querySelectorAll(".sub-menu").length) {
        e.preventDefault();
        submenu_display_toggle(e.target.parentElement);
      }
    };
    var submenu_display_toggle = (current2) => {
      if (current2.classList.contains("si-open")) {
        current2.classList.remove("si-open");
        current2.querySelectorAll(".sub-menu").forEach((submenu) => {
          submenu.style.display = null;
        });
        current2.querySelectorAll("li").forEach((item) => {
          item.classList.remove("si-open");
          item.querySelectorAll(".sub-menu").forEach((submenu) => {
            submenu.style.display = null;
          });
        });
      } else {
        current2.querySelectorAll(".sub-menu").forEach((submenu) => {
          if (current2 === submenu.parentElement) {
            submenu.style.display = "block";
          }
        });
        current2.classList.add("si-open");
      }
    };
    document.addEventListener("sinatra-close-mobile-menu", close_menu);
  };
  var sinatraPreloader = (timeout = 0) => {
    var preloader = document.getElementById("si-preloader");
    if (null === preloader) {
      return;
    }
    var delay = 250;
    var hide_preloader = () => {
      if (document.body.classList.contains("si-loaded")) {
        return;
      }
      document.body.classList.add("si-loading");
      setTimeout(function() {
        document.body.classList.replace("si-loading", "si-loaded");
        sinatraTriggerEvent(document.body, "si-preloader-done");
      }, delay);
    };
    if (timeout > 0) {
      setTimeout(function() {
        hide_preloader();
      }, timeout);
    } else {
      hide_preloader();
    }
    return false;
  };
  var sinatraToggleComments = () => {
    if (!document.body.classList.contains("sinatra-has-comments-toggle")) {
      return;
    }
    if (null == document.getElementById("sinatra-comments-toggle")) {
      return;
    }
    var toggleComments = (e) => {
      if ("undefined" !== typeof e) {
        e.preventDefault();
      }
      if (document.body.classList.contains("comments-visible")) {
        document.body.classList.remove("comments-visible");
        document.getElementById("sinatra-comments-toggle").querySelector("span").textContent = sinatra_vars.strings.comments_toggle_show;
      } else {
        document.body.classList.add("comments-visible");
        document.getElementById("sinatra-comments-toggle").querySelector("span").textContent = sinatra_vars.strings.comments_toggle_hide;
      }
    };
    if (null !== document.getElementById("sinatra-comments-toggle") && (-1 !== location.href.indexOf("#comment") || -1 !== location.href.indexOf("respond"))) {
      toggleComments();
    }
    document.getElementById("sinatra-comments-toggle").addEventListener("click", toggleComments);
  };
  var sinatraCommentsClick = () => {
    var commentsLink = document.querySelector(".single .comments-link");
    if (null === commentsLink) {
      return;
    }
    commentsLink.addEventListener("click", function(e) {
      if (document.body.classList.contains("sinatra-has-comments-toggle") && !document.body.classList.contains("comments-visible")) {
        document.getElementById("sinatra-comments-toggle").click();
      }
    });
  };
  var sinatraCheckMobileMenu = () => {
    if (window.innerWidth <= sinatra_vars["responsive-breakpoint"]) {
      document.body.classList.add("sinatra-is-mobile");
    } else {
      if (document.body.classList.contains("sinatra-is-mobile")) {
        document.body.classList.remove("sinatra-is-mobile");
        sinatraTriggerEvent(document, "sinatra-close-mobile-menu");
      }
    }
  };
  document.addEventListener("DOMContentLoaded", function() {
    sinatraPreloader(5e3);
    sinatraMenuAccessibility();
    sinatraKeyboardFocus();
    sinatraScrollTopButton();
    sinatraSmoothScroll();
    sinatraDropdownDelay();
    sinatraToggleComments();
    sinatraHeaderSearch();
    sinatraMobileMenu();
    sinatraCheckMobileMenu();
    sinatraSmartSubmenus();
    sinatraCommentsClick();
    sinatraCartDropdownDelay();
    sinatraStickyHeader();
    sinatraCalcScreenWidth();
  });
  window.addEventListener("load", function() {
    sinatraPreloader();
  });
  window.addEventListener("scroll", function() {
    sinatraDebounce(sinatraScrollTopButton());
  });
  window.addEventListener("resize", function() {
    sinatraDebounce(sinatraSmartSubmenus());
    sinatraDebounce(sinatraCheckMobileMenu());
    sinatraDebounce(sinatraCalcScreenWidth());
  });
  sinatraTriggerEvent(document.body, "si-ready");
  window.sinatra = window.sinatra || {};
  window.sinatra.preloader = sinatraPreloader;
  window.sinatra.stickyHeader = sinatraStickyHeader;
})();
