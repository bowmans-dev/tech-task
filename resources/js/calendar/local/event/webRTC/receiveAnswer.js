import { state } from '../../state.js';

export default async function receiveAnswer(data) {
  const { answer, fromUserId } = data.payload;
  const peerConnection = state.media.peerConnectionsByUserId?.[fromUserId];

  if (!peerConnection) {
    console.warn("No peer connection found for answer from", fromUserId);
    return;
  }

  try {
    await peerConnection.setRemoteDescription(new RTCSessionDescription(answer));

    const pendingCandidates = state.media.pendingIceCandidates?.[fromUserId];
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
  } catch (err) {
    console.error("Failed to set remote description from answer:", err);
  }
}