// Shared page transition: Fade + blur
(function () {
  function onReady() {
    var el = document.getElementById('page');
    if (!el) return;
    // allow initial styles to apply
    requestAnimationFrame(function () {
      el.classList.add('page-loaded');
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', onReady);
  } else {
    onReady();
  }
})();

