var sinatraHoverSlider = function(el) {
  var current = 0, spinner = el.querySelector(".si-spinner");
  var hideSpinner = function() {
    spinner.classList.remove("visible");
    setTimeout(function() {
      spinner.style.display = "none";
    }, 300);
    el.querySelector(".hover-slider-backgrounds").classList.add("loaded");
  };
  el.querySelector(".hover-slide-bg").classList.add("active");
  el.querySelectorAll(".hover-slider-backgrounds .hover-slide-bg").forEach((item, i) => {
    item.style.backgroundImage = "url(" + item.getAttribute("data-background") + ")";
    el.querySelector(".hover-slider-items > div:nth-child(" + (i + 1) + ")").style.setProperty("--bg-image", 'url("' + item.getAttribute("data-background") + '")');
    item.removeAttribute("data-background");
  });
  imagesLoaded(el.querySelectorAll(".hover-slider-backgrounds"), { background: ".hover-slide-bg" }, function() {
    var preloader = document.getElementById("si-preloader");
    if (null !== preloader && !document.body.classList.contains("si-loaded")) {
      document.body.addEventListener("si-preloader-done", function() {
        setTimeout(function() {
          hideSpinner();
        }, 300);
      });
    } else {
      setTimeout(function() {
        hideSpinner();
      }, 300);
    }
  });
  el.querySelectorAll(".hover-slider-item-wrapper").forEach((item) => {
    item.addEventListener("mouseenter", function() {
      if (current !== sinatraGetIndex(item)) {
        current = sinatraGetIndex(item);
        el.querySelectorAll(".hover-slide-bg").forEach((item2, i) => {
          item2.classList.remove("active");
          if (i === current) {
            item2.classList.add("active");
          }
        });
      }
    });
  });
  return el;
};
(function() {
  document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".si-hover-slider").forEach((item) => {
      sinatraHoverSlider(item);
    });
  });
})();
