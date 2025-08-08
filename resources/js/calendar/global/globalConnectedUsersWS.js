import { currentUser } from "../local/state.js";
import { fetchUserEventIds } from "./utils/fetchUserEventIds.js";
import { handleMessageBroadcast } from "./handlers/messageBroadcast.js";
import { handleTeamMemberAdded } from "./handlers/teamMemberAdded.js";
import { getWebSocketToken } from "../local/state.js";

let socket = null;
let retryInterval = null;
let retryCount = 0;
const retryDelays = [10000, 30000, 60000];
let reconnecting = false;

const actionMap = {
  message_broadcast: handleMessageBroadcast,
  team_member_added: handleTeamMemberAdded,
};

async function handleSocketOpen() {
  try {
    const userEventIds = await fetchUserEventIds();
    console.log(`Connected as user ${currentUser.userId}`);
    socket.send(JSON.stringify({ action: "online", userId: currentUser.userId, eventIds: userEventIds }));
    retryCount = 0;
    reconnecting = false;
    if (retryInterval) {
      clearInterval(retryInterval);
      retryInterval = null;
    }
  } catch (err) {
    console.error("Error in handleSocketOpen:", err);
  }
}

async function handleSocketClose() {
  if (reconnecting) return;
  reconnecting = true;

  try {
    await fetch("https://localhost:8080", { method: "HEAD" });
    console.warn("Global Server is online. Reconnecting in 3 seconds...");
    retryInterval = setTimeout(() => {
      reconnecting = false;
      globalConnectedUsersWS();
    }, 3000);
  } catch {
    if (retryCount < retryDelays.length) {
      const delay = retryDelays[retryCount];
      console.warn(`Global Server is down. Retrying in ${delay / 1000}s...`);
      retryInterval = setTimeout(() => {
        reconnecting = false;
        globalConnectedUsersWS();
      }, delay);
      retryCount++;
    } else {
      console.warn("Max retry reached. No further attempts.");
    }
  }
}

function handleIncomingMessage(event) {
  try {
    const data = JSON.parse(event.data);
    const handler = actionMap[data.action];
    if (handler) {
      handler(data.payload);
    }
  } catch (err) {
    console.error("Failed to handle incoming message:", err);
  }
}

export async function globalConnectedUsersWS() {
  if (!currentUser?.userId) {
    console.warn("WebSocket connection failed: Missing user ID.");
    return;
  }

  if (socket) {
    socket.removeEventListener("open", handleSocketOpen);
    socket.removeEventListener("message", handleIncomingMessage);
    socket.removeEventListener("close", handleSocketClose);

    if (socket.readyState === WebSocket.OPEN || socket.readyState === WebSocket.CONNECTING) {
      socket.close();
    }
  }

  const token = await getWebSocketToken();
  if (!token) {
    console.error("No token available for WebSocket connection");
    return;
  }
  socket = new WebSocket(`wss://localhost:8080`, [token]);

  socket.addEventListener("open", handleSocketOpen);
  socket.addEventListener("message", handleIncomingMessage);
  socket.addEventListener("close", handleSocketClose);
}