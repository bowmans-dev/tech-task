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
    eventOwnerId: null,
    eventOwnerDetails: null,
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
  const profilePicture = calendarWrapper?.getAttribute("data-profile-picture");
  const firstName = calendarWrapper?.getAttribute("data-first-name");
  const lastName = calendarWrapper?.getAttribute("data-last-name");

  return {
    userId: currentUserId,
    profilePicture: profilePicture,
    firstName: firstName || "User",
    lastName: lastName || "Name"
  };
}

export const currentUser = getCurrentUser();

// ─────────────────────────────────────────────
// 3. WebSocket Connection
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
      existingTeamMembers: state.existingTeamMembers,
      files: state.droppedFiles,
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

      if (data.action === "update_event") {

        // Filter out existing users before adding new ones
        const newUsers = data.teamMembers.filter(user =>
          !state.existingTeamMembers.some(
            existing => String(existing.userId) === String(user.userId)
          )
        );

        // Ensure the newUsers array is not empty before processing
        if (
          newUsers.length > 0 &&
          !state.existingTeamMembers.some(user =>
            data.teamMembers.includes(user)
          )
        ) {
          newUsers.forEach(user => addTeamMember(user));
        }

        state.droppedFiles = data.files || state.droppedFiles;

        // Check if user object exists before accessing properties
        if (data.user && data.user.profile_picture) {
            console.log("User profile picture exists:", data.user.profile_picture);
        }

        // Re-render UI with updated team members and files
        renderTeamMembers();
        renderDroppedFiles();
      }

      if (data.action === "message_broadcast") {
        const dropZone = document.getElementById("drop-zone");
        const dropZoneEventId = dropZone?.getAttribute("data-event-id");

        if (dropZoneEventId === data.event_id) {
          const messagesContainer = document.getElementById("messages");

          const isSender = parseInt(data.user.id) === parseInt(currentUser.userId);
          const alignmentClass = isSender ? "justify-end" : "justify-start";
          const backgroundClass = isSender ? "bg-[#d9fdd3]" : "bg-[#ffffff]";

          const displayName =
            data.user.type === "Admin"
              ? `(Admin) ${data.user.name}`
              : `${data.user.first_name} ${data.user.last_name}`;

          // Outer wrapper for alignment
          const wrapper = document.createElement("div");
          wrapper.className = `w-full flex ${alignmentClass}`;

          // Inner message block
          const messageElement = document.createElement("div");
          messageElement.className = `message mb-4 mt-4 text-left w-[200px] p-2 rounded-2xl shadow-md ${backgroundClass}`;

          messageElement.innerHTML = `
            <div class="flex flex-row align-center">
              <img 
                class="rounded-full bg-gray-50 h-8 w-8 left-1 mr-2 flex-shrink-0 object-cover" 
                src="${data.user.profile_picture}" 
                alt="${displayName}'s profile picture" />
              <p class="flex items-center">${displayName}:</p>
            </div>
            <p class="text-black mt-2 mb-4">${data.message}</p>
          `;

          wrapper.appendChild(messageElement);
          messagesContainer.appendChild(wrapper);
        }
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
// WebSocket Subscriptions
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

// ─────────────────────────────────────────────
// Websocket Send Event Updates
// ─────────────────────────────────────────────

export function sendEventUpdate() {
    if (!state.currentWs || state.currentWs.readyState !== WebSocket.OPEN) {
        console.warn("WebSocket connection is not open. Cannot send update.");
        return;
    }

    console.log("📡 Sending event update to WebSocket with files:", state.droppedFiles);

    const sanitizedFiles = state.droppedFiles.map(file => ({
        fileName: file.name,
        fileType: file.type,
        fileSize: (file.size / 1024).toFixed(1),
        eventId: state.currentEvent.id,
        userId: state.currentEvent.eventOwnerId
    }));

    console.log("🚀 Sanitized files being sent:", JSON.stringify(sanitizedFiles));

    state.currentWs.send(JSON.stringify({
        action: "update_event",
        event_id: state.currentEvent.id,
        teamMembers: state.existingTeamMembers,
        files: sanitizedFiles
    }));
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
// 5. UI Rendering - Team Members
// ─────────────────────────────────────────────

export function renderTeamMembers() {
    const teamMembersDiv = document.getElementById('team-members');
    if (!teamMembersDiv) return;

    teamMembersDiv.innerHTML = '';

    state.existingTeamMembers.forEach(user => {

      console.log(`🆕 Adding user to UI: ${user.firstName} ${user.lastName}`);

      let profilePicture = user.profilePicture;

      if (!profilePicture.startsWith('/storage/')) {
        profilePicture = `storage/${profilePicture}`;
      }

      const userDiv = document.createElement('div');
      userDiv.className = 'team-members relative flex items-center mb-2 mt-2 border border-gray-900/25 rounded-full p-1';
      userDiv.setAttribute('data-user-id', user.userId);
      userDiv.innerHTML = `
          <a href="/users/${user.userId}" class="cursor-pointer flex items-center">
              <img src="${profilePicture}" 
                  alt="${user.firstName} ${user.lastName}" 
                  class="w-8 h-8 rounded-full object-cover mr-2">
              <span class="text-sm font-medium text-gray-700">${user.firstName} ${user.lastName}</span>
          </a>
          <button
              class="absolute cursor-pointer top-2 right-4 text-gray-500 hover:text-gray-700 z-50" aria-label="Close Modal">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 z-50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
          </button>
      `;

      const removeButton = userDiv.querySelector('button');
      removeButton.addEventListener('click', () => {
          userDiv.remove();
          removeTeamMember(user.userId);
          removeTeamMemberFromCalendarEvent(state.currentEvent.id, user.userId);
      });

      teamMembersDiv.appendChild(userDiv);

    });

}
// ─────────────────────────────────────────────
// 5. UI Rendering - File Drops
// ─────────────────────────────────────────────


export function renderDroppedFiles() {
    const previewContainer = document.getElementById('file-preview');
    if (!previewContainer) return;

    state.droppedFiles.forEach((file, index) => {
        if (!file || Object.keys(file).length === 0) {
            console.warn(`Skipping invalid file at index ${index}:`, file);
            return;
        }

        const fileType = file.fileType || "unknown";
        const fileName = file.fileName || "Unnamed File";
        const fileSize = file.fileSize ? `${file.fileSize} KB` : "Unknown size";
        const filePath = `/storage/events/${file.eventId}/${file.userId}/${fileName}`;

        const fileDiv = document.createElement("a");
        fileDiv.href = filePath; 
        fileDiv.className = "p-3 rounded bg-gray-100 flex items-center space-x-3";

        if (fileType.startsWith("image/")) {
            const img = document.createElement("img");
            img.className = "w-12 h-12 object-cover rounded";
            setTimeout(() => {
              img.src = filePath;
            }, 1000);
            fileDiv.appendChild(img);
        } else {
            const icon = document.createElement("div");
            icon.className = "w-12 h-12 bg-gray-300 rounded flex items-center justify-center text-gray-700 font-bold";
            icon.textContent = fileType.split("/")[1]?.toUpperCase() || "FILE";
            fileDiv.appendChild(icon);
        }

        const info = document.createElement("div");
        info.innerHTML = `
            <p class="text-sm font-medium text-gray-700 max-w-[200px] truncate">${fileName}</p>
            <p class="text-xs text-gray-500">${fileType} · ${fileSize}</p>
        `;

        fileDiv.appendChild(info);
        previewContainer.appendChild(fileDiv);
    });
    state.droppedFiles = [];
}