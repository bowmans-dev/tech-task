import { state } from '../../state';
import { sendMessageForEvent } from './sendMessageForEvent';

export async function setUpSendMessageButton(e) {
  e.preventDefault();

  const textarea = document.getElementById('message-content');

  // Retrieve the message content.
  const content = textarea.value.trim();

  const eventName = document.getElementById('eventName').value;

  let eventId = state.currentEvent.id;

  if (!eventId) {
      console.error("Error: eventId is undefined before sending the message.");
      return;
  }

  // Send message for event
  const csrfToken = document.querySelector('input[name="_token"]').value;
  try {
    const result = await sendMessageForEvent(content, eventId, eventName, csrfToken);
    if (result.success) {
      // Clear out the textarea after a successful send
      textarea.value = '';
    }
  } catch (error) {
    console.error("Error sending message via AJAX:", error);
  }
}