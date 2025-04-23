export function setupSearchInput() {
  const searchInput = document.getElementById('search');
  if (!searchInput) return;

  searchInput.addEventListener('input', function () {
      const search = this.value;

      fetch(`/users/filter?search=${encodeURIComponent(search)}`, {
          headers: { 'Accept': 'text/vnd.turbo-stream.html' },
      })
      .then((response) => {
          if (response.headers.get('Content-Type').includes('text/vnd.turbo-stream.html')) {
              return response.text().then((turboStream) => {
                  Turbo.renderStreamMessage(turboStream);
              });
          } else {
              return response.text().then((html) => {
                  document.getElementById('user-list').innerHTML = html;
              });
          }
      })
      .catch((error) => console.error('Error:', error));
  });
}