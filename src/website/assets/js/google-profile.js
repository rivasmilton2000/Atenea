(function () {
  'use strict';

  const form = document.querySelector('[data-google-profile-form]');
  if (!form) return;

  const config = window.ATENEA_GOOGLE_PROFILE || {};
  const department = form.elements.departamento_id;
  const municipality = form.elements.municipio_id;
  const district = form.elements.distrito_id;
  const phone = form.elements.telefono;
  const phoneCode = form.elements.codigo_telefono;
  const submitButton = form.querySelector('[data-google-submit]');
  const namePattern = /^(?=.*\p{L})[\p{L}\p{M}'’ -]+$/u;
  const phoneLengths = {'+503': 8, '+502': 8, '+504': 8, '+505': 8, '+506': 8, '+507': 8, '+52': 10, '+1': 10};

  function normalizeSpaces(value) {
    return String(value || '').replace(/\s+/gu, ' ').trim();
  }

  function fieldError(field, message) {
    const error = form.querySelector(`[data-google-error="${field}"]`);
    const control = field === 'ubicacion' ? district : form.elements[field];
    if (control) control.classList.toggle('is-invalid', Boolean(message));
    if (field === 'ubicacion') {
      [department, municipality, district].forEach(item => item.classList.toggle('is-invalid', Boolean(message)));
    }
    if (error) {
      error.textContent = message || '';
      error.classList.toggle('d-block', Boolean(message));
    }
    return !message;
  }

  function parseDate(value) {
    const match = /^(\d{4})-(\d{2})-(\d{2})$/.exec(value);
    if (!match) return null;
    const date = new Date(Number(match[1]), Number(match[2]) - 1, Number(match[3]));
    return date.getFullYear() === Number(match[1]) && date.getMonth() === Number(match[2]) - 1 && date.getDate() === Number(match[3]) ? date : null;
  }

  function validate() {
    let valid = true;
    ['nombre', 'apellido'].forEach(field => {
      const control = form.elements[field];
      const value = normalizeSpaces(control.value);
      control.value = value;
      const label = field === 'nombre' ? 'Los nombres' : 'Los apellidos';
      const message = value.length < 2 || value.length > 60 || !namePattern.test(value)
        ? `${label} deben tener entre 2 y 60 caracteres y usar únicamente letras, espacios, apóstrofes o guiones.` : '';
      valid = fieldError(field, message) && valid;
    });

    const birthdate = form.elements.fecha_nacimiento;
    const parsed = parseDate(birthdate.value);
    const messageDate = !parsed || birthdate.value < config.minDate || birthdate.value > config.maxDate
      ? 'Debes tener al menos 18 años y proporcionar una fecha válida.' : '';
    valid = fieldError('fecha_nacimiento', messageDate) && valid;

    valid = fieldError('dui', /^\d{8}-\d$/.test(form.elements.dui.value) ? '' : 'El DUI debe usar exactamente el formato 00000000-0.') && valid;

    normalizePhone();
    const expected = phoneLengths[phoneCode.value];
    let phoneValid = expected ? new RegExp(`^\\d{${expected}}$`).test(phone.value) : /^\d{7,15}$/.test(phone.value);
    if (phoneCode.value === '+503') phoneValid = phoneValid && /^[267]/.test(phone.value);
    valid = fieldError('telefono', phoneValid ? '' : (phoneCode.value === '+503'
      ? 'Para El Salvador ingresa ocho dígitos; el número debe iniciar con 2, 6 o 7.'
      : 'Ingresa la cantidad de dígitos correspondiente al prefijo seleccionado.')) && valid;

    const locationValid = Boolean(department.value && municipality.value && district.value && !municipality.disabled && !district.disabled);
    valid = fieldError('ubicacion', locationValid ? '' : 'Selecciona un departamento, municipio y distrito válidos.') && valid;

    const address = form.elements.direccion;
    address.value = normalizeSpaces(address.value);
    const addressValid = address.value.length >= 8 && address.value.length <= 250
      && /[\p{L}\p{N}]/u.test(address.value) && !/<[^>]*>/.test(address.value)
      && !/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/.test(address.value);
    valid = fieldError('direccion', addressValid ? '' : 'La dirección debe tener entre 8 y 250 caracteres y no contener etiquetas HTML.') && valid;
    valid = fieldError('terminos', form.elements.terminos.checked ? '' : 'Debes aceptar los términos y confirmar que los datos son correctos.') && valid;
    return valid;
  }

  async function fill(select, type, parent, selected) {
    select.innerHTML = '<option value="">Seleccione…</option>';
    select.disabled = !parent;
    if (!parent) return;
    try {
      const response = await fetch(`${config.endpoint}?tipo=${type}&padre=${encodeURIComponent(parent)}`, {headers: {Accept: 'application/json'}});
      if (!response.ok) throw new Error('HTTP');
      const payload = await response.json();
      (payload.data || []).forEach(item => select.add(new Option(item.nombre, item.id, false, Number(item.id) === Number(selected))));
      select.disabled = false;
    } catch (error) {
      select.disabled = true;
      fieldError('ubicacion', 'No se pudieron cargar las ubicaciones. Intenta nuevamente.');
    }
  }

  function normalizePhone() {
    let digits = phone.value.replace(/\D/g, '').slice(0, 15);
    const prefix = phoneCode.value.replace(/\D/g, '');
    if (prefix && digits.startsWith(prefix)) digits = digits.slice(prefix.length);
    phone.value = digits;
    const expected = phoneLengths[phoneCode.value];
    // Mantener espacio para que un pegado con prefijo pueda normalizarse antes de limitarlo.
    phone.maxLength = 15;
    if (expected) phone.value = phone.value.slice(0, expected);
  }

  department.addEventListener('change', async () => {
    await fill(municipality, 'municipios', department.value, 0);
    await fill(district, 'distritos', '', 0);
    fieldError('ubicacion', '');
  });
  municipality.addEventListener('change', async () => {
    await fill(district, 'distritos', municipality.value, 0);
    fieldError('ubicacion', '');
  });
  form.elements.dui.addEventListener('input', event => {
    const digits = event.target.value.replace(/\D/g, '').slice(0, 9);
    event.target.value = digits.length > 8 ? `${digits.slice(0, 8)}-${digits.slice(8)}` : digits;
  });
  phone.addEventListener('input', normalizePhone);
  phoneCode.addEventListener('change', normalizePhone);

  form.addEventListener('submit', event => {
    event.preventDefault();
    if (!validate()) {
      form.querySelector('.is-invalid')?.focus();
      return;
    }
    submitButton.disabled = true;
    submitButton.setAttribute('aria-busy', 'true');
    submitButton.querySelector('[data-google-submit-label]')?.classList.add('d-none');
    submitButton.querySelector('[data-google-submit-loading]')?.classList.remove('d-none');
    window.setTimeout(() => HTMLFormElement.prototype.submit.call(form), 0);
  });

  const initialMunicipality = municipality.dataset.selected;
  const initialDistrict = district.dataset.selected;
  if (department.value) {
    fill(municipality, 'municipios', department.value, initialMunicipality)
      .then(() => municipality.value && fill(district, 'distritos', municipality.value, initialDistrict));
  }

  if (window.flatpickr) {
    window.flatpickr(form.elements.fecha_nacimiento, {
      altInput: true,
      altFormat: 'd/m/Y',
      dateFormat: 'Y-m-d',
      minDate: config.minDate,
      maxDate: config.maxDate,
      allowInput: true,
      disableMobile: true,
      monthSelectorType: 'dropdown',
      locale: {firstDayOfWeek: 1, weekdays: {shorthand: ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'], longhand: ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado']}, months: {shorthand: ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'], longhand: ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre']}}
    });
  }
})();
