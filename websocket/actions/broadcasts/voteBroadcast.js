import { broadcastToEvent } from "../../utils/broadcastToEvent.js";

export default function voteBroadcast(ws, data, eventScopedConnections, wss) {
    broadcastToEvent(ws, data, eventScopedConnections, wss, 'vote_broadcast', {
        message_id: data.message_id,
        user: data.user,
        html: data.html
    });
}