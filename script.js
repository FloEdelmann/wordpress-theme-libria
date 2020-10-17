var skrollrActive = null;

function initSkrollr() {
  var width = window.innerWidth
    || document.documentElement.clientWidth
    || document.getElementsByTagName('body')[0].clientWidth;
    
  if (width > 600 && skrollrActive != true) {
    skrollr.init({
      forceHeight: false,
      smoothScrolling: false
    });
    skrollrActive = true;
  }
  else if (width <= 600 && skrollrActive != false) {
    // deactivate skrollr
    var s = skrollr.init();
    s.destroy();
    skrollrActive = false;
  }
}

window.addEventListener("resize", initSkrollr);
window.addEventListener("load", initSkrollr);

