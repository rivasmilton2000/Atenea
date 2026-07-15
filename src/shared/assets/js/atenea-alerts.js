(function (window, document) {
  'use strict';

  const colors = {
    gold: '#c49a3a',
    green: '#173f35',
    danger: '#b4232f',
    cancel: '#66706a'
  };

  const hasSweetAlert = () => typeof window.Swal !== 'undefined' && typeof window.Swal.fire === 'function';
  const text = (value, fallback) => typeof value === 'string' && value.trim() ? value.trim() : fallback;

  function fire(options) {
    const config = Object.assign({
      customClass: { popup: 'atenea-alert-popup' },
      buttonsStyling: true,
      confirmButtonColor: colors.gold,
      cancelButtonColor: colors.cancel,
      focusConfirm: true,
      allowEscapeKey: true,
      heightAuto: false
    }, options || {});

    if (hasSweetAlert()) return window.Swal.fire(config);

    if (config.showCancelButton) {
      return Promise.resolve({ isConfirmed: window.confirm([config.title, config.text].filter(Boolean).join('\n\n')) });
    }
    window.alert([config.title, config.text].filter(Boolean).join('\n\n'));
    return Promise.resolve({ isConfirmed: true });
  }

  function notify(type, title, message) {
    return fire({
      icon: type,
      title: text(title, 'Atenea'),
      text: text(message, ''),
      confirmButtonText: 'Aceptar',
      confirmButtonColor: type === 'error' ? colors.danger : (type === 'success' ? colors.green : colors.gold)
    });
  }

  function confirmAction(options) {
    const danger = Boolean(options && options.danger);
    return fire({
      icon: danger ? 'warning' : 'question',
      title: text(options && options.title, '¿Deseas continuar?'),
      text: text(options && options.message, 'Confirma esta acción para continuar.'),
      showCancelButton: true,
      reverseButtons: true,
      focusCancel: danger,
      confirmButtonText: text(options && options.confirmText, 'Sí, continuar'),
      cancelButtonText: text(options && options.cancelText, 'Cancelar'),
      confirmButtonColor: danger ? colors.danger : colors.gold,
      showLoaderOnConfirm: true,
      allowOutsideClick: () => !hasSweetAlert() || !window.Swal.isLoading()
    }).then(result => Boolean(result && result.isConfirmed));
  }

  function loading(title, message) {
    return fire({
      title: text(title, 'Procesando'),
      text: text(message, 'Espera un momento…'),
      allowEscapeKey: false,
      allowOutsideClick: false,
      showConfirmButton: false,
      didOpen: () => { if (hasSweetAlert()) window.Swal.showLoading(); }
    });
  }

  function confirmationOptions(source) {
    const href = source.getAttribute && (source.getAttribute('href') || '');
    const action = source.name === 'accion' ? String(source.value || '') : '';
    const label = String(source.textContent || '').trim().toLowerCase();
    const inferredMode = ['eliminar', 'reasignar', 'eliminar_imagen'].includes(action) ? 'delete' : ((action === 'desactivar' || (action === 'toggle' && label.includes('desactivar'))) ? 'deactivate' : '');
    const mode = source.dataset.ateneaConfirm || (href.includes('/src/login/logout.php') ? 'logout' : inferredMode);
    const isLogout = mode === 'logout';
    const danger = source.dataset.ateneaDanger === 'true' || mode === 'delete' || mode === 'deactivate';
    return {
      title: text(source.dataset.ateneaConfirmTitle, isLogout ? '¿Cerrar sesión?' : (mode === 'delete' ? '¿Eliminar este registro?' : (mode === 'deactivate' ? '¿Desactivar este registro?' : '¿Deseas continuar?'))),
      message: text(source.dataset.ateneaConfirmMessage, isLogout ? 'Tendrás que iniciar sesión nuevamente para acceder a tu cuenta.' : (danger ? 'Esta acción puede afectar su disponibilidad o sus relaciones. Confirma que deseas continuar.' : 'Revisa la información antes de continuar.')),
      confirmText: text(source.dataset.ateneaConfirmText, isLogout ? 'Sí, cerrar sesión' : (danger ? 'Sí, confirmar' : 'Continuar')),
      cancelText: text(source.dataset.ateneaCancelText, 'Cancelar'),
      danger
    };
  }

  document.addEventListener('click', function (event) {
    const link = event.target.closest('a[data-atenea-confirm], a[href*="/src/login/logout.php"]');
    if (!link || link.dataset.ateneaConfirmed === 'true') return;
    event.preventDefault();
    confirmAction(confirmationOptions(link)).then(confirmed => {
      if (!confirmed) return;
      link.dataset.ateneaConfirmed = 'true';
      link.setAttribute('aria-disabled', 'true');
      window.location.assign(link.href);
    });
  });

  document.addEventListener('submit', function (event) {
    const form = event.target;
    if (!(form instanceof HTMLFormElement)) return;
    const submitter = event.submitter;
    const criticalAction = submitter && submitter.name === 'accion' && ['eliminar', 'eliminar_imagen', 'reasignar', 'desactivar', 'toggle'].includes(String(submitter.value || ''));
    const source = submitter && (submitter.hasAttribute('data-atenea-confirm') || criticalAction) ? submitter : (form.hasAttribute('data-atenea-confirm') ? form : null);

    if (source && form.dataset.ateneaConfirmed !== 'true') {
      event.preventDefault();
      confirmAction(confirmationOptions(source)).then(confirmed => {
        if (!confirmed) return;
        form.dataset.ateneaConfirmed = 'true';
        loading('Procesando', 'No cierres esta ventana mientras completamos la operación.');
        if (submitter) form.requestSubmit(submitter); else form.submit();
      });
      return;
    }

    if (form.dataset.ateneaSubmitting === 'true') {
      event.preventDefault();
      return;
    }
    form.dataset.ateneaSubmitting = 'true';
    if (submitter) submitter.disabled = true;
    if (form.hasAttribute('data-atenea-loading')) loading(form.dataset.ateneaLoadingTitle, form.dataset.ateneaLoadingMessage);
  });

  window.AteneaAlerts = {
    show: fire,
    success: (title, message) => notify('success', title, message),
    error: (title, message) => notify('error', title, message),
    warning: (title, message) => notify('warning', title, message),
    info: (title, message) => notify('info', title, message),
    confirm: confirmAction,
    loading,
    close: () => { if (hasSweetAlert()) window.Swal.close(); }
  };

  const flashNode = document.getElementById('atenea-flash-data');
  if (flashNode) {
    try {
      const flash = JSON.parse(flashNode.textContent || 'null');
      if (flash && flash.message) notify(flash.type || 'info', flash.title || 'Atenea', flash.message);
    } catch (error) {
      console.error('No fue posible leer el mensaje de Atenea.', error);
    }
  }
})(window, document);
