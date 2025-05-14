import http from "http";
import { WebSocketServer, WebSocket } from "ws";

const server = http.createServer();
const wss = new WebSocketServer({ server });

const eventSubscriptions = {}; // Tracks subscribed users per event ID

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

wss.on("connection", (ws, req) => {

    if (req.url === "/internal") {
        ws.isInternal = true;

        ws.on("message", (message) => {
            const data = JSON.parse(message.toString());
            if (data.action === "message_broadcast") {
                const senderId = String(data.user.id);
                const eventId = data.event_id;

                const recipients = eventSubscriptions[eventId] || [];
                if (!recipients.includes(senderId)) recipients.push(senderId);

                console.log(`\n\n [INTERNAL BROADCAST] from user ${senderId} to event ${eventId}`);
                console.log(`Recipients:`, recipients);

                wss.clients.forEach((client) => {
                    if (
                        !client.isInternal &&
                        client.subscriptions?.some(sub => sub.eventId === eventId && recipients.includes(sub.userId))
                    ) {
                        console.log(`Sending to client userId: ${client.subscriptions.map(s => s.userId).join(", ")}`);
                        client.send(JSON.stringify(data));
                    }
                });
                ws.terminate(); // Immediately close the internal socket after broadcast
            }
        });

        return;
    }

    ws.subscriptions = [];

    ws.on("message", (message) => {
        try {
            const jsonData = JSON.parse(message.toString());

            console.log(`\n\nTotal connected clients: ${wss.clients.size}`);

            if (jsonData.action === "subscribe") {
                const subscribingUserId = String(jsonData.userId);
                const eventId = String(jsonData.event_id);

                if (!subscribingUserId || !eventId) {
                    console.warn("Invalid subscribe request — missing userId or eventId");
                    return;
                }

                ws.subscriptions.push({ userId: subscribingUserId, eventId });

                if (!eventSubscriptions[eventId]) {
                    eventSubscriptions[eventId] = [];
                }

                if (!eventSubscriptions[eventId].includes(subscribingUserId)) {
                    eventSubscriptions[eventId].push(subscribingUserId);
                    console.log(`Added user ${subscribingUserId} to eventSubscriptions[${eventId}]`);
                }

                console.log("[AFTER SUBSCRIBE] eventSubscriptions state:", eventSubscriptions[eventId]);
            }

            if (jsonData.action === "unsubscribe") {

                const unsubscribedUserId = String(jsonData.userId);
                const eventId = String(jsonData.event_id);

                if (eventSubscriptions[eventId]) {
                    eventSubscriptions[eventId] = eventSubscriptions[eventId].filter(id => id !== unsubscribedUserId);
                }

                [...wss.clients].forEach((client) => {
                    if (
                        client.subscriptions &&
                        client.subscriptions.some(sub => sub.userId === unsubscribedUserId && sub.eventId === eventId)
                    ) {
                        client.send(JSON.stringify({
                            action: "unsubscribed",
                            userId: unsubscribedUserId,
                            event_id: eventId
                        }));

                        // Clean the subscription
                        client.subscriptions = client.subscriptions.filter(sub =>
                            sub.userId !== unsubscribedUserId || sub.eventId !== eventId
                        );

                        // Optionally close if no more subscriptions
                        if (client.subscriptions.length === 0) {
                            client.terminate();
                        }
                    }
                });

            }

            if (jsonData.action === "message_broadcast") {
                const senderId = String(jsonData.user.id);
                const eventId = String(jsonData.event_id);

                let recipients = eventSubscriptions[eventId] || [];
                if (!recipients.includes(senderId)) recipients.push(senderId);

                console.log(`\n\n [MESSAGE_BROADCAST] from user ${senderId} to event ${eventId}`);
                console.log(`Recipients:`, recipients);

                console.log("[CLIENT STATES BEFORE BROADCAST]");
                wss.clients.forEach((client) => {
                    console.log(`   Client: subs =`, client.subscriptions);
                });

                wss.clients.forEach((client) => {
                    if (
                        client.subscriptions?.some(sub => sub.eventId === eventId && recipients.includes(sub.userId))
                    ) {
                        if (client.readyState === WebSocket.OPEN) {
                            console.log(`Broadcasting message to client with eventId: ${eventId}`);
                            client.send(JSON.stringify({
                                action: "message_broadcast",
                                message: jsonData.message,
                                event_id: eventId,
                                userId: senderId,
                            }));
                        }
                    }
                });

                wss.clients.forEach((client) => {
                    console.log(`   Client: subs =`, client.subscriptions);
                });
            }

        } catch (error) {
            console.error("JSON parsing error:", error.message);
        }
    });

    ws.on("error", (err) => console.error("WebSocket server error:", err.message));

    ws.on("close", () => {
        if (ws.subscriptions) {
            ws.subscriptions.forEach(({ userId, eventId }) => {
                if (eventSubscriptions[eventId]) {
                    eventSubscriptions[eventId] = eventSubscriptions[eventId].filter(id => id !== userId);
                    console.log(`Client disconnected: userId = ${userId} disconnected from eventId = ${eventId}`);
                    console.log(`Updated subscriptions for ${eventId}:`, eventSubscriptions[eventId]);
                }
            });
        }
    });
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