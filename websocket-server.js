import http from "http";
import { WebSocketServer, WebSocket } from "ws";
import monitor from "./websocket/monitor.js";
import internal from "./websocket/internal.js";
import WebSocketDispatcher from "./websocket/dispatcher.js";

const server = http.createServer();
const wss = new WebSocketServer({ server });

const globalConnectedUsers = new Map(); // Map<userId, WebSocket>
const eventScopedConnections = new Map(); // Map<eventId, Set<userId>>

const dispatcher = new WebSocketDispatcher({
    globalConnectedUsers,
    eventScopedConnections,
    wss,
    WebSocket,
});


wss.on("connection", (ws, req) => {

    ws.allEventIds = [];
    ws.connectedEvent = [];

    if (req.url === "/monitor") {
        monitor(ws, wss, eventScopedConnections);
        return;
    }

    if (req.url === "/internal") {
        internal(ws, wss, eventScopedConnections, globalConnectedUsers);
        return;
    }

    ws.on("message", (message) => {
        try {
            const jsonData = JSON.parse(message.toString());
            dispatcher.handle(ws, jsonData);
        } catch (error) {
            console.error("JSON parsing error:", error.message);
        }
    });

    ws.on("error", (err) => console.error("WebSocket server error:", err.message));

    ws.on("close", () => {

        if (ws.userId) {
            globalConnectedUsers.delete(ws.userId);
        }


        (ws.connectedEvent || []).forEach(({ userId, eventId }) => {
            const connections = eventScopedConnections.get(eventId);
            if (!connections) return;

            connections.delete(userId);

            if (connections.size === 0) {
                eventScopedConnections.delete(eventId);
            }
        });
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

export { server, wss };