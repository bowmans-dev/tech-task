import http from "http";
import { WebSocketServer, WebSocket } from "ws";

const server = http.createServer();
const wss = new WebSocketServer({ server });

const eventSubscriptions = {}; // Tracks subscribed users per event ID
// global map that tracks which users are subscribed to each event, 
// making it easy to broadcast messages to all relevant users.

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

    if (req.url === "/monitor") {
        ws.isMonitor = true;

        const sendUpdate = () => {
            // Aggregate details from all non-monitor clients
            const allUserDetails = {};
            const allEventDetails = {};

            for (const client of wss.clients) {
                if (client.isMonitor) continue;

                if (client.userDetails) {
                    Object.assign(allUserDetails, client.userDetails);
                }

                if (client.eventDetails) {
                    Object.assign(allEventDetails, client.eventDetails);
                }
            }

            ws.send(JSON.stringify({
                type: "monitor_update",
                eventSubscriptions,
                eventDetails: allEventDetails,
                userDetails: allUserDetails,
                clients: [...wss.clients]
                .filter(c => !c.isMonitor)
                .map((c) => ({
                    subscriptions: c.subscriptions,
                    isInternal: c.isInternal,
                }))
            }));
        };


        // Immediately send snapshot
        sendUpdate();

        // Set up interval for continuous updates
        const interval = setInterval(sendUpdate, 1000); // every second

        ws.on("close", () => clearInterval(interval));
        return;
    }


    if (req.url === "/internal") {
        ws.isInternal = true;

        ws.on("message", (message) => {
            const data = JSON.parse(message.toString());
            if (data.action === "message_broadcast") {
                const senderId = String(data.user.id);
                const eventId = data.event_id;

                const recipients = eventSubscriptions[eventId] || [];
                if (!recipients.includes(senderId)) recipients.push(senderId);

                console.log(`\n\n 📢 [INTERNAL BROADCAST] from user ${senderId} to event ${eventId}`);
                console.log(`👥 Recipients:`, recipients);

                wss.clients.forEach((client) => {
                    if (
                        !client.isInternal &&
                        client.subscriptions?.some(sub => sub.eventId === eventId && recipients.includes(sub.userId))
                    ) {
                        console.log(`📨 Sending to client userId: ${client.subscriptions.map(s => s.userId).join(", ")}`);
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

            const totalConnectedClients = [...wss.clients].filter(c => !c.isMonitor && !c.isInternal);
            console.log(`\n\n🔧 Total connected clients: ${totalConnectedClients.length}`);

            if (jsonData.action === "subscribe") {
                const subscribingUserId = String(jsonData.userId);
                const eventId = String(jsonData.event_id);

                if (!subscribingUserId || !eventId) {
                    console.warn("Invalid subscribe request — missing userId or eventId");
                    return;
                }

                console.log(`📡 [SUBSCRIBE] Request from userId: ${subscribingUserId} for eventId: ${eventId}`);

                ws.subscriptions.push({ userId: subscribingUserId, eventId });

                if (!eventSubscriptions[eventId]) {
                    eventSubscriptions[eventId] = [];
                }

                if (!eventSubscriptions[eventId].includes(subscribingUserId)) {
                    eventSubscriptions[eventId].push(subscribingUserId);
                    console.log(`📥 Added user ${subscribingUserId} to eventSubscriptions[${eventId}]`);
                }

                console.log(`📋 [AFTER SUBSCRIBE] [${eventId}] eventSubscriptions state:`, eventSubscriptions[eventId]);

                // For Admins /active (Live Event Websocket Connection Monitoring)
                const allUsers = jsonData.existingTeamMembers || [];

                const user = allUsers.find(u => String(u.userId) === String(subscribingUserId));

                if (!ws.userDetails) ws.userDetails = {};
                if (!ws.eventDetails) ws.eventDetails = {};

                if (user) {
                    ws.userDetails[subscribingUserId] = {
                        id: user.userId,
                        first_name: user.firstName,
                        last_name: user.lastName,
                        profile_picture: user.profilePicture || "/storage/default_profile_image.png"
                    };
                }
                if (jsonData.currentEvent) {
                    const { title, date } = jsonData.currentEvent;

                    ws.eventDetails[eventId] = {
                        title: title || `Event ${eventId}`,
                        date: date || null,
                    };
                }

            }


            if (jsonData.action === "unsubscribe") {

                const unsubscribedUserId = String(jsonData.userId);
                const eventId = String(jsonData.event_id);

                console.log(`📡 [UNSUBSCRIBE] userId: ${unsubscribedUserId} from eventId: ${eventId}`);

                if (eventSubscriptions[eventId]) {
                    eventSubscriptions[eventId] = eventSubscriptions[eventId].filter(id => id !== unsubscribedUserId);
                }

                console.log(`🫷 Removed user ${unsubscribedUserId} from eventSubscriptions[${eventId}]`);

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
                console.log(`📋 [AFTER UNSUBSCRIBE] [${eventId}] eventSubscriptions state:`, eventSubscriptions[eventId]);
            } 

            if (jsonData.action === "message_broadcast") {
                const senderId = String(jsonData.user.id);
                const eventId = String(jsonData.event_id);

                let recipients = eventSubscriptions[eventId] || [];
                if (!recipients.includes(senderId)) recipients.push(senderId);

                console.log(`\n\n [MESSAGE_BROADCAST] from user ${senderId} to event ${eventId}`);
                console.log(`👥 Recipients:`, recipients);

                wss.clients.forEach((client) => {
                    console.log(`📫 Client: subscriptions =`, client.subscriptions);
                });

                wss.clients.forEach((client) => {
                    if (
                        client.subscriptions?.some(sub => sub.eventId === eventId && recipients.includes(sub.userId))
                    ) {
                        if (client.readyState === WebSocket.OPEN) {
                            console.log(`📨 Broadcasting message to client with eventId: ${eventId}`);
                            client.send(JSON.stringify({
                                action: "message_broadcast",
                                message: jsonData.message,
                                event_id: eventId,
                                userId: senderId,
                            }));
                        }
                    }
                });

            }

        } catch (error) {
            console.error("JSON parsing error:", error.message);
        } finally {
            console.log(`🔧 All Event Subscriptions:`, eventSubscriptions);
        }
    });

    ws.on("error", (err) => console.error("WebSocket server error:", err.message));

    ws.on("close", () => {
        if (ws.subscriptions) {
            ws.subscriptions.forEach(({ userId, eventId }) => {
                if (eventSubscriptions[eventId]) {
                    // Remove the userId from the eventSubscriptions
                    eventSubscriptions[eventId] = eventSubscriptions[eventId].filter(id => id !== userId);
                    console.log(`\n\n 🔌 Client disconnected: Event ${eventId}: User ${userId} disconnected`);
                    console.log(`📉 Updated subscriptions for ${eventId}:`, eventSubscriptions[eventId]);

                    // If no users are left for this eventId, clean up
                    if (eventSubscriptions[eventId].length === 0) {
                        delete eventSubscriptions[eventId];
                        console.log(`🧹 Cleaned up empty eventSubscriptions[${eventId}]`);
                    }
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