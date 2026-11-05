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

  // Live-Validierung für Formulare mit data-live-validate (Regeln wie serverseitig)
  document.querySelectorAll('form[data-live-validate]').forEach(function (form) {
    var field = function (name) { return form.elements[name]; };
    var val = function (name) { return field(name) ? field(name).value.trim() : ''; };
    var emailRe = /^[A-Za-z0-9.!#$%&'*+\/=?^_`{|}~-]+@[A-Za-z0-9](?:[A-Za-z0-9-]{0,61}[A-Za-z0-9])?(?:\.[A-Za-z0-9](?:[A-Za-z0-9-]{0,61}[A-Za-z0-9])?)+$/;
    var touched = {};

    var rules = {
      name: function () {
        var v = val('name');
        return v === '' || v.length > 120 ? 'Bitte geben Sie Ihren Namen an.' : '';
      },
      tel: function () {
        var v = val('tel');
        if (v === '') {
          // Nur meckern, wenn beide Kontaktfelder bereits besucht wurden (oder beim Absenden)
          return val('email') === '' && touched.tel && touched.email ? 'Bitte geben Sie eine Telefonnummer oder E-Mail-Adresse an.' : '';
        }
        return /^[0-9 +()\/\-]{5,30}$/.test(v) ? '' : 'Die Telefonnummer ist ungültig.';
      },
      email: function () {
        var v = val('email');
        return v === '' || emailRe.test(v) ? '' : 'Die E-Mail-Adresse ist ungültig.';
      },
      datum: function () {
        var el = field('datum');
        return el.validity && el.validity.badInput ? 'Bitte ein gültiges Datum wählen.' : '';
      },
      personen: function () {
        var el = field('personen');
        var v = val('personen');
        if (el.validity && el.validity.badInput) return 'Bitte eine Personenzahl angeben.';
        if (v === '') return '';
        return /^\d+$/.test(v) && +v >= 1 && +v <= 999 ? '' : 'Bitte eine Personenzahl zwischen 1 und 999 angeben.';
      },
      nachricht: function () {
        return val('nachricht').length > 5000 ? 'Die Nachricht ist zu lang.' : '';
      },
      datenschutz: function () {
        return field('datenschutz').checked ? '' : 'Bitte bestätigen Sie die Datenschutzerklärung.';
      }
    };

    var feedbackFor = function (el) {
      var box = el.closest('.form-check') || el.parentNode;
      var fb = box.querySelector('.invalid-feedback');
      if (!fb) {
        fb = document.createElement('div');
        fb.className = 'invalid-feedback';
        box.appendChild(fb);
      }
      fb.classList.remove('d-block'); // Sichtbarkeit steuert Bootstrap über .is-invalid ~ .invalid-feedback
      if (!fb.id) fb.id = el.id + '-fehler';
      return fb;
    };

    var show = function (name, msg) {
      var el = field(name);
      if (!el) return;
      var fb = feedbackFor(el);
      fb.textContent = msg;
      el.classList.toggle('is-invalid', msg !== '');
      if (msg) {
        el.setAttribute('aria-invalid', 'true');
        el.setAttribute('aria-describedby', fb.id);
      } else {
        el.removeAttribute('aria-invalid');
        el.removeAttribute('aria-describedby');
      }
    };

    var check = function (name) {
      var msg = rules[name]();
      show(name, msg);
      return msg === '';
    };

    var hideSummary = function () {
      var alert = form.querySelector('.alert-warning');
      if (alert && !form.querySelector('.is-invalid')) alert.remove();
    };

    // Vom Server markierte Felder sofort live nachprüfen
    Object.keys(rules).forEach(function (name) {
      var el = field(name);
      if (!el) return;
      if (el.classList.contains('is-invalid')) {
        touched[name] = true;
        feedbackFor(el);
        el.setAttribute('aria-invalid', 'true');
        el.setAttribute('aria-describedby', feedbackFor(el).id);
      }

      var onChange = function () {
        // Während der ersten Eingabe noch nicht meckern – erst nach Verlassen des Feldes
        if (!touched[name]) return;
        check(name);
        if (name === 'tel' || name === 'email') check(name === 'tel' ? 'email' : 'tel');
        hideSummary();
      };
      el.addEventListener('input', onChange);
      el.addEventListener('change', function () {
        if (el.type === 'checkbox' || el.tagName === 'SELECT' || el.type === 'date') touched[name] = true;
        onChange();
      });
      el.addEventListener('blur', function () {
        touched[name] = true;
        onChange();
      });
    });

    form.addEventListener('submit', function (event) {
      var first = null;
      Object.keys(rules).forEach(function (name) { touched[name] = true; });
      Object.keys(rules).forEach(function (name) {
        if (field(name) && !check(name) && !first) first = field(name);
      });
      if (first) {
        event.preventDefault();
        first.focus({ preventScroll: true });
        var smooth = !window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        first.scrollIntoView({ block: 'center', behavior: smooth ? 'smooth' : 'auto' });
        return;
      }
      hideSummary();
      var button = form.querySelector('[type="submit"]');
      if (button) button.disabled = true; // Doppelklick verhindern
    });

    // Zurück-Button (bfcache): Senden-Button wieder freigeben
    window.addEventListener('pageshow', function () {
      var button = form.querySelector('[type="submit"]');
      if (button) button.disabled = false;
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
