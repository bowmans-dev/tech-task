export function setupContextMenu() {
    document.addEventListener('click', (e) => {
      if (!e.target.closest('.context-menu') && !e.target.closest('.sub-context-menu')) {
        document.querySelectorAll('.sub-context-menu').forEach((submenu) => submenu.classList.add('hidden'));
        document.querySelectorAll('.context-menu').forEach((menu) => menu.classList.add('hidden'));
      }
    });
  
    document.querySelectorAll('.sub-context-menu').forEach((subMenu) => {
      subMenu.addEventListener('click', (e) => {
        e.stopPropagation();
      });
    });
  }
  
  export function toggleContextMenu(event) {
    event.stopPropagation();
    const contextMenu = event.currentTarget.nextElementSibling;
    document.querySelectorAll('.context-menu').forEach((menu) => {
      if (menu !== contextMenu) menu.classList.add('hidden');
    });
    contextMenu.classList.toggle('hidden');
  }
  
  export function toggleSubMenu(event) {
    event.stopPropagation();
    const subMenu = event.currentTarget.querySelector('.sub-context-menu');
    document.querySelectorAll('.sub-context-menu').forEach((menu) => {
      if (menu !== subMenu) menu.classList.add('hidden');
    });
    subMenu.classList.toggle('hidden');
  }