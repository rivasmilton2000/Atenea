(() => {
  'use strict';

  const body = document.body;
  const sidebar = document.getElementById('portalSidebar');
  const buttons = document.querySelectorAll('[data-portal-sidebar-toggle]');
  if (!sidebar) return;

  const desktop = () => matchMedia('(min-width: 1200px)').matches;

  function sync() {
    const expanded = desktop()
      ? !sidebar.classList.contains('sidebar-mini')
      : body.classList.contains('portal-sidebar-open');
    buttons.forEach((button) => button.setAttribute('aria-expanded', String(expanded)));
  }

  function toggle() {
    if (desktop()) {
      sidebar.classList.toggle('sidebar-mini');
      localStorage.setItem('ateneaStudentSidebar', sidebar.classList.contains('sidebar-mini') ? 'mini' : 'full');
    } else {
      body.classList.toggle('portal-sidebar-open');
    }
    sync();
  }

  if (desktop() && localStorage.getItem('ateneaStudentSidebar') === 'mini') {
    sidebar.classList.add('sidebar-mini');
  }

  buttons.forEach((button) => button.addEventListener('click', toggle));
  sidebar.querySelectorAll('a[href]').forEach((link) => link.addEventListener('click', () => {
    if (!desktop() && !link.hasAttribute('data-bs-toggle')) body.classList.remove('portal-sidebar-open');
  }));

  addEventListener('resize', () => {
    if (desktop()) body.classList.remove('portal-sidebar-open');
    sync();
  });

  addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && body.classList.contains('portal-sidebar-open')) {
      body.classList.remove('portal-sidebar-open');
      sync();
      buttons[0]?.focus();
    }
  });

  sync();
})();
