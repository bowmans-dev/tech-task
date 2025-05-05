export function initializeUserListDragHandlers() {
    document.querySelectorAll('.user-link').forEach(item => {
        item.addEventListener('dragstart', function (event) {
            let profilePicture = item.dataset.profilePicture;
            if (profilePicture === './storage/') {
                profilePicture = '/storage/default_profile_image.png';
            }
            const dragData = JSON.stringify({
                userId: item.dataset.userId,
                profilePicture: profilePicture,
                firstName: item.dataset.firstName,
                lastName: item.dataset.lastName,
            });

            event.dataTransfer.setData('text/plain', dragData);
            console.log("Drag data set:", dragData);
        });
    });
}