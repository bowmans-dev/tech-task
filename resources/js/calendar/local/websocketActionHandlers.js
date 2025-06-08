import disconnectFromEvent from './websocket/disconnectFromEvent.js';
import updateEvent from './websocket/updateEvent.js';
import messageBroadcast from './websocket/messageBroadcast.js';
import reactionBroadcast from './websocket/reactionBroadcast.js';
import voteBroadcast from './websocket/voteBroadcast.js';
import taskCompletedBroadcast from './websocket/taskCompletedBroadcast.js';

export const websocketActionHandlers = {
  disconnect_from_event: disconnectFromEvent,
  update_event: updateEvent,
  message_broadcast: messageBroadcast,
  reaction_broadcast: reactionBroadcast,
  vote_broadcast: voteBroadcast,
  task_completed_broadcast: taskCompletedBroadcast
};