export function setupSearchInput() {
    const searchInput = document.getElementById('search');
    if (!searchInput) return;
  
    searchInput.addEventListener('input', function () {
      const search = this.value;
  
      fetch(`/users/filter?search=${encodeURIComponent(search)}`)
        .then((response) => response.text())
        .then((data) => {
          document.getElementById('user-list').innerHTML = data;
        })
        .catch((error) => console.error('Error:', error));
    });
  }