export function closeModal (event, el, modalSelector = '#event-modal') {
    const modal = document.querySelector(modalSelector);
    modal.classList.add('hidden');
};