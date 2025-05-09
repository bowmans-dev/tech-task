import http from "http";
import { WebSocketServer, WebSocket } from "ws";

const server = http.createServer();
const wss = new WebSocketServer({ server });

server.listen(8080, () => {
  console.log("websocket server running on ws://localhost:8080");
});

server.on("error", (err) => {
    console.error("WebSocket server error:", err.message);
});

wss.on("connection", (ws, req) => {
    console.log(`Websocket client connected from ${req.socket.remoteAddress}`);
    console.log("Client connected with headers:", req.headers);
    
    ws.send(JSON.stringify({ message: "Hello from webSocket server!" }));
    
    ws.on("message", (message) => {
        console.log("Raw websocket message received:", message.toString());
        try {
            const jsonData = JSON.parse(message.toString());
            console.log("Received parsed message:", jsonData);

            wss.clients.forEach((client) => {
                if (client.readyState === WebSocket.OPEN) {
                    console.log("Broadcasting websocket message:", jsonData);
                    client.send(JSON.stringify(jsonData));
                }
            });

        } catch (error) {
            console.error("JSON parsing error:", error.message);
        }
    });

    ws.on("close", () => console.log("Client disconnected"));
    ws.on("error", (err) => console.error("WebSocket server error:", err.message));
});