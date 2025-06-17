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
		eventOwnerDetails: null,
		title: '',
		date: '',
		time: '',
		allDay: false,
		files: [],
		teamMembers: [],
		isNew: true,
	},
	media: {
		type: null,
		localStream: null,
		peerConnectionsByUserId: {},
		pendingIceCandidates: {},
	},
};

// ─────────────────────────────────────────────
// 2. Current User Initialization
// ─────────────────────────────────────────────

const calendar = document.getElementById("calendar-wrapper");
const currentUserId = calendar?.getAttribute("data-user-id") ?? "admin";

export function getCurrentUser() {
	const profilePicture = calendar?.getAttribute("data-profile-picture");
	const firstName = calendar?.getAttribute("data-first-name");
	const lastName = calendar?.getAttribute("data-last-name");

	return {
        eventOwnerId: currentUserId,
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

import { websocketActionHandlers } from './local/websocketActionHandlers.js';
import { webRtcActionHandlers } from './local/webRtcActionHandlers.js';

let retryInterval;
let retryCount = 0;
const retryDelays = [10000, 30000, 60000];

export function connectToEventWebSocket() {
    const eventId = state.currentEvent?.id;

    if (!eventId || !currentUser?.userId) {
        console.warn("Cannot connect to WebSocket: missing eventId or userId");
        return;
    }

    const normalizedUserId = String(currentUser.userId);
    const socket = new WebSocket("ws://localhost:8080");

    if (state.currentWs) {
        state.currentWs.close();
    }

    state.currentWs = socket;

    socket.onopen = () => {
        socket.send(JSON.stringify({
            action: "connect_to_event",
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
        retryCount = 0;
        clearInterval(retryInterval);
    };

    socket.onmessage = async (message) => {

        try {
            const data = JSON.parse(message.data);

            const handler =
            websocketActionHandlers[data.action] ||
            webRtcActionHandlers[data.action];

            if (handler) {
                await handler(data);
            }

        } catch (err) {
            console.error("WebSocket message parsing failed:", err);
        }
    };

    socket.onerror = (event) => {
        if (socket.readyState !== 3) {  // Filter out standard error due to offline server
            console.error("WebSocket error:", event);
        }
    };

    socket.onclose = async () => {
        // Check if the server is online before deciding retry strategy
        try {
            await fetch("http://localhost:8080", { method: "HEAD" }); // Ping server
            console.warn("Event Server is online. Reconnecting in 3 seconds...");
            retryInterval = setInterval(connectToEventWebSocket, 3000);
        } catch {
            if (retryCount < retryDelays.length) {
                console.warn(`Event Server is down. Retrying in ${retryDelays[retryCount] / 1000}s...`);
                setTimeout(connectToEventWebSocket, retryDelays[retryCount]); // Exponential backoff
                retryCount++;
            } else {
                console.warn("Max retry reached. No further attempts.");
            }
        }
    };
}

// ─────────────────────────────────────────────
// WebSocket Subscriptions
// ─────────────────────────────────────────────

export function subscribeUserToEvent(userId) {
  const eventId = state.currentEvent.id || "MISSING_EVENT_ID";

  state.currentWs?.send(JSON.stringify({
    action: "connect_to_event",
    event_id: eventId,
    userId,
    existingTeamMembers: state.currentEvent.teamMembers.map(member => member.userId),
  }));
}

export function unsubscribeUserFromEvent(userId) {
  const eventId = state.currentEvent.id;

  state.currentWs?.send(JSON.stringify({
    action: "disconnect_from_event",
    event_id: eventId,
    userId,
  }));
}

// ─────────────────────────────────────────────
// Websocket Send Event Updates
// ─────────────────────────────────────────────

export function sendEventUpdate() {
    if (!state.currentWs || state.currentWs.readyState !== WebSocket.OPEN) {
        console.warn("WebSocket connection is not open. Cannot send update.");
        return;
    }

    const sanitizedFiles = state.droppedFiles.map(file => ({
        fileName: file.name,
        fileType: file.type,
        fileSize: (file.size / 1024).toFixed(1),
        eventId: state.currentEvent.id,
        userId: state.currentEvent.eventOwnerDetails.eventOwnerId
    }));

    state.currentWs.send(JSON.stringify({
        action: "update_event",
        event_id: state.currentEvent.id,
        teamMembers: state.existingTeamMembers,
        files: sanitizedFiles
    }));
}

// ─────────────────────────────────────────────
// 4. WebRTC Management
// ─────────────────────────────────────────────


export async function startMediaStream({ stream, type }) {
	const eventId = state.currentEvent?.id;
	const eventTitle = state.currentEvent?.title;

	if (!eventId || !currentUser) {
		console.warn("Missing event or user context");
		return;
	}

	state.media.localStream = stream;
	state.media.type = type;

	// Notify server that broadcast is starting (include type)
	state.currentWs.send(JSON.stringify({
		action: "start_media_broadcast",
		payload: {
		type,
		eventId,
		eventTitle,
		userId: currentUser.userId,
		currentUser: currentUser,
		},
	}));
}

// ─────────────────────────────────────────────
// 5. Team Member Management
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

	if (state.currentEvent.teamMembers && !state.currentEvent.teamMembers.some(member => member.userId === userId)) {
		state.currentEvent.teamMembers.push(user);
	}
}

export function removeTeamMember(userId) {

	state.existingTeamMembers = state.existingTeamMembers.filter(member => member.userId !== userId);
	state.teamMembers = state.teamMembers.filter(member => member.userId !== userId);
	state.currentEvent.teamMembers = state.currentEvent.teamMembers.filter(member => member.userId !== userId);

}

export function notifyNewTeamMembers() {
  
    if (!state.currentWs || state.currentWs.readyState !== WebSocket.OPEN) return;

    const event = window.calendar.getEventById(state.currentEvent.id);
    if (!event) return;

    const owner = event.extendedProps.eventOwnerDetails;
    const newMembers = state.teamMembers;

    newMembers.forEach(member => {
        state.currentWs.send(JSON.stringify({
            action: "team_member_added",
            event_id: state.currentEvent.id,
            eventName: event.title,
            newMemberId: member.userId,
            user: {
                first_name: owner.firstName,
                last_name: owner.lastName,
                profile_picture: owner.profilePicture || "/storage/default_profile_image.webp"
            }
        }));
    });
}