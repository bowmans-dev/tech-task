import { broadcastToEvent } from "../../utils/broadcastToEvent.js";

export default function taskCompletedBroadcast(ws, data, eventScopedConnections, wss) {
    broadcastToEvent(ws, data, eventScopedConnections, wss, 'task_completed_broadcast', {
        message_id: data.message_id,
        task_id: data.task_id,
        user: data.user,
        html: data.html
    });
}