import https from "https";
import fs from "fs";
import { WebSocketServer } from "ws";
import monitor from "./websocket/monitor.js";
import internal from "./websocket/internal.js";
import WebSocketDispatcher from "./websocket/dispatcher.js";
import { verifyToken } from "./websocket/auth/verifyToken.js";

const server = https.createServer({
    key: fs.readFileSync('./localhost-key.pem'),
    cert: fs.readFileSync('./localhost.pem'),
});

const wss = new WebSocketServer({ server });

const globalConnectedUsers = new Map(); // Map<userId, WebSocket>
const eventScopedConnections = new Map(); // Map<eventId, Set<userId>>

const dispatcher = new WebSocketDispatcher({
    globalConnectedUsers,
    eventScopedConnections,
    wss,
});


wss.on("connection", (ws, req) => {

    const protocols = req.headers["sec-websocket-protocol"];
    const token = Array.isArray(protocols) ? protocols[0] : protocols;
    
    const result = verifyToken(token);

    if (result.error) {
        console.warn("Connection rejected:", result.error);
        ws.close(result.code, result.error);
        return;
    }

    ws.userId = result.payload.sub;
    ws.allEventIds = [];
    ws.connectedEvent = [];

    if (req.url === "/internal") {
        internal(ws, wss, eventScopedConnections, globalConnectedUsers);
        return;
    }

    if (req.url === "/monitor") {
        monitor(ws, wss, eventScopedConnections);
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
    console.log("WebSocket server running on wss://localhost:8080");
});

let isShuttingDown = false;

function shutdown() {
  if (isShuttingDown) return;
  isShuttingDown = true;

  console.log("Shutting down...");

  wss.clients.forEach((client) => client.terminate());

  wss.close(() => {
    server.close(() => {
      process.exit(0);
    });
  });
}

process.once("SIGINT", shutdown);

export { server, wss };