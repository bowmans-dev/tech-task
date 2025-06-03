import http from "http";
import { WebSocketServer, WebSocket } from "ws";
import monitor from "./websocket/monitor.js";
import internal from "./websocket/internal.js";

import online from "./websocket/actions/online.js";
import subscribe from "./websocket/actions/subscribe.js";
import unsubscribe from "./websocket/actions/unsubscribe.js";
import updateEvent from "./websocket/actions/updateEvent.js";
import teamMemberAdded from "./websocket/actions/teamMemberAdded.js";
import messageBroadcast from "./websocket/actions/messageBroadcast.js";
import reactionBroadcast from "./websocket/actions/reactionBroadcast.js";
import voteBroadcast from "./websocket/actions/voteBroadcast.js";
import startMediaBroadcast from "./websocket/actions/startMediaBroadcast.js";
import sendOffer from "./websocket/actions/sendOffer.js";
import sendAnswer from "./websocket/actions/sendAnswer.js";
import forwardIceCandidate from "./websocket/actions/forwardIceCandidate.js";



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
        team_member_added:     (ws, data) => teamMemberAdded(ws, data, globalConnectedUsers, wss, notifyTeamMembers, WebSocket),
        connect_to_event:      (ws, data) => subscribe(ws, data, eventScopedConnections),
        disconnect_from_event: (ws, data) => unsubscribe(ws, data, eventScopedConnections, wss),
        update_event:          (ws, data) => updateEvent(data, eventScopedConnections, wss),
        message_broadcast:     (ws, data) => messageBroadcast(data, eventScopedConnections, wss),
        reaction_broadcast:    (ws, data) => reactionBroadcast(data, eventScopedConnections, wss),
        vote_broadcast:        (ws, data) => voteBroadcast(data, eventScopedConnections, wss),
        start_media_broadcast: (ws, data) => startMediaBroadcast(ws, data, eventScopedConnections, WebSocket, wss),
        send_offer:            (ws, data) => sendOffer(ws, data, eventScopedConnections, WebSocket, wss),
        send_answer:           (ws, data) => sendAnswer(ws, data, eventScopedConnections, WebSocket, wss),
        ice_candidate:         (ws, data) => forwardIceCandidate(ws, data, eventScopedConnections, WebSocket, wss),
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


function notifyTeamMembers(eventId, senderId, actionType, payload, wss) {
    const { eventName, message = "", created_at = null, user } = payload;

    for (const client of wss.clients) {
        const isNotSender = String(client.userId) !== String(senderId);
        const isSubscribed = client.allEventIds?.includes(eventId);

        if (client.readyState === WebSocket.OPEN && isSubscribed && isNotSender) {
            client.send(JSON.stringify({
                action: actionType,
                payload: {
                    eventId,
                    eventName,
                    message,
                    created_at,
                    user
                }
            }));
        }
    }
}


export { server, wss };