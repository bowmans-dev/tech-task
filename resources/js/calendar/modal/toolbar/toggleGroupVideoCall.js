import { startMediaStream } from "../../state";
import { stopMediaSession } from "./utils/stopMediaSession";
import { initializeCanvas } from "./utils/initializeCanvas";
import { setupAudioVisualizer } from "./utils/setupAudioVisualizer";
import { drawVisualizer } from "./utils/drawVisualizer";
import { renderVideoPreview } from "./utils/renderVideoPreview";
import { removeVideoPreview } from "./utils/removeVideoPreview";

const WIDTH = 1440;
const HEIGHT = WIDTH * 2 / 3;

const VIDEO_PREVIEW_CONTAINER = 'modal-group-video-input-container';
const canvasCtx = initializeCanvas(".video-mic-canvas", WIDTH, HEIGHT);

let isMicInitialized = false;
let stream = null;
let audioCtx = null;
let sourceNode = null;

export async function toggleGroupVideoCall() {
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
    stream = await navigator.mediaDevices.getUserMedia({ audio: true, video: true });
    const { audioCtx: ctx, analyser, source } = setupAudioVisualizer(stream);
    audioCtx = ctx;
    sourceNode = source;
    isMicInitialized = true;
    
    drawVisualizer(analyser, canvasCtx, WIDTH, HEIGHT, () => isMicInitialized);
    startMediaStream({ stream, type: "video" });
    renderVideoPreview(VIDEO_PREVIEW_CONTAINER, stream, '100%', 'auto', true);

  } catch (err) {
    console.error("Microphone access denied:", err);
  }
}