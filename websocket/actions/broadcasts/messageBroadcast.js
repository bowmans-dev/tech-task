// for testing purposes only

import { broadcastToEvent } from "../../utils/broadcastToEvent.js";

export default function messageBroadcast(ws, data, eventScopedConnections, wss) {
    broadcastToEvent(ws, data, eventScopedConnections, wss, 'message_broadcast', {
        message_id: data.message_id,
        message: data.message,
        event_id: data.event_id,
        userId: String(ws.userId)
    });
}