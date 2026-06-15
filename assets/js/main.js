// Public site behaviour
(function () {
  'use strict';

  // Bootstrap client-side validation styling
  document.querySelectorAll('.needs-validation').forEach(function (form) {
    form.addEventListener('submit', function (ev) {
      if (!form.checkValidity()) {
        ev.preventDefault();
        ev.stopPropagation();
      }
      form.classList.add('was-validated');
    }, false);
  });

  // Sticky "Register Now": smooth-scroll if the form is on this page,
  // otherwise let the link navigate to register.php#register-form.
  var sticky = document.getElementById('stickyRegister');
  if (sticky) {
    sticky.addEventListener('click', function (ev) {
      var target = document.getElementById('register-form');
      if (target) {
        ev.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'center' });
        var firstInput = target.querySelector('input,select,textarea');
        if (firstInput) { setTimeout(function () { firstInput.focus(); }, 600); }
      }
    });
  }

  // If we arrived with #register-form in the URL, scroll smoothly to it.
  if (window.location.hash === '#register-form') {
    var el = document.getElementById('register-form');
    if (el) { setTimeout(function () { el.scrollIntoView({ behavior: 'smooth', block: 'center' }); }, 200); }
  }
})();
