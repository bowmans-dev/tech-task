export function maximiseModal(event, el, modalSelector = '#event-modal') {
    if (!document.fullscreenElement) {
        document.getElementById("fs").requestFullscreen().catch(err => {
            console.error("Failed to enter fullscreen:", err);
        });
    } else {
        document.exitFullscreen().catch(err => {
            console.error("Failed to exit fullscreen:", err);
        });
    }
}