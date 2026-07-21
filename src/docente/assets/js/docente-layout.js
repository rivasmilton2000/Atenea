(function () {
  'use strict';
  function synchronizeActiveItem() {
    const sidebar = document.getElementById('sidebar');
    if (!sidebar) return;
    sidebar.querySelectorAll('.nav-item.active').forEach(item => item.classList.remove('active'));
    const current = sidebar.querySelector('.nav-link[aria-current="page"]');
    current?.closest('.nav-item')?.classList.add('active');
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      synchronizeActiveItem();
      window.setTimeout(synchronizeActiveItem, 0);
    });
  } else {
    synchronizeActiveItem();
  }
  window.addEventListener('load', synchronizeActiveItem);
})();
