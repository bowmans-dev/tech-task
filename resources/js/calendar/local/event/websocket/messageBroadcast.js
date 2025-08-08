export default function messageBroadcast(data) {

  const dropZone = document.getElementById("drop-zone");
  const dropZoneEventId = dropZone?.getAttribute("data-event-id");

  if (dropZoneEventId === data.event_id) {
    const messagesContainer = document.getElementById("messages");
    const temp = document.createElement("div");
    temp.innerHTML = data.html.trim();

    const messageEl = temp.firstElementChild;
    if (messageEl) {
      if (data.isSender) {
        messageEl.classList.add("justify-end");
        messageEl.querySelector(".message").classList.add("bg-[#d9fdd3]");
      } else {
        messageEl.classList.add("justify-start");
        messageEl.querySelector(".message").classList.add("bg-[#ffffff]");
      }

      messagesContainer.appendChild(messageEl);
    }
  }
}