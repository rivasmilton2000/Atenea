(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    var toggle = document.getElementById('adminProfileTrigger');
    var menu = document.getElementById('adminProfileMenu');

    if (!toggle || !menu || typeof bootstrap === 'undefined' || !bootstrap.Dropdown) {
      return;
    }

    var dropdown = bootstrap.Dropdown.getOrCreateInstance(toggle);

    function menuItems() {
      return Array.prototype.slice.call(menu.querySelectorAll('.dropdown-item:not(:disabled)'));
    }

    function focusItem(index) {
      var items = menuItems();
      if (!items.length) {
        return;
      }
      items[(index + items.length) % items.length].focus();
    }

    toggle.addEventListener('keydown', function (event) {
      if (event.key !== 'ArrowDown' && event.key !== 'ArrowUp') {
        return;
      }
      event.preventDefault();
      dropdown.show();
      window.requestAnimationFrame(function () {
        focusItem(event.key === 'ArrowDown' ? 0 : -1);
      });
    });

    menu.addEventListener('keydown', function (event) {
      var items = menuItems();
      var current = items.indexOf(document.activeElement);

      if (event.key === 'Escape') {
        event.preventDefault();
        dropdown.hide();
        toggle.focus();
        return;
      }

      if (event.key === 'Home' || event.key === 'End') {
        event.preventDefault();
        focusItem(event.key === 'Home' ? 0 : -1);
        return;
      }

      if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
        event.preventDefault();
        focusItem(current + (event.key === 'ArrowDown' ? 1 : -1));
      }
    });

    document.addEventListener('click', function (event) {
      if (toggle.getAttribute('aria-expanded') === 'true' && !toggle.parentElement.contains(event.target)) {
        dropdown.hide();
      }
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') {
        dropdown.hide();
        toggle.focus();
      }
    });
  });
}());
