import { broadcastToEvent } from "../../utils/broadcastToEvent.js";

export default function reactionBroadcast(ws, data, eventScopedConnections, wss) {
    broadcastToEvent(ws, data, eventScopedConnections, wss, 'reaction_broadcast', {
        message_id: data.message_id,
        emoji: data.emoji,
        user: data.user,
        html: data.html
    });
}
