export function showMessageNotification(payload) {
  renderNotification(payload, `✨ ${truncate(payload.message, 80)}`);
}

export function showTeamMemberNotification(payload) {
  renderNotification(payload, `✨ You were added to <strong>${payload.eventName}</strong>`);
}

function renderNotification(payload, messageHtml) {
  const container = document.getElementById("notification-container");
  const notification = document.createElement("div");
  notification.className = "notification block cursor-pointer";

  let firstName;
  let lastName;

  if (payload.user.name) {
    firstName = "(Admin)";
    lastName = payload.user.name;
  } else {
    firstName = payload.user.first_name;
    lastName = payload.user.last_name;
  }

  const profilePicturePath = payload.user.profile_picture.startsWith("/storage/")
    ? payload.user.profile_picture
    : `/storage/${payload.user.profile_picture}`;

  notification.innerHTML = `
    <div>
      <div class="flex align-center mb-2">
        <div class="rounded-full bg-[#e0e0e0]">
          <img src="${profilePicturePath}" alt="${firstName}'s profile picture">
        </div>
        <div class="notification-title">
          <div>${firstName} ${lastName}</div>
          <div class="text-[10px] leading-[10px]">${payload.eventName}</div>
        </div>
      </div>
      <div class="notification-content">
        <div class="notification-message">${messageHtml}</div>
      </div>
    </div>
  `;

  container.appendChild(notification);
  setupNotificationBehavior(notification, payload.eventId);
}

function truncate(str, len) {
  return str.length > len ? str.slice(0, len) + "..." : str;
}

function setupNotificationBehavior(notification, eventId) {
  notification.addEventListener("click", () => openEventModal(eventId));
  setTimeout(() => notification.classList.add("show"), 100);
  setTimeout(() => {
    notification.classList.remove("show");
    setTimeout(() => notification.remove(), 500);
  }, 10000);
}

function openEventModal(eventId) {
  const event = window.calendar.getEventById(eventId);
  if (!event) return window.location.reload();

  event.setProp("backgroundColor", "#2D89EF");
  setTimeout(() => {
    window.calendar.trigger("eventClick", { event, el: event._def.ui.classNames });
    event.setProp("backgroundColor", null);

    const dropZone = document.getElementById("drop-zone");
    if (dropZone) dropZone.scrollTo({ top: dropZone.scrollHeight, behavior: "smooth" });
    
    highlightLastMessage();
    
  }, 500);
}


function highlightLastMessage() {
  setTimeout(() => {
    const messageContainer = document.getElementById("messages");
    if (!messageContainer) return;

    const messages = messageContainer.querySelectorAll(".bubble");
    const lastMessage = messages[messages.length - 1];
    if (!lastMessage) return;

    lastMessage.classList.add("highlight-message");
    const messageText = lastMessage.querySelector("p");
    if (messageText) messageText.classList.add("message-text-color");

  }, 500);
}