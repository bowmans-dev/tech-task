export function stopMediaSession(streamRef, audioCtxRef, canvasCtx, width, height, sourceRef) {
  if (streamRef) {
    [...streamRef.getAudioTracks(), ...streamRef.getVideoTracks()].forEach(track => {
      try {
        track.stop();
      } catch (err) {
        console.warn("Track stop error:", err);
      }
    });
  }

  if (sourceRef?.disconnect) {
    sourceRef.disconnect();
  }

  if (audioCtxRef?.state !== "closed") {
    audioCtxRef.close().catch(console.error);
  }

  canvasCtx?.clearRect(0, 0, width, height);
}