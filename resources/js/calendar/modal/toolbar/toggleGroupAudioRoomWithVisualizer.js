import { startMediaStream } from "../../local/state";
import { stopMediaSession } from "./utils/stopMediaSession";
import { initializeCanvas } from "./utils/initializeCanvas";
import { setupAudioVisualizer } from "./utils/setupAudioVisualizer";
import { drawVisualizer } from "./utils/drawVisualizer";

const WIDTH = 1440;
const HEIGHT = WIDTH * 2 / 3;
const canvasCtx = initializeCanvas("canvas", WIDTH, HEIGHT);

let isMicInitialized = false;
let stream = null;
let audioCtx = null;
let sourceNode = null;

export async function toggleGroupAudioRoomWithVisualizer() {
  // If already initialized: stop the mic and reset
  if (isMicInitialized) {
    stopMediaSession(stream, audioCtx, canvasCtx, WIDTH, HEIGHT, sourceNode);
    stream = null;
    audioCtx = null;
    sourceNode = null;
    isMicInitialized = false;
    return;
  }

  try {
    stream = await navigator.mediaDevices.getUserMedia({ audio: true });

    const { audioCtx: ctx, analyser, source } = setupAudioVisualizer(stream);
    audioCtx = ctx;
    sourceNode = source;

    isMicInitialized = true;
    drawVisualizer(analyser, canvasCtx, WIDTH, HEIGHT, () => isMicInitialized);

    startMediaStream({ stream, type: "audio" });

  } catch (err) {
    console.error("Microphone access denied:", err);
  }
}