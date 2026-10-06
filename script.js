(() => {
  const menuButton = document.getElementById('menu');
  const navigation = document.getElementById('nav');

  if (!menuButton || !navigation) return;

  const menuLabel = menuButton.querySelector('[data-menu-label]');
  const mobileViewport = window.matchMedia('(max-width: 900px)');

  const setMenuOpen = (isOpen) => {
    navigation.classList.toggle('open', isOpen);
    menuButton.setAttribute('aria-expanded', String(isOpen));
    menuButton.setAttribute(
      'aria-label',
      isOpen ? 'Close navigation menu' : 'Open navigation menu'
    );

    if (menuLabel) menuLabel.textContent = isOpen ? 'Close' : 'Menu';
  };

  setMenuOpen(false);
  document.documentElement.classList.add('js');

  menuButton.addEventListener('click', () => {
    if (!mobileViewport.matches) return;
    setMenuOpen(!navigation.classList.contains('open'));
  });

  navigation.addEventListener('click', (event) => {
    if (event.target instanceof Element && event.target.closest('a')) {
      setMenuOpen(false);
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && navigation.classList.contains('open')) {
      setMenuOpen(false);
      menuButton.focus();
    }
  });

  document.addEventListener('click', (event) => {
    if (
      navigation.classList.contains('open') &&
      event.target instanceof Node &&
      !navigation.contains(event.target) &&
      !menuButton.contains(event.target)
    ) {
      setMenuOpen(false);
    }
  });

  mobileViewport.addEventListener('change', () => {
    if (mobileViewport.matches && navigation.contains(document.activeElement)) {
      menuButton.focus();
    }
    setMenuOpen(false);
  });
})();
