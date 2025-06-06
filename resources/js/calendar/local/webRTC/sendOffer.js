import { state, currentUser } from '../../state.js';

export default async function sendOffer(data) {
  const { eventId, toUserId, broadcastingUserDetails } = data.payload;

  const stream = state.media.localStream;
  if (!stream) {
    console.warn("No local stream found");
    return;
  }

  const peerConnection = new RTCPeerConnection({
    iceServers: [
      { urls: "stun:stun.l.google.com:19302" },
      { urls: "stun:stun1.l.google.com:19302" }
    ]
  });

  if (!state.media.peerConnectionsByUserId) {
    state.media.peerConnectionsByUserId = {};
  }

  state.media.peerConnectionsByUserId[toUserId] = peerConnection;

  stream.getTracks().forEach(track => {
    const transceiver = peerConnection.addTransceiver(track.kind, { direction: "sendrecv" });
    transceiver.sender.replaceTrack(track);
  });

  peerConnection.onicecandidate = (event) => {
    if (event.candidate) {
      state.currentWs?.send(JSON.stringify({
        action: "ice_candidate",
        payload: {
          candidate: event.candidate,
          toUserId,
          fromUserId: currentUser.userId,
          eventId
        }
      }));
    }
  };

  const offer = await peerConnection.createOffer();
  await peerConnection.setLocalDescription(offer);

  state.currentWs?.send(JSON.stringify({
    action: "send_offer",
    payload: {
      offer: peerConnection.localDescription,
      eventId,
      toUserId,
      userId: currentUser.userId,
      broadcastingUserDetails
    }
  }));
}