import { currentUser } from "../state.js";
import { fetchUserEventIds } from "./utils/fetchUserEventIds.js";
import { handleMessageBroadcast } from "./handlers/messageBroadcast.js";
import { handleTeamMemberAdded } from "./handlers/teamMemberAdded.js";

let socket = null;
let retryInterval;
let retryCount = 0;
const retryDelays = [10000, 30000, 60000];

const actionMap = {
  message_broadcast: handleMessageBroadcast,
  team_member_added: handleTeamMemberAdded
};

export async function globalCalendarEventsWS() {
  if (!currentUser?.userId) {
    console.warn("WebSocket connection failed: Missing user ID.");
    return;
  }

  const userEventIds = await fetchUserEventIds();
  socket = new WebSocket("ws://localhost:8080");

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
    clearInterval(retryInterval);
  }

  async function handleSocketClose() {
    try {
      await fetch("http://localhost:8080", { method: "HEAD" });
      console.warn("Global Server is online. Reconnecting in 3 seconds...");
      retryInterval = setInterval(globalCalendarEventsWS, 3000);
    } catch {
      if (retryCount < retryDelays.length) {
        console.warn(`Global Server is down. Retrying in ${retryDelays[retryCount] / 1000}s...`);
        setTimeout(globalCalendarEventsWS, retryDelays[retryCount]);
        retryCount++;
      } else {
        console.warn("Max retry reached. No further attempts.");
      }
    }
  }

  function handleIncomingMessage(event) {
    const data = JSON.parse(event.data);
    const handler = actionMap[data.action];
    if (handler) {
      handler(data.payload);
    } 
  }
}