(function () {
  'use strict';
  document.addEventListener('click', function (event) {
    var button = event.target.closest('.wla-exp-play[data-embed]');
    if (!button) return;
    var url;
    try { url = new URL(button.dataset.embed); } catch (error) { return; }
    if (url.protocol !== 'https:' || !['www.youtube-nocookie.com', 'player.vimeo.com'].includes(url.hostname)) return;
    url.searchParams.set('autoplay', '1');
    var frame = document.createElement('iframe');
    frame.src = url.toString();
    frame.title = button.getAttribute('aria-label').replace(/^Play video:\s*/i, '');
    frame.loading = 'lazy';
    frame.referrerPolicy = 'strict-origin-when-cross-origin';
    frame.allow = 'accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture; web-share';
    frame.allowFullscreen = true;
    button.replaceWith(frame);
  });
})();
