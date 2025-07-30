import online from "./actions/online.js";
import subscribe from "./actions/subscribe.js";
import unsubscribe from "./actions/unsubscribe.js";
import updateEvent from "./actions/updateEvent.js";
import teamMemberAdded from "./actions/teamMemberAdded.js";
import messageBroadcast from "./actions/messageBroadcast.js";
import reactionBroadcast from "./actions/reactionBroadcast.js";
import voteBroadcast from "./actions/voteBroadcast.js";
import taskCompletedBroadcast from "./actions/taskCompletedBroadcast.js";
import notifyTeamMembers from "./actions/notifyTeamMembers.js";
import startMediaBroadcast from "./actions/startMediaBroadcast.js";
import sendOffer from "./actions/sendOffer.js";
import sendAnswer from "./actions/sendAnswer.js";
import forwardIceCandidate from "./actions/forwardIceCandidate.js";

export default class WebSocketDispatcher {
    constructor({ globalConnectedUsers, eventScopedConnections, wss, WebSocket }) {
        this.globalConnectedUsers = globalConnectedUsers;
        this.eventScopedConnections = eventScopedConnections;
        this.wss = wss;
        this.WebSocket = WebSocket;
    }

    handle(ws, data) {
        const handler = this[data.action];
        if (typeof handler === "function") {
            handler.call(this, ws, data);
        } else {
            console.warn(`No handler found for action: ${data.action}`);
        }
    }

    online(ws, data) {
        online(ws, data, this.globalConnectedUsers);
    }

    team_member_added(ws, data) {
        teamMemberAdded(ws, data, this.globalConnectedUsers, this.wss, notifyTeamMembers, this.WebSocket);
    }

    connect_to_event(ws, data) {
        subscribe(ws, data, this.eventScopedConnections);
    }

    disconnect_from_event(ws, data) {
        unsubscribe(ws, data, this.eventScopedConnections, this.wss);
    }

    update_event(ws, data) {
        updateEvent(data, this.eventScopedConnections, this.wss);
    }

    message_broadcast(ws, data) {
        messageBroadcast(data, this.eventScopedConnections, this.wss);
    }

    reaction_broadcast(ws, data) {
        reactionBroadcast(data, this.eventScopedConnections, this.wss);
    }

    vote_broadcast(ws, data) {
        voteBroadcast(data, this.eventScopedConnections, this.wss);
    }

    task_completed_broadcast(ws, data) {
        taskCompletedBroadcast(data, this.eventScopedConnections, this.wss);
    }

    start_media_broadcast(ws, data) {
        startMediaBroadcast(ws, data, this.eventScopedConnections, this.WebSocket, this.wss);
    }

    send_offer(ws, data) {
        sendOffer(ws, data, this.eventScopedConnections, this.WebSocket, this.wss);
    }

    send_answer(ws, data) {
        sendAnswer(ws, data, this.eventScopedConnections, this.WebSocket, this.wss);
    }

    ice_candidate(ws, data) {
        forwardIceCandidate(ws, data, this.eventScopedConnections, this.WebSocket, this.wss);
    }
}