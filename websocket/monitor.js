export default function monitor(ws, wss, eventScopedConnections) {
    ws.isMonitor = true;

    // serialize eventScopedConnections Map<eventId, Set<userId>> to Object<eventId, Array<userId>>
    function serializeEventScopedConnections(map) {
        const obj = {};
        for (const [eventId, userSet] of map.entries()) {
            obj[eventId] = Array.from(userSet);
        }
        return obj;
    }

    const sendUpdate = () => {
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
            eventScopedConnections: serializeEventScopedConnections(eventScopedConnections),
            eventDetails: allEventDetails,
            userDetails: allUserDetails,
            clients: [...wss.clients]
                .filter(c => !c.isMonitor)
                .map((c) => ({
                    subscriptions: c.connectedEvent,
                    isInternal: c.isInternal,
                }))
        }));
    };

    sendUpdate();
    const interval = setInterval(sendUpdate, 1000);
    ws.on("close", () => clearInterval(interval));
}
