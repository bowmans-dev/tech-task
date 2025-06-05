import { state } from '../../state.js';

export default async function iceCandidate(data) {
  const { candidate, fromUserId } = data.payload;

  const peerConnection = state.media.peerConnectionsByUserId[fromUserId];

  if (peerConnection) {
    if (peerConnection.remoteDescription?.type) {
      await peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
    } else {
      if (!state.media.pendingIceCandidates[fromUserId]) {
        state.media.pendingIceCandidates[fromUserId] = [];
      }
      state.media.pendingIceCandidates[fromUserId].push(candidate);
    }
  } else {
    if (!state.media.pendingIceCandidates[fromUserId]) {
      state.media.pendingIceCandidates[fromUserId] = [];
    }
    state.media.pendingIceCandidates[fromUserId].push(candidate);
  }
}