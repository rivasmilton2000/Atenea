(function () {
  'use strict';
  document.querySelectorAll('[data-content-publication-form]').forEach(form => {
    const type = form.querySelector('[data-resource-type]');
    const urlWrap = form.querySelector('[data-resource-url-wrap]');
    const fileWrap = form.querySelector('[data-resource-file-wrap]');
    const url = form.elements.recurso_url;
    const file = form.elements.archivo;
    function update() {
      const usesFile = ['video_archivo', 'documento'].includes(type.value);
      const usesUrl = ['youtube', 'google_drive', 'enlace'].includes(type.value);
      fileWrap.hidden = !usesFile; urlWrap.hidden = !usesUrl;
      file.required = usesFile && !form.dataset.existingFile;
      url.required = usesUrl;
      if (!usesFile) file.value = '';
      if (!usesUrl) url.value = '';
      file.accept = type.value === 'video_archivo' ? '.mp4,.webm,.ogv' : '.pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.jpg,.jpeg,.png,.webp,.txt';
    }
    type.addEventListener('change', update);update();
    form.addEventListener('submit', event => {
      if (file.files.length && url.value.trim()) { event.preventDefault(); url.setCustomValidity('Selecciona un archivo o un enlace, no ambos.'); url.reportValidity(); }
      else url.setCustomValidity('');
    });
  });
})();
