export default function online(ws, jsonData, globalConnectedUsers) {
    ws.allEventIds = (jsonData.eventIds || []).map(String);
    ws.userId = jsonData.userId;
    globalConnectedUsers[jsonData.userId] = ws;
}
