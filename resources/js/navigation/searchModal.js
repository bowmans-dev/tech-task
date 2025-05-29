export function setupSearchModalInput() {
    const searchInput = document.getElementById('searchModal');
    if (!searchInput) return;

    searchInput.addEventListener('input', function () {
    const search = this.value.trim();

    fetch(`/users/filter/modal?search=${encodeURIComponent(search)}`, {
        headers: { 'Accept': 'text/vnd.turbo-stream.html' },
    })
    .then(response => {
        return response.text().then(html => {
            const userListModal = document.getElementById('user-list-modal');
            userListModal.innerHTML = html;

            // Reattach dragstart listener for newly rendered user links
            const userLinks = document.querySelectorAll('.user-link');
            userLinks.forEach(userLink => {
                userLink.addEventListener('dragstart', function (event) {
                    const userId = this.dataset.userId;
                    let profilePicture = this.dataset.profilePicture;
                    if (profilePicture == './storage/') { profilePicture = '/storage/default_profile_image.webp' }
                    const firstName = this.dataset.firstName;
                    const lastName = this.dataset.lastName;

                    const userData = {
                        userId,
                        profilePicture,
                        firstName,
                        lastName,
                    };

                    event.dataTransfer.setData('text/plain', JSON.stringify(userData));
                });
            });
        });
    })
    .catch(error => console.error('Error:', error));
});
}