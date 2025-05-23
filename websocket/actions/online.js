export default function online(ws, jsonData, globalConnectedUsers) {
    ws.allEventIds = jsonData.eventIds;
    ws.userId = jsonData.userId;
    globalConnectedUsers[jsonData.userId] = ws;
}
