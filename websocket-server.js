import http from "http";
import { WebSocketServer } from "ws";

const server = http.createServer();
const wss = new WebSocketServer({ server });


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
        console.log("Websocket server already active, skipping startup.");
    }
});


wss.on("connection", (ws) => {

    ws.existingTeamMembers = [];

    ws.on("message", (message) => {
        try {
            const jsonData = JSON.parse(message.toString());

            if (jsonData.action === "test_message") {
                ws.send(JSON.stringify({ reply: `Received: ${jsonData.content}` }));
            }

            if (jsonData.action === "subscribe") {
                ws.userId = jsonData.userId || null;
                ws.eventId = jsonData.event_id || "MISSING";
                ws.existingTeamMembers = jsonData.existingTeamMembers || [];
            }

            if (jsonData.action === "message_broadcast") {

                const validClients = [...wss.clients].filter(client => client.userId && Array.isArray(client.existingTeamMembers));

                if (validClients.length === 0) {
                    console.error("No valid websocket clients found, skipping broadcast.");
                    return;
                }

                validClients.forEach((client) => {

                    if (client.eventId === jsonData.event_id && client.existingTeamMembers.some(({ userId }) => userId === client.userId)) {
                        client.send(JSON.stringify(jsonData));
                    } 
                });
            }
        } catch (error) {
            console.error("JSON parsing error:", error.message);
        }
    });

    ws.on("error", (err) => console.error("WebSocket server error:", err.message));
});

// shut down websocket server on exit
process.on("SIGINT", () => {
    
    wss.clients.forEach((client) => client.terminate());
    
    wss.close(() => {
        server.close(() => {
            process.exit(0);
        });
    });
});

export { server, wss };