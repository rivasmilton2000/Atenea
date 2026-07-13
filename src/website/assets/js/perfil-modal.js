document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('modalPerfil');
  if (!modal) return;

  const foto = document.getElementById('cuenta_foto');
  const preview = document.getElementById('vistaPreviaFoto');
  foto?.addEventListener('change', () => {
    const archivo = foto.files?.[0];
    if (!archivo) return;
    if (!['image/jpeg', 'image/png', 'image/webp'].includes(archivo.type) || archivo.size > 3 * 1024 * 1024) {
      foto.value = '';
      alert('Selecciona una imagen JPG, PNG o WEBP de hasta 3 MB.');
      return;
    }
    preview.src = URL.createObjectURL(archivo);
  });

  const departamento = document.getElementById('cuenta_departamento');
  const municipio = document.getElementById('cuenta_municipio');
  const distrito = document.getElementById('cuenta_distrito');
  const endpoint = window.ATENEA_CUENTA?.ubicaciones;
  const llenar = async (select, tipo, padre, valor = '') => {
    select.innerHTML = '<option value="">Seleccione…</option>';
    select.disabled = !padre;
    if (!padre || !endpoint) return;
    const respuesta = await fetch(`${endpoint}?tipo=${tipo}&padre=${encodeURIComponent(padre)}`, { headers: { Accept: 'application/json' } });
    if (!respuesta.ok) return;
    for (const opcion of await respuesta.json()) {
      select.add(new Option(opcion.nombre, opcion.id, false, String(opcion.id) === String(valor)));
    }
  };
  departamento?.addEventListener('change', async () => {
    await llenar(municipio, 'municipios', departamento.value);
    await llenar(distrito, 'distritos', '');
  });
  municipio?.addEventListener('change', () => llenar(distrito, 'distritos', municipio.value));
  if (departamento?.value) {
    llenar(municipio, 'municipios', departamento.value, municipio.dataset.selected)
      .then(() => municipio.value && llenar(distrito, 'distritos', municipio.value, distrito.dataset.selected));
  }
  if (modal.dataset.autoOpen === '1' && window.bootstrap) new bootstrap.Modal(modal).show();
});
