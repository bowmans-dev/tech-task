// state.js

// ─────────────────────────────────────────────
// 1. App State
// ─────────────────────────────────────────────

export let state = {
  droppedFiles: [],
  existingTeamMembers: [],
  teamMembers: [],
  currentWs: null,
  currentEvent: {
    id: null,
    title: '',
    date: '',
    time: '',
    allDay: false,
    files: [],
    teamMembers: [],
    isNew: true,
  },
};

// ─────────────────────────────────────────────
// 2. Current User Initialization
// ─────────────────────────────────────────────

const calendarWrapper = document.getElementById("calendar-wrapper");
const currentUserId = calendarWrapper?.getAttribute("data-user-id") ?? "admin";

export function getCurrentUser() {
  const profilePicture = calendarWrapper?.getAttribute("data-user-picture");
  const firstName = calendarWrapper?.getAttribute("data-user-first-name");
  const lastName = calendarWrapper?.getAttribute("data-user-last-name");

  return {
    userId: currentUserId,
    profile_picture: profilePicture || "/storage/default_profile_image.png",
    first_name: firstName || "User",
    last_name: lastName || "Name"
  };
}

export const currentUser = getCurrentUser();

// ─────────────────────────────────────────────
// 3. WebSocket Management
// ─────────────────────────────────────────────

export function connectToEventWebSocket() {
  const eventId = state.currentEvent.id;
  const user = getCurrentUser();

  if (!eventId || !user?.userId) {
    console.warn("Cannot connect to WebSocket: missing eventId or userId");
    return;
  }

  const normalizedUserId = String(user.userId);
  const socket = new WebSocket("ws://localhost:8080");

  if (state.currentWs) {
    console.log("🔌Closing previous WebSocket connection.");
    state.currentWs.close();
  }

  state.currentWs = socket;

  socket.onopen = () => {
    console.log(`✅Connected to WebSocket for event ${eventId}`);
    socket.send(JSON.stringify({
      action: "subscribe",
      event_id: eventId,
      userId: normalizedUserId,
      existingTeamMembers: state.currentEvent.teamMembers,
      currentEvent: {
        title: state.currentEvent.title,
        date: state.currentEvent.date,
        time: state.currentEvent.time,
        allDay: state.currentEvent.allDay,
      }
    }));
  };

  socket.onmessage = (message) => {
    try {
      const data = JSON.parse(message.data);
      console.log("📨 WebSocket message received:", data);

      if (data.action === "unsubscribed") {
        console.log(`User ${data.userId} has unsubscribed from ${data.event_id}`);
        return;
      }

      if (data.action !== "message_broadcast") {
        console.warn(`Ignored action: ${data.action}`);
        return;
      }

      const dropZone = document.getElementById("drop-zone");
      const dropZoneEventId = dropZone?.getAttribute("data-event-id");

      if (dropZoneEventId === data.event_id) {
        const messagesContainer = document.getElementById("messages");
        const messageElement = document.createElement("div");
        messageElement.classList.add("message");
        messageElement.innerHTML = `
          <div class="w-full text-left flex flex-row align-center">
            <img 
              class="rounded-full bg-gray-50 h-8 w-8 left-1 mr-4 flex-shrink-0 object-cover"
              src="${data.user.profile_picture}"
              alt="${data.user.first_name} ${data.user.last_name}'s profile picture" />
            <p>${data.user.first_name} ${data.user.last_name}:</p>
          </div>
          <p class="text-left text-black mb-4">${data.message}</p>
        `;
        messagesContainer.appendChild(messageElement);
      }
    } catch (err) {
      console.error("WebSocket message parsing failed:", err);
    }
  };

  socket.onerror = (event) => {
    console.error("WebSocket error:", event);
  };
}

// ─────────────────────────────────────────────
// 4. Team Member Management
// ─────────────────────────────────────────────

export function isUserInDatabase(userId) {
  return state.existingTeamMembers.some(member => member.userId === userId);
}

export function isUserAlreadyInTeam(userId) {
  return state.teamMembers.some(member => member.userId === userId);
}

export function addTeamMember(user) {
  const userId = user.userId;

  if (!isUserInDatabase(userId)) {
    state.existingTeamMembers.push(user);
  }

  if (!isUserAlreadyInTeam(userId)) {
    state.teamMembers.push(user);
  }

  if (!state.currentEvent.teamMembers.some(member => member.userId === userId)) {
    state.currentEvent.teamMembers.push(user);
  }
}

export function removeTeamMember(userId) {
  console.log("Removing team member:", userId);

  state.existingTeamMembers = state.existingTeamMembers.filter(member => member.userId !== userId);
  state.teamMembers = state.teamMembers.filter(member => member.userId !== userId);
  state.currentEvent.teamMembers = state.currentEvent.teamMembers.filter(member => member.userId !== userId);
}

// ─────────────────────────────────────────────
// 5. WebSocket Subscriptions
// ─────────────────────────────────────────────

export function subscribeUserToEvent(userId) {
  const eventId = state.currentEvent.id || "MISSING_EVENT_ID";

  state.currentWs?.send(JSON.stringify({
    action: "subscribe",
    event_id: eventId,
    userId,
    existingTeamMembers: state.currentEvent.teamMembers.map(member => member.userId),
  }));

  console.log(`Subscribed user ${userId} to event ${eventId}`);
}

export function unsubscribeUserFromEvent(userId) {
  const eventId = state.currentEvent.id;

  state.currentWs?.send(JSON.stringify({
    action: "unsubscribe",
    event_id: eventId,
    userId,
  }));

  console.log(`Unsubscribed user ${userId} from event ${eventId}`);
}