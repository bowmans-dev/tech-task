export function openReactionPicker(event, el) {
  event.preventDefault(); // Prevent context menu
  const messageId = el.dataset.messageId;

  // Show emoji picker next to the message
  showEmojiPicker(el, messageId);
}

export function showEmojiPicker(targetEl, messageId) {
  const emojis = ['❤️', '😂', '😮', '😢', '😡', '👍'];
  const picker = document.createElement('div');
  picker.className = 'absolute bg-white shadow p-2 rounded flex space-x-2 z-50';
  picker.style.top = `${targetEl.getBoundingClientRect().top - 40}px`;
  picker.style.left = `${targetEl.getBoundingClientRect().left}px`;

  emojis.forEach(emoji => {
    const btn = document.createElement('button');
    btn.textContent = emoji;
    btn.className = 'text-2xl hover:scale-125 transition';
    btn.onclick = () => sendReaction(messageId, emoji);
    picker.appendChild(btn);
  });

  document.body.appendChild(picker);

  document.addEventListener('click', () => picker.remove(), { once: true });
}