export function toggleSidebar(button) {
    const sidebar = document.getElementById('hs-application-sidebar');
  
    if (sidebar.classList.contains('-translate-x-full')) {
      sidebar.classList.remove('-translate-x-full');
      sidebar.classList.add('translate-x-0');
      sidebar.classList.remove('hidden');
    } else {
      sidebar.classList.add('-translate-x-full');
      sidebar.classList.remove('translate-x-0');
    }
  }