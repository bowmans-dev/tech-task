import http from "http";
import { WebSocketServer } from "ws";
import monitor from "./websocket/monitor.js";
import internal from "./websocket/internal.js";

import online from "./websocket/actions/online.js";
import subscribe from "./websocket/actions/subscribe.js";
import unsubscribe from "./websocket/actions/unsubscribe.js";
import updateEvent from "./websocket/actions/updateEvent.js";
import messageBroadcast from "./websocket/actions/messageBroadcast.js";


const server = http.createServer();
const wss = new WebSocketServer({ server });

const globalConnectedUsers = {};
const eventScopedConnections = {}; 

wss.on("connection", (ws, req) => {

    ws.allEventIds = [];
    ws.connectedEvent = [];

    if (req.url === "/monitor") {
        monitor(ws, wss, eventScopedConnections);
        return;
    }

    if (req.url === "/internal") {
        internal(ws, wss, eventScopedConnections, notifyTeamMembers, globalConnectedUsers);
        return;
    }

    const actionHandlers = {
        online:                (ws, data) => online(ws, data, globalConnectedUsers),
        connect_to_event:      (ws, data) => subscribe(ws, data, eventScopedConnections),
        disconnect_from_event: (ws, data) => unsubscribe(ws, data, eventScopedConnections, wss),
        update_event:          (ws, data) => updateEvent(data, eventScopedConnections, wss),
        message_broadcast:     (ws, data) => messageBroadcast(data, eventScopedConnections, wss),
    };

    ws.on("message", (message) => {
        try {
            const jsonData = JSON.parse(message.toString());
            const handler = actionHandlers[jsonData.action];
            if (handler) handler(ws, jsonData);
        } catch (error) {
            console.error("JSON parsing error:", error.message);
        }
    });

    ws.on("error", (err) => console.error("WebSocket server error:", err.message));

    ws.on("close", () => {
        Object.keys(globalConnectedUsers).forEach(userId => {
            if (globalConnectedUsers[userId] === ws) delete globalConnectedUsers[userId];
        });
        if (ws.connectedEvent) {
            ws.connectedEvent.forEach(({ userId, eventId }) => {
                if (eventScopedConnections[eventId]) {
                    eventScopedConnections[eventId] = eventScopedConnections[eventId].filter(id => id !== userId);
                    if (eventScopedConnections[eventId].length === 0) {
                        delete eventScopedConnections[eventId];
                    }
                }
            });
        }
    });
});

server.on("error", (err) => {
    if (err.code === "EADDRINUSE") {
        console.log("WebSocket server is already running on port 8080");
    } else {
        console.error("WebSocket server error:", err.message);
    }
});

server.listen(8080, () => {
    console.log("WebSocket server running on ws://localhost:8080");
}).on("error", (err) => {
    if (err.code === "EADDRINUSE") {
        console.log("WebSocket server already active, skipping startup.");
    }
});

process.on("SIGINT", () => {
    wss.clients.forEach((client) => client.terminate());
    wss.close(() => {
        server.close(() => {
            process.exit(0);
        });
    });
});

function notifyTeamMembers(eventId, action, payload) {

    const senderId = String(payload.user.id);

    const recipients = Object.values(globalConnectedUsers).filter(client => 
        client.allEventIds.some(id => id == eventId) && String(client.userId) !== senderId
    );;

    recipients.forEach(client => {
        client.send(JSON.stringify({ action, payload }));
    });
}

export { server, wss };