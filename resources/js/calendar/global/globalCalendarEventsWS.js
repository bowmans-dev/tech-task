import { currentUser } from "../state.js";

let socket = null;

async function fetchUserEventIds() {
    const response = await fetch("/calendar/events"); // Calls `getUserEvents`
    const events = await response.json();
    return events.map(event => event.id); // Extract event IDs
}

async function globalCalendarEventsWS() {
    if (!currentUser?.userId) {
        console.warn("WebSocket connection failed: Missing user ID.");
        return;
    }

    const userEventIds = await fetchUserEventIds(); // Get events for the user

    socket = new WebSocket("ws://localhost:8080");

    socket.addEventListener("open", () => {
        console.log(`Connected to Global Notifications WebSocket as user ${currentUser.userId}`);
        socket.send(JSON.stringify({ action: "online", userId: currentUser.userId, eventIds: userEventIds }));
    });

    socket.addEventListener("message", handleIncomingMessage);

    // Automatic reconnect if disconnected
    socket.addEventListener("close", () => {
        console.warn("WebSocket disconnected, attempting reconnect...");
        setTimeout(globalCalendarEventsWS, 3000);
    });
}

function handleIncomingMessage(event) {
    const data = JSON.parse(event.data);

    if (data.action === "message_broadcast") {
        showNotification(data.payload);
        const event = window.calendar.getEventById(data.payload.eventId);
        if (event) {
            event.setProp("backgroundColor", "#2D89EF");
        }
    }
}

function showNotification(payload) {
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

    notification.addEventListener("click", () => {
        openEventModal(payload.eventId);
    });

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

            if (lastMessage) {
                lastMessage.classList.add("highlight-message");
                setTimeout(() => lastMessage.classList.remove("highlight-message"), 3000);
            }

            event.setProp("backgroundColor", null);
        }, 1000);
    } else {
        console.warn(`Event ID ${eventId} not found.`);
    }
}

export { globalCalendarEventsWS };