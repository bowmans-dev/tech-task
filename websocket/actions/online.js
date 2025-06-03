export default function online(ws, jsonData, globalConnectedUsers) {
    ws.connectionType = 'global';
    ws.allEventIds = (jsonData.eventIds || []).map(String);
    ws.userId = jsonData.userId;
    globalConnectedUsers[jsonData.userId] = ws;
}
