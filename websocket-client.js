process.env.NODE_TLS_REJECT_UNAUTHORIZED = "0"; 

import WebSocket from "ws";


let ws = new WebSocket("ws://localhost:8080");

ws.on("open", () => {
    console.log("WebSocket connected!");
});

ws.on("message", (data) => {
    console.log("Received message:", data.toString());
});

ws.on("close", () => {
    console.log("WebSocket disconnected. Attempting to reconnect...");
    setTimeout(() => {
        reconnectWebSocket();
    }, 5000);
});

ws.on("error", (err) => {
    console.error("WebSocket error:", err);
});

function reconnectWebSocket() {
    console.log("Reconnecting websocket...");
    
    ws = new WebSocket("ws://localhost:8080");

    ws.onopen = () => console.log("Websocket reconnected!");

    ws.onmessage = (message) => {
        console.log("Websocket update received:", message.data);
    };

    ws.onclose = () => {
        console.log("websocket disconnected. Attempting to reconnect...");
        setTimeout(reconnectWebSocket, 5000);
    };

    ws.onerror = (err) => console.error("Websocket error on reconnect:", err);
}