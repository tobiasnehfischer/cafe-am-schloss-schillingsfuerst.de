/* Café am Schloss – kleine Helfer (Bootstrap-Navbar läuft über bootstrap.bundle) */
(function () {
  'use strict';

  // Karte erst nach Klick laden (Zwei-Klick-Lösung, Datenschutz)
  document.querySelectorAll('.js-map-load').forEach(function (button) {
    button.addEventListener('click', function () {
      var box = button.closest('.map-consent');
      if (!box) return;
      var iframe = document.createElement('iframe');
      iframe.src = box.getAttribute('data-map-src');
      iframe.title = 'Karte: Lage des Café am Schloss';
      iframe.referrerPolicy = 'no-referrer-when-downgrade';
      box.innerHTML = '';
      box.appendChild(iframe);
    });
  });

  // Mobile Navigation nach Klick auf einen Anker schließen
  var nav = document.getElementById('hauptnavigation');
  if (nav && window.bootstrap) {
    nav.querySelectorAll('a').forEach(function (a) {
      a.addEventListener('click', function () {
        if (nav.classList.contains('show')) bootstrap.Collapse.getOrCreateInstance(nav).hide();
      });
    });
  }
})();
