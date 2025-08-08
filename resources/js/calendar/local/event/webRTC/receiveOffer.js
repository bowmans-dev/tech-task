import { state } from '../../state.js';
import { createVideoMessageWrapper } from './utils/createVideoMessageWrapper.js';

export default async function receiveOffer(data) {
  const { type, offer, fromUserId, toUserId, broadcastingUserDetails, eventId } = data.payload;

  const peerConnection = new RTCPeerConnection({
    iceServers: [
      { urls: "stun:stun.l.google.com:19302" },
      { urls: "stun:stun1.l.google.com:19302" }
    ]
  });

  state.media.peerConnectionsByUserId[fromUserId] = peerConnection;

  peerConnection.addTransceiver('audio', { direction: 'recvonly' });
  peerConnection.addTransceiver('video', { direction: 'recvonly' });

  peerConnection.ontrack = (event) => {
    const container = document.getElementById('messages');

    if (event.track.kind === "audio") {
      const audio = new Audio();
      audio.srcObject = new MediaStream([event.track]);
      audio.muted = false;
      audio.autoplay = true;
      audio.play().catch(err => console.error("Audio playback error:", err));
      return;
    }

    if (event.track.kind === "video") {
      const videoMessage = createVideoMessageWrapper(broadcastingUserDetails, type, event.track);
      container.appendChild(videoMessage);

      event.track.onmute = () => {
        container.removeChild(videoMessage);
        peerConnection.close();
      };
    }
  };

  peerConnection.onicecandidate = (event) => {
    if (event.candidate) {
      state.currentWs?.send(JSON.stringify({
        action: "ice_candidate",
        payload: {
          candidate: event.candidate,
          toUserId: fromUserId,
          fromUserId: toUserId,
          eventId
        }
      }));
    }
  };

  await peerConnection.setRemoteDescription(new RTCSessionDescription(offer));

  const pendingCandidates = state.media.pendingIceCandidates[fromUserId];
  if (pendingCandidates?.length > 0) {
    for (const c of pendingCandidates) {
      try {
        await peerConnection.addIceCandidate(new RTCIceCandidate(c));
      } catch (e) {
        console.error("Failed to apply buffered ICE candidate:", e);
      }
    }
    delete state.media.pendingIceCandidates[fromUserId];
  }

  const answer = await peerConnection.createAnswer();
  await peerConnection.setLocalDescription(answer);

  state.currentWs?.send(JSON.stringify({
    action: "send_answer",
    payload: {
      answer,
      toUserId: fromUserId,
      fromUserId: toUserId,
      eventId
    }
  }));
}