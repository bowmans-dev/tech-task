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

let retryInterval;
let retryCount = 0;
const retryDelays = [10000, 30000, 60000];

export function connectToEventWebSocket() {
    const eventId = state.currentEvent?.id;
    const user = getCurrentUser();

    if (!eventId || !user?.userId) {
        console.warn("Cannot connect to WebSocket: missing eventId or userId");
        return;
    }

    const normalizedUserId = String(user.userId);
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

            if (data.action === "disconnect_from_event") {
                console.log(`User ${data.userId} has unsubscribed from ${data.event_id}`);
                return;
            }

            if (data.action === "update_event") {
                const newUsers = data.teamMembers.filter(user => 
                    !state.existingTeamMembers.some(existing => String(existing.userId) === String(user.userId))
                );

                if (newUsers.length > 0 && !state.existingTeamMembers.some(user => data.teamMembers.includes(user))) {
                    newUsers.forEach(user => addTeamMember(user));
                }

                state.droppedFiles = data.files || state.droppedFiles;

                renderTeamMembers();
                renderDroppedFiles();
            }

            if (data.action === "message_broadcast") {

                const dropZone = document.getElementById("drop-zone");
                const dropZoneEventId = dropZone?.getAttribute("data-event-id");

                if (dropZoneEventId === data.event_id) {

                    const messagesContainer = document.getElementById("messages");
                    const temp = document.createElement("div");
                    temp.innerHTML = data.html.trim();

                    const messageEl = temp.firstElementChild;
                    if (messageEl) {
                        messagesContainer.appendChild(messageEl);
                    }

                }
            }


            if (data.action === 'reaction_broadcast') {

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

            if (data.action === 'vote_broadcast') {

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

			if (data.action === "send_offer") {
				const { eventId, toUserId, broadcastingUserDetails } = data.payload;

				const stream = state.media.localStream;
				if (!stream) {
					console.warn("No local stream found");
					return;
				}

				const peerConnection = new RTCPeerConnection({
					iceServers: [
						{ urls: "stun:stun.l.google.com:19302" },
						{ urls: "stun:stun1.l.google.com:19302" }
					]
				});

				if (!state.media.peerConnectionsByUserId) {
					state.media.peerConnectionsByUserId = {};
				}

				state.media.peerConnectionsByUserId[toUserId] = peerConnection;

				stream.getTracks().forEach(track => {
					const transceiver = peerConnection.addTransceiver(track.kind, {direction: "sendrecv"});
					transceiver.sender.replaceTrack(track);
				});

				peerConnection.onicecandidate = (event) => {
					if (event.candidate) {
						socket.send(JSON.stringify({
							action: "ice_candidate",
							payload: {
								candidate: event.candidate,
								toUserId,
								fromUserId: currentUser.userId,
								eventId
							}
						}));
					}
				};

				const offer = await peerConnection.createOffer();
				await peerConnection.setLocalDescription(offer);

				socket.send(JSON.stringify({
					action: "send_offer",
					payload: {
						offer: peerConnection.localDescription,
						eventId,
						toUserId,
						userId: currentUser.userId,
                        broadcastingUserDetails: broadcastingUserDetails,
					}
				}));
			}



			if (data.action === "receive_offer") {

				const { offer, fromUserId, toUserId, broadcastingUserDetails, eventId } = data.payload;

				const peerConnection = new RTCPeerConnection({
					iceServers: [{ urls: "stun:stun.l.google.com:19302" },{ urls: "stun:stun1.l.google.com:19302" }]
				});

				state.media.peerConnectionsByUserId[fromUserId] = peerConnection;

                peerConnection.addTransceiver('audio', { direction: 'recvonly' });
                peerConnection.addTransceiver('video', { direction: 'recvonly' });

				peerConnection.ontrack = (event) => {
                    const container = document.getElementById('messages');

                    if (event.track.kind === "audio") {
                        const audio = new Audio();
                        audio.srcObject = new MediaStream([event.track]);
                        audio.muted = false;
                        audio.autoplay = true;
                        audio.play().catch(err => console.error("Audio playback error:", err));
                        return;
                    }

                    if (event.track.kind === "video" || event.track.kind === "screen") {
                        const video = document.createElement('video');
                        video.srcObject = new MediaStream([event.track]);
                        video.autoplay = true;
                        video.playsInline = true;
                        video.muted = false;
                        const videoMessage = createVideoMessageWrapper(broadcastingUserDetails, event.track);
		                container.appendChild(videoMessage);

                        // Remove video element when the track ends
                        event.track.onended = () => {
                            container.removeChild(videoMessage);
                            peerConnection.close();
                        };
                        event.track.oninactive = () => {
                            container.removeChild(videoMessage);
                            peerConnection.close();
                        };
                        event.track.onmute = () => {
                            container.removeChild(videoMessage);
                            peerConnection.close();
                        };

                        video.play().catch(err => console.error("Video playback error:", err));
                    }
                };


				peerConnection.onicecandidate = (event) => {
					if (event.candidate) {
						socket.send(JSON.stringify({
							action: "ice_candidate",
							payload: {
							candidate: event.candidate,
							toUserId: fromUserId,
							fromUserId: toUserId,
							eventId
							}
						}));
					}
				};

				await peerConnection.setRemoteDescription(new RTCSessionDescription(offer));

				// Flush buffered ICE candidates
				const pendingCandidates = state.media.pendingIceCandidates[fromUserId];

				if (pendingCandidates && pendingCandidates.length > 0) {
					for (const c of pendingCandidates) {
						try {
							await peerConnection.addIceCandidate(new RTCIceCandidate(c));
						} catch (e) {
							console.error("Failed to apply buffered ICE candidate:", e);
						}
					}
					delete state.media.pendingIceCandidates[fromUserId];
				}

				const answer = await peerConnection.createAnswer();
				await peerConnection.setLocalDescription(answer);

				socket.send(JSON.stringify({
					action: "send_answer",
					payload: {
					answer,
					toUserId: fromUserId,
					fromUserId: toUserId,
					eventId
					}
				}));
			}



            if (data.action === "receive_answer") {

                const { answer, fromUserId } = data.payload;
                const peerConnection = state.media.peerConnectionsByUserId?.[fromUserId];

                if (!peerConnection) {
                    console.warn("No peer connection found for answer from", fromUserId);
                    return;
                }

                try {

                    await peerConnection.setRemoteDescription(new RTCSessionDescription(answer));

                    const pendingCandidates = state.media.pendingIceCandidates?.[fromUserId];
                    if (pendingCandidates && pendingCandidates.length > 0) {
                        for (const c of pendingCandidates) {
                            try {
                                await peerConnection.addIceCandidate(new RTCIceCandidate(c));
                            } catch (e) {
                                console.error("Failed to apply buffered ICE candidate:", e);
                            }
                        }
                        delete state.media.pendingIceCandidates[fromUserId];
                    }

                } catch (err) {
                    console.error("Failed to set remote description from answer:", err);
                }
            }

            if (data.action === "ice_candidate") {

                const { candidate, fromUserId } = data.payload;

                const peerConnection = state.media.peerConnectionsByUserId[fromUserId];

                if (peerConnection) {

                    if (peerConnection.remoteDescription && peerConnection.remoteDescription.type) {
                        await peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
                    } else {

                        if (!state.media.pendingIceCandidates[fromUserId]) {
                            state.media.pendingIceCandidates[fromUserId] = [];
                        }
                        state.media.pendingIceCandidates[fromUserId].push(candidate);
                    }

                } else {

                    if (!state.media.pendingIceCandidates[fromUserId]) {
                        state.media.pendingIceCandidates[fromUserId] = [];
                    }
                    state.media.pendingIceCandidates[fromUserId].push(candidate);
                }

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
        userId: state.currentEvent.eventOwnerId
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

function createVideoMessageWrapper(broadcastingUserDetails, track) {
	const wrapper = document.createElement("div");
	wrapper.className = "message-wrapper w-full flex relative justify-start";

	const messageBubble = document.createElement("div");
	messageBubble.className = "message bubble text-left min-w-[200px] p-2 mb-4 rounded-2xl relative shadow-md cursor-pointer transition-all duration-200 hover:mb-10 bg-[#ffffff]";

	const header = document.createElement("div");
	header.className = "flex flex-row align-center mb-2";

	const profileImg = document.createElement("img");
	profileImg.className = "rounded-full bg-gray-50 h-8 w-8 left-1 mr-2 flex-shrink-0 object-cover";
	profileImg.src = `/storage/${broadcastingUserDetails?.profilePicture}` || "/storage/default_profile_image.webp";
	profileImg.alt = `${broadcastingUserDetails?.firstName || "User"} ${broadcastingUserDetails?.lastName || ""}'s profile picture`;

	const nameDiv = document.createElement("div");
	nameDiv.className = "flex items-center text-sm font-medium text-gray-800";
	nameDiv.textContent = `${broadcastingUserDetails?.firstName || "Unknown"} ${broadcastingUserDetails?.lastName || ""}`;

	header.appendChild(profileImg);
	header.appendChild(nameDiv);

	const video = document.createElement("video");
	video.srcObject = new MediaStream([track]);
	video.autoplay = true;
	video.playsInline = true;
	video.muted = false;
	video.className = "w-full rounded-xl mt-2";

	// Remove when the track ends or goes inactive
	track.onended = () => wrapper.remove();
	track.oninactive = () => wrapper.remove();

	messageBubble.appendChild(header);
	messageBubble.appendChild(video);
	wrapper.appendChild(messageBubble);

	// Fullscreen toggle on click
	messageBubble.addEventListener("click", async (e) => {
		// Only trigger if the user clicked outside the header (to avoid fullscreen when clicking the name/image)
		if (e.target.closest("video")) {
			if (!document.fullscreenElement) {
				await messageBubble.requestFullscreen().catch(err => {
					console.error("Failed to enter fullscreen:", err);
				});
			} else {
				await document.exitFullscreen().catch(err => {
					console.error("Failed to exit fullscreen:", err);
				});
			}
		}
	});

    setTimeout(() => {
        const dropZone = document.getElementById("drop-zone");
        if (dropZone) {
            dropZone.scrollTo({ top: dropZone.scrollHeight, behavior: "smooth" });
        }
    }, 1000);

    setTimeout(() => {
        const messageContainer = document.getElementById("messages");
        const messages = messageContainer.querySelectorAll(".bubble");
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

    }, 1000);

	return wrapper;
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

// ─────────────────────────────────────────────
// 5. UI Rendering - Team Members
// ─────────────────────────────────────────────

export function renderTeamMembers() {
	const teamMembersDiv = document.getElementById('team-members');
	if (!teamMembersDiv) return;

	teamMembersDiv.innerHTML = '';

	state.existingTeamMembers.forEach(user => {

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
	const handleRemoveClick = () => {
		userDiv.remove();
		removeTeamMember(user.userId);
		removeTeamMemberFromCalendarEvent(state.currentEvent.id, user.userId);
	};

	removeButton.removeEventListener('click', handleRemoveClick);
	removeButton.addEventListener('click', handleRemoveClick);

	teamMembersDiv.appendChild(userDiv);

	});
	state.teamMembers = [];
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