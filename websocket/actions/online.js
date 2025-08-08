export default function online(ws, jsonData, globalConnectedUsers) {
    ws.connectionType = 'global';
    ws.allEventIds = (jsonData.eventIds || []).map(String);
    globalConnectedUsers.set(jsonData.userId, ws);
}
