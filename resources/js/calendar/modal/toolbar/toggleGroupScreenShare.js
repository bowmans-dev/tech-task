import { startMediaStream } from "../../state";
import { stopMediaSession } from "./utils/stopMediaSession";
import { initializeCanvas } from "./utils/initializeCanvas";
import { setupAudioVisualizer } from "./utils/setupAudioVisualizer";
import { drawVisualizer } from "./utils/drawVisualizer";
import { renderVideoPreview } from "./utils/renderVideoPreview";
import { removeVideoPreview } from "./utils/removeVideoPreview";

const WIDTH = 1440;
const HEIGHT = WIDTH * 2 / 3;

const VIDEO_PREVIEW_CONTAINER = 'modal-group-screen-input-container';
const canvasCtx = initializeCanvas(".screen-mic-canvas", WIDTH, HEIGHT);

let isMicInitialized = false;
let audioCtx = null;
let stream = null;
let sourceNode = null;

export async function toggleGroupScreenShare() {

  if (isMicInitialized) {
    stopMediaSession(stream, audioCtx, canvasCtx, WIDTH, HEIGHT, sourceNode);
    stream = null;
    audioCtx = null;
    sourceNode = null;
    isMicInitialized = false;

    removeVideoPreview(VIDEO_PREVIEW_CONTAINER);
    return;
  }

  try {
    // Capture screen with tab audio (if available)
    const screenStream = await navigator.mediaDevices.getDisplayMedia({ video: true, audio: true });

    // Capture mic
    const micStream = await navigator.mediaDevices.getUserMedia({ audio: true });

    // Create a combined stream
    stream = new MediaStream([
      ...screenStream.getVideoTracks(),
      ...screenStream.getAudioTracks(),
      ...micStream.getAudioTracks()
    ]);

    // Initialize audio context only if we have audio tracks
    const hasAudio = stream.getAudioTracks().length > 0;
    if (hasAudio) {
      const { audioCtx: ctx, analyser } = setupAudioVisualizer(micStream);
      audioCtx = ctx;

      isMicInitialized = true;
      drawVisualizer(analyser, canvasCtx, WIDTH, HEIGHT, () => isMicInitialized);
    } else {
      console.warn("No audio tracks found in mixed stream.");
    }


    startMediaStream({ stream: stream, type: "screen" });

    // Preview the local video stream
    renderVideoPreview(VIDEO_PREVIEW_CONTAINER, stream, '300px', 'auto');

  } catch (err) {
    console.error("Error capturing screen + mic:", err);
  }
}
