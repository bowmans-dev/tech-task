export function closeModal (event, el, modalSelector = '#event-modal') {
    const modal = document.querySelector(modalSelector);
    modal.classList.add('hidden');
    
    if (document.fullscreenElement) {
        document.exitFullscreen().catch(err => {
            console.error("Failed to exit fullscreen:", err);
        });
    }
};