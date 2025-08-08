import online from "./actions/online.js";
import subscribe from "./actions/subscribe.js";
import unsubscribe from "./actions/unsubscribe.js";
import updateEvent from "./actions/updateEvent.js";
import teamMemberAdded from "./actions/teamMemberAdded.js";
import messageBroadcast from "./actions/broadcasts/messageBroadcast.js";
import reactionBroadcast from "./actions/broadcasts/reactionBroadcast.js";
import voteBroadcast from "./actions/broadcasts/voteBroadcast.js";
import taskCompletedBroadcast from "./actions/broadcasts/taskCompletedBroadcast.js";
import startMediaBroadcast from "./actions/webRTC/startMediaBroadcast.js";
import sendOffer from "./actions/webRTC/sendOffer.js";
import sendAnswer from "./actions/webRTC/sendAnswer.js";
import forwardIceCandidate from "./actions/webRTC/forwardIceCandidate.js";

export default class WebSocketDispatcher {
    constructor({ globalConnectedUsers, eventScopedConnections, wss }) {
        this.globalConnectedUsers = globalConnectedUsers;
        this.eventScopedConnections = eventScopedConnections;
        this.wss = wss;
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

    connect_to_event(ws, data) {
        subscribe(ws, data, this.eventScopedConnections);
    }

    team_member_added(ws, data) {
        teamMemberAdded(ws, data, this.globalConnectedUsers, this.wss);
    }

    disconnect_from_event(ws, data) {
        unsubscribe(ws, data, this.eventScopedConnections, this.wss);
    }

    update_event(ws, data) {
        updateEvent(ws, data, this.eventScopedConnections, this.wss);
    }

    message_broadcast(ws, data) {
        messageBroadcast(ws, data, this.eventScopedConnections, this.wss);
    }

    reaction_broadcast(ws, data) {
        reactionBroadcast(ws, data, this.eventScopedConnections, this.wss);
    }

    vote_broadcast(ws, data) {
        voteBroadcast(ws, data, this.eventScopedConnections, this.wss);
    }

    task_completed_broadcast(ws, data) {
        taskCompletedBroadcast(ws, data, this.eventScopedConnections, this.wss);
    }

    start_media_broadcast(ws, data) {
        startMediaBroadcast(ws, data, this.eventScopedConnections, this.wss);
    }

    send_offer(ws, data) {
        sendOffer(ws, data, this.eventScopedConnections, this.wss);
    }

    send_answer(ws, data) {
        sendAnswer(ws, data, this.eventScopedConnections, this.wss);
    }

    ice_candidate(ws, data) {
        forwardIceCandidate(ws, data, this.eventScopedConnections, this.wss);
    }
}