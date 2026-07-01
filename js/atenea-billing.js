(function (window, document) {
  'use strict';

  var catalog = Array.isArray(window.AteneaSvLocationCatalog) ? window.AteneaSvLocationCatalog : [];

  function toDigits(value) {
    return String(value || '').replace(/\D+/g, '');
  }

  function setSelectOptions(select, items, placeholder, selectedValue) {
    if (!select) {
      return;
    }

    var currentValue = String(selectedValue || '').trim();
    var fragment = document.createDocumentFragment();
    var placeholderOption = document.createElement('option');
    placeholderOption.value = '';
    placeholderOption.textContent = placeholder;
    fragment.appendChild(placeholderOption);

    var hasSelected = currentValue === '';

    items.forEach(function (item) {
      var option = document.createElement('option');
      option.value = item;
      option.textContent = item;
      if (item === currentValue) {
        option.selected = true;
        hasSelected = true;
      }
      fragment.appendChild(option);
    });

    if (!hasSelected && currentValue !== '') {
      var legacyOption = document.createElement('option');
      legacyOption.value = currentValue;
      legacyOption.textContent = currentValue;
      legacyOption.selected = true;
      fragment.appendChild(legacyOption);
    }

    select.innerHTML = '';
    select.appendChild(fragment);
  }

  function findDepartmentEntry(value) {
    var department = String(value || '').trim();
    for (var index = 0; index < catalog.length; index += 1) {
      if (catalog[index].department === department) {
        return catalog[index];
      }
    }

    return null;
  }

  function formatDocumentValue(documentType, rawValue) {
    var digits = toDigits(rawValue);

    if (documentType === 'NIT') {
      digits = digits.slice(0, 14);
      if (digits.length <= 4) {
        return digits;
      }
      if (digits.length <= 10) {
        return digits.slice(0, 4) + '-' + digits.slice(4);
      }
      if (digits.length <= 13) {
        return digits.slice(0, 4) + '-' + digits.slice(4, 10) + '-' + digits.slice(10);
      }

      return digits.slice(0, 4) + '-' + digits.slice(4, 10) + '-' + digits.slice(10, 13) + '-' + digits.slice(13);
    }

    digits = digits.slice(0, 9);
    if (digits.length <= 8) {
      return digits;
    }

    return digits.slice(0, 8) + '-' + digits.slice(8);
  }

  function updateDocumentUi(form) {
    var documentType = form.querySelector('[data-document-type]');
    var documentNumber = form.querySelector('[data-document-number]');
    var documentHelp = form.querySelector('[data-document-help]');

    if (!documentType || !documentNumber) {
      return;
    }

    if (documentType.value === 'NIT') {
      documentNumber.placeholder = '0000-000000-000-0';
      documentNumber.maxLength = 17;
      documentNumber.setAttribute('inputmode', 'numeric');
      documentNumber.setAttribute('autocomplete', 'off');
      if (documentHelp) {
        documentHelp.textContent = 'Formato de NIT: 0000-000000-000-0';
      }
    } else {
      documentNumber.placeholder = '00000000-0';
      documentNumber.maxLength = 10;
      documentNumber.setAttribute('inputmode', 'numeric');
      documentNumber.setAttribute('autocomplete', 'off');
      if (documentHelp) {
        documentHelp.textContent = 'Formato de DUI: 00000000-0';
      }
    }

    documentNumber.value = formatDocumentValue(documentType.value, documentNumber.value);
  }

  function initDocumentFormatting(form) {
    var documentType = form.querySelector('[data-document-type]');
    var documentNumber = form.querySelector('[data-document-number]');
    if (!documentType || !documentNumber) {
      return;
    }

    updateDocumentUi(form);

    documentType.addEventListener('change', function () {
      var previousValue = documentNumber.value;
      updateDocumentUi(form);
      documentNumber.value = formatDocumentValue(documentType.value, previousValue);

      var digits = toDigits(documentNumber.value);
      if ((documentType.value === 'DUI' && digits.length > 0 && digits.length !== 9)
        || (documentType.value === 'NIT' && digits.length > 0 && digits.length !== 14)) {
        documentNumber.value = '';
      }
    });

    documentNumber.addEventListener('input', function () {
      documentNumber.value = formatDocumentValue(documentType.value, documentNumber.value);
    });

    documentNumber.addEventListener('paste', function () {
      window.setTimeout(function () {
        documentNumber.value = formatDocumentValue(documentType.value, documentNumber.value);
      }, 0);
    });
  }

  function toggleNrcField(form) {
    var nrcToggle = form.querySelector('[data-billing-nrc-toggle]');
    var nrcField = form.querySelector('[data-billing-nrc-input]');
    var nrcGroup = form.querySelector('[data-billing-nrc-group]');

    if (!nrcToggle || !nrcField) {
      return;
    }

    var enabled = !!nrcToggle.checked;
    nrcField.disabled = !enabled;
    nrcField.required = enabled;
    if (nrcGroup) {
      nrcGroup.hidden = !enabled;
    }

    if (!enabled) {
      nrcField.value = '';
    }
  }

  function initNrcToggle(form) {
    var nrcToggle = form.querySelector('[data-billing-nrc-toggle]');
    if (!nrcToggle) {
      return;
    }

    toggleNrcField(form);
    nrcToggle.addEventListener('change', function () {
      toggleNrcField(form);
    });
  }

  function syncMunicipalityOptions(form, selectedMunicipality) {
    var departmentSelect = form.querySelector('[data-billing-department]');
    var municipalitySelect = form.querySelector('[data-billing-municipality]');
    var districtField = form.querySelector('[data-billing-district]');

    if (!departmentSelect || !municipalitySelect) {
      return;
    }

    var departmentEntry = findDepartmentEntry(departmentSelect.value);
    var municipalities = departmentEntry
      ? departmentEntry.municipalities.map(function (entry) { return entry.name; })
      : [];

    setSelectOptions(
      municipalitySelect,
      municipalities,
      'Selecciona un municipio',
      selectedMunicipality
    );
    municipalitySelect.disabled = municipalities.length === 0;

    if (districtField && districtField.tagName === 'SELECT') {
      setSelectOptions(districtField, [], 'Selecciona un distrito o ciudad', districtField.getAttribute('data-selected') || districtField.value);
      districtField.disabled = true;
    }
  }

  function initLocationSelectors(form) {
    var departmentSelect = form.querySelector('[data-billing-department]');
    var municipalitySelect = form.querySelector('[data-billing-municipality]');

    if (!departmentSelect || !municipalitySelect) {
      return;
    }

    var selectedDepartment = departmentSelect.getAttribute('data-selected') || departmentSelect.value;
    var selectedMunicipality = municipalitySelect.getAttribute('data-selected') || municipalitySelect.value;

    setSelectOptions(
      departmentSelect,
      catalog.map(function (entry) { return entry.department; }),
      'Selecciona un departamento',
      selectedDepartment
    );

    syncMunicipalityOptions(form, selectedMunicipality);

    departmentSelect.addEventListener('change', function () {
      syncMunicipalityOptions(form, '');

      var districtField = form.querySelector('[data-billing-district]');
      if (districtField) {
        districtField.value = '';
      }
    });
  }

  function initBillingForm(form) {
    if (!form) {
      return;
    }

    initLocationSelectors(form);
    initDocumentFormatting(form);
    initNrcToggle(form);
  }

  function initAll() {
    var forms = document.querySelectorAll('[data-atenea-billing-form]');
    Array.prototype.forEach.call(forms, initBillingForm);
  }

  window.AteneaBilling = {
    initAll: initAll,
    initForm: initBillingForm,
    formatDocumentValue: formatDocumentValue
  };

  document.addEventListener('DOMContentLoaded', initAll);
}(window, document));
