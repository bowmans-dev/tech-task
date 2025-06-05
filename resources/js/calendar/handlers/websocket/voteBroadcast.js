export default function voteBroadcast(data) {
  const messageEl = document.getElementById(`message-${data.message_id}`);
  if (!messageEl) return;

  const bubble = messageEl.querySelector('.bubble');
  if (!bubble) return;

  const pollBlock = bubble.querySelector('.poll-block');
  if (pollBlock) {
    pollBlock.remove();
  }

  const temp = document.createElement('div');
  temp.innerHTML = data.html.trim();

  const newPollBlock = temp.firstElementChild;
  if (newPollBlock) {
    bubble.appendChild(newPollBlock);
  }
}