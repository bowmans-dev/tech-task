import { state } from '../../local/state';
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
      const dropZone = document.getElementById("drop-zone");
      if (dropZone) {
          dropZone.scrollTo({ top: dropZone.scrollHeight, behavior: "smooth" });
      }
      const messageContainer = document.getElementById("messages");
      const messages = messageContainer.querySelectorAll(".message");
      const lastMessage = messages[messages.length - 1] || null;
      const messageText = lastMessage.querySelector("p");

      if (lastMessage) {
          lastMessage.classList.add("highlight-message");
          setTimeout(() => lastMessage.classList.remove("highlight-message"), 4000);
      }
      if (messageText) {
          messageText.classList.add("message-text-color");
          setTimeout(() => messageText.classList.remove("message-text-color"), 4500);
      }
    }
  } catch (error) {
    console.error("Error sending message via AJAX:", error);
  }
}