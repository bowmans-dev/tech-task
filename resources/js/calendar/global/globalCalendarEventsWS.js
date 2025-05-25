import { currentUser } from "../state.js";

let socket = null;

async function fetchUserEventIds() {
    const response = await fetch("/calendar/events"); // Calls `getUserEvents`
    const events = await response.json();
    return events.map(event => event.id); // Extract event IDs
}

let retryInterval;
let retryCount = 0;
const retryDelays = [10000, 30000, 60000];

async function globalCalendarEventsWS() {
    if (!currentUser?.userId) {
        console.warn("WebSocket connection failed: Missing user ID.");
        return;
    }

    const userEventIds = await fetchUserEventIds();
    socket = new WebSocket("ws://localhost:8080");

    // Remove existing listeners before adding new ones
    socket.removeEventListener("open", handleSocketOpen);
    socket.removeEventListener("message", handleIncomingMessage);
    socket.removeEventListener("close", handleSocketClose);

    socket.addEventListener("open", handleSocketOpen);
    socket.addEventListener("message", handleIncomingMessage);
    socket.addEventListener("close", handleSocketClose);

    function handleSocketOpen() {
        console.log(`Connected as user ${currentUser.userId}`);
        socket.send(JSON.stringify({ action: "online", userId: currentUser.userId, eventIds: userEventIds }));
        retryCount = 0;
        clearInterval(retryInterval); // Stop polling when connected
    }

    async function handleSocketClose() {

        // Check if the server is online before deciding retry strategy
        try {
            await fetch("http://localhost:8080", { method: "HEAD" }); // Ping server
            console.warn("Global Server is online. Reconnecting in 3 seconds...");
            retryInterval = setInterval(globalCalendarEventsWS, 3000);
        } catch {
            if (retryCount < retryDelays.length) {
                console.warn(`Global Server is down. Retrying in ${retryDelays[retryCount] / 1000}s...`);
                setTimeout(globalCalendarEventsWS, retryDelays[retryCount]); // Exponential backoff
                retryCount++;
            } else {
                console.warn("Max retry reached. No further attempts.");
            }
        }
    };
}


function handleIncomingMessage(event) {
    const data = JSON.parse(event.data);

    if (data.action === "message_broadcast") {

        if (data.payload) {

            const payload = data.payload;
            
            showMessageNotification({
                eventId: payload.eventId,
                eventName: payload.eventName,
                message: payload.message,
                user: payload.user
            });
            
            const calendarEvent = window.calendar.getEventById(payload.eventId);
            if (calendarEvent) {
                calendarEvent.setProp("backgroundColor", "#2D89EF");
            }
        }
    }

    if (data.action === "team_member_added") {

        calendar.refetchEvents();

        if (data.payload) {

        const payload = data.payload;

            setTimeout(() => {
                showTeamMemberNotification({
                    eventId: payload.eventId,
                    eventName: payload.eventName,
                    message: payload.message,
                    user: payload.user
                });
                const event = window.calendar.getEventById(payload.eventId);
                if (event) {
                    event.setProp("backgroundColor", "#2D89EF");
                }
            }, 1000);
        }
    }
}

function showMessageNotification(payload) {

    const notificationContainer = document.getElementById("notification-container");
    const notification = document.createElement("div");
    notification.className = "notification block cursor-pointer";

    const truncatedMessage = payload.message.length > 80 
        ? payload.message.substring(0, 80) + "..." 
        : payload.message;

    notification.innerHTML = `
    <div>
        <div class="flex align-center mb-2">
            <div class="rounded-full bg-[#e0e0e0]">
                <img src="${payload.user.profile_picture}" alt="${payload.user.first_name}'s profile picture">
            </div>
            <div class="notification-title">
                <div>${payload.user.first_name} ${payload.user.last_name}</div>
                <div class="text-[10px] leading-[10px]">${payload.eventName}</div>
            </div>
        </div>
        <div class="notification-content">
            <div class="notification-message">✨ ${truncatedMessage}</div>
        </div>
    </div>
    `;

    notificationContainer.appendChild(notification);
    setupNotificationBehavior(notification, payload.eventId);
    return;
}

function showTeamMemberNotification(payload) {
    const notificationContainer = document.getElementById("notification-container");
    const notification = document.createElement("div");
    notification.className = "notification block cursor-pointer";

    notification.innerHTML = `
    <div>
        <div class="flex align-center mb-2">
            <div class="rounded-full bg-[#e0e0e0]">
                <img src="/storage/${payload.user.profile_picture}" alt="${payload.user.first_name}'s profile picture">
            </div>
            <div class="notification-title">
                <div>${payload.user.first_name} ${payload.user.last_name}</div>
                <div class="text-[10px] leading-[10px]">${payload.eventName}</div>
            </div>
        </div>
        <div class="notification-content">
            <div class="notification-message">✨ You were added to <strong>${payload.eventName}</strong></div>
        </div>
    </div>
    `;

    notificationContainer.appendChild(notification);
    setupNotificationBehavior(notification, payload.eventId);
    return;
}

function setupNotificationBehavior(notification, eventId) {
    notification.removeEventListener("click", handleNotificationClick);
    notification.addEventListener("click", handleNotificationClick);

    function handleNotificationClick() {
        openEventModal(eventId);
    }

    setTimeout(() => notification.classList.add("show"), 100);
    setTimeout(() => {
        notification.classList.remove("show");
        setTimeout(() => notification.remove(), 500);
    }, 10000);
}

function openEventModal(eventId) {
    const event = window.calendar.getEventById(eventId);
    if (event) {
        event.setProp("backgroundColor", "#2D89EF");
        window.calendar.trigger("eventClick", { event: event, el: event._def.ui.classNames });

        setTimeout(() => {
            const dropZone = document.getElementById("drop-zone");
            if (dropZone) {
                dropZone.scrollTo({ top: dropZone.scrollHeight, behavior: "smooth" });
            }
        }, 1000);

        setTimeout(() => {
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

            event.setProp("backgroundColor", null);
        }, 1000);
    } else {
        console.warn(`Event ID ${eventId} not found.`);
    }
}

export { globalCalendarEventsWS };