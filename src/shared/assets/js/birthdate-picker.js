(function () {
  'use strict';

  const message = 'Debes tener al menos 18 años para registrarte.';

  function validIso(value, min, max) {
    const match = /^(\d{4})-(\d{2})-(\d{2})$/.exec(value || '');
    if (!match) return false;
    const date = new Date(Number(match[1]), Number(match[2]) - 1, Number(match[3]));
    return date.getFullYear() === Number(match[1]) && date.getMonth() === Number(match[2]) - 1
      && date.getDate() === Number(match[3]) && value >= min && value <= max;
  }

  function showError(input, altInput, error) {
    altInput.setCustomValidity(error ? message : '');
    altInput.classList.toggle('is-invalid', error);
    const feedback = input.closest('.atenea-birthdate-field, .col-md-6')?.querySelector('[data-birthdate-error]');
    if (feedback) {
      feedback.textContent = error ? message : '';
      feedback.classList.toggle('d-block', error);
    }
    return !error;
  }

  function initialize(input) {
    if (!window.flatpickr || input._flatpickr) return;
    const min = input.dataset.minDate;
    const max = input.dataset.maxDate;
    const required = input.required;
    const picker = window.flatpickr(input, {
      locale: window.flatpickr.l10ns?.es || 'es',
      altInput: true,
      altFormat: 'd/m/Y',
      dateFormat: 'Y-m-d',
      minDate: min,
      maxDate: max,
      allowInput: true,
      disableMobile: true,
      monthSelectorType: 'dropdown',
      static: false,
      onReady: (_dates, _value, instance) => {
        instance.calendarContainer.classList.add('atenea-calendar');
        const label = input.id ? document.querySelector(`label[for="${CSS.escape(input.id)}"]`) : null;
        if (input.id) instance.altInput.id = `${input.id}_visible`;
        if (label) label.htmlFor = instance.altInput.id;
        instance.altInput.placeholder = 'dd/mm/aaaa';
        instance.altInput.autocomplete = 'bday';
        instance.altInput.setAttribute('aria-label', 'Fecha de nacimiento, formato día, mes y año');
        instance.altInput.required = required;
        input.required = false;
      },
      onChange: (_dates, value, instance) => showError(input, instance.altInput, !validIso(value, min, max)),
      onClose: (_dates, value, instance) => showError(input, instance.altInput, !validIso(value, min, max)),
    });
    const form = input.form;
    form?.addEventListener('submit', event => {
      const invalid = !validIso(input.value, min, max);
      if (!showError(input, picker.altInput, invalid)) {
        event.preventDefault();
        event.stopImmediatePropagation();
        picker.altInput.reportValidity();
        picker.altInput.focus();
      }
    }, true);
  }

  function initializeAll(root) {
    root.querySelectorAll('[data-atenea-birthdate]').forEach(initialize);
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', () => initializeAll(document));
  else initializeAll(document);
  window.AteneaBirthdatePicker = { initializeAll };
})();
