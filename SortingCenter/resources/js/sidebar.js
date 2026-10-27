const sidebar = document.getElementById('appSidebar');
const toggle = document.getElementById('sidebarToggle');
const closeButton = document.getElementById('sidebarClose');
const backdrop = document.getElementById('sidebarBackdrop');

if (sidebar && toggle && closeButton && backdrop) {
  const desktop = window.matchMedia('(min-width: 1024px)');
  const isOpen = () => sidebar.classList.contains('is-open');

  function syncState() {
    const open = isOpen();

    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');

    if (desktop.matches) {
      sidebar.inert = false;
      document.body.classList.remove('sidebar-locked');
      return;
    }

    sidebar.inert = !open;
    document.body.classList.toggle('sidebar-locked', open);
  }

  function openSidebar() {
    if (desktop.matches) {
      return;
    }

    sidebar.classList.add('is-open');
    backdrop.classList.add('is-open');
    syncState();
    closeButton.focus();
  }

  function closeSidebar(restoreFocus = false) {
    sidebar.classList.remove('is-open');
    backdrop.classList.remove('is-open');
    syncState();

    if (restoreFocus && !desktop.matches) {
      toggle.focus();
    }
  }

  toggle.addEventListener('click', () => {
    isOpen() ? closeSidebar(true) : openSidebar();
  });

  closeButton.addEventListener('click', () => closeSidebar(true));
  backdrop.addEventListener('click', () => closeSidebar(true));

  sidebar.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', () => closeSidebar(false));
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && !desktop.matches && isOpen()) {
      event.preventDefault();
      closeSidebar(true);
    }
  });

  desktop.addEventListener('change', () => {
    sidebar.classList.remove('is-open');
    backdrop.classList.remove('is-open');
    syncState();
  });

  syncState();
}
