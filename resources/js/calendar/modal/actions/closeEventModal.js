export function closeModal (modalSelector = '#event-modal') {
    const modal = document.querySelector(modalSelector);
    modal.classList.add('hidden');
};