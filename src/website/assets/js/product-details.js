(function () {
  'use strict';

  document.querySelectorAll('[data-product-image]').forEach(function (button) {
    button.addEventListener('click', function () {
      var main = document.getElementById('product-main-image');
      if (!main) return;
      main.src = button.dataset.productImage;
      main.alt = button.dataset.productAlt || main.alt;
      document.querySelectorAll('[data-product-image]').forEach(function (item) {
        var active = item === button;
        item.classList.toggle('is-active', active);
        item.setAttribute('aria-pressed', active ? 'true' : 'false');
      });
    });
  });

  document.querySelectorAll('[data-quantity]').forEach(function (button) {
    button.addEventListener('click', function () {
      var input = button.closest('.quantity-control')?.querySelector('input');
      if (!input) return;
      var min = Number(input.min || 1);
      var max = Number(input.max || 99);
      var change = button.dataset.quantity === 'up' ? 1 : -1;
      input.value = String(Math.min(max, Math.max(min, Number(input.value || min) + change)));
      input.dispatchEvent(new Event('change', { bubbles: true }));
    });
  });

  document.querySelectorAll('[data-checkout-form]').forEach(function (form) {
    form.addEventListener('submit', function (event) {
      if (!form.checkValidity()) {
        event.preventDefault();
        form.reportValidity();
        return;
      }
      var button = form.querySelector('.product-buy-button');
      if (!button || button.disabled) {
        event.preventDefault();
        return;
      }
      button.disabled = true;
      button.classList.add('is-loading');
      button.setAttribute('aria-busy', 'true');
    });
  });
})();
