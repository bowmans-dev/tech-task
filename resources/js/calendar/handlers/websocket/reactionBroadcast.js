export default function reactionBroadcast(data) {
  const messageEl = document.getElementById(`message-${data.message_id}`);
  if (!messageEl) return;

  const bubble = messageEl.querySelector('.bubble');
  if (!bubble) return;

  const existingReactions = bubble.querySelector('.reaction-block');
  if (existingReactions) {
    existingReactions.remove();
  }

  const temp = document.createElement('div');
  temp.innerHTML = data.html.trim();

  const newReactions = temp.firstElementChild;
  if (newReactions) {
    bubble.appendChild(newReactions);
  }
}