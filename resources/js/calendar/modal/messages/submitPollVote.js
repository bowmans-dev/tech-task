export function submitPollVote(event, button) {
  event.preventDefault();
  event.stopPropagation();
  const messageId = button.dataset.messageId;
  const optionId = button.dataset.optionId;

  fetch(`/poll-vote`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify({
      message_id: messageId,
      option_id: optionId,
    })
  })
  .then(response => response.json())
  .then(data => {
    // Optional: Update UI with vote confirmation or disable voting
    console.log('Vote submitted:', data);
  })
  .catch(error => {
    console.error('Error submitting vote:', error);
  });
}