import { startMediaStream } from "../../state";

const WIDTH = 1440;
const HEIGHT = WIDTH * 2 / 3;

const canvas = document.querySelector("canvas");
canvas.width = WIDTH;
canvas.height = HEIGHT;
const canvasCtx = canvas.getContext("2d");

let isMicInitialized = false;
let audioCtx = null;
let mixedStream = null;

export async function toggleGroupScreenShare() {

  if (isMicInitialized) {
    if (stream) {
      stream.getTracks().forEach(track => track.stop()); // stop media
      stream = null;
    }
    if (audioCtx) {
      audioCtx.close();
      audioCtx = null;
    }
    isMicInitialized = false;
    canvasCtx.clearRect(0, 0, WIDTH, HEIGHT); // clear canvas

    // Remove local preview video element
    const container = document.getElementById('modal-group-video-input-container');
    const existingVideo = container.querySelector('video');
    if (existingVideo) {
      container.removeChild(existingVideo);
    }

    return;
  }

  try {
    // Capture screen with tab audio (if available)
    const screenStream = await navigator.mediaDevices.getDisplayMedia({ video: true, audio: true });

    // Capture mic
    const micStream = await navigator.mediaDevices.getUserMedia({ audio: true });

    // Create a combined stream
    mixedStream = new MediaStream([
      ...screenStream.getVideoTracks(),
      ...screenStream.getAudioTracks(),
      ...micStream.getAudioTracks()
    ]);

    // Initialize audio context only if we have audio tracks
    const hasAudio = mixedStream.getAudioTracks().length > 0;
    if (hasAudio) {
      audioCtx = new AudioContext();
      const analyser = audioCtx.createAnalyser();
      const source = audioCtx.createMediaStreamSource(mixedStream);

      source.connect(analyser);

      analyser.fftSize = 2048;
      const bufferLength = analyser.frequencyBinCount;
      const dataArray = new Uint8Array(bufferLength);

      function draw() {
        if (!isMicInitialized) return;
        requestAnimationFrame(draw);

        analyser.getByteFrequencyData(dataArray);

        canvasCtx.clearRect(0, 0, WIDTH, HEIGHT);
        const barWidth = (WIDTH / bufferLength) * 3;
        let x = 0;

        for (let i = 0; i < bufferLength; i++) {
          const barHeight = Math.pow(dataArray[i] / 16, 2) * 4;
          canvasCtx.fillStyle = `rgb(43, 127, 255)`;
          canvasCtx.fillRect(x, (HEIGHT / 2) - barHeight, barWidth, barHeight * 2);
          x += barWidth + 20;
        }
      }

      draw();
    } else {
      console.warn("No audio tracks found in mixed stream.");
    }

    isMicInitialized = true;

    startMediaStream({ stream: mixedStream, type: "screen" });

    // Preview the local video stream
    const localVideo = document.createElement('video');
    localVideo.srcObject = mixedStream;
    localVideo.autoplay = true;
    localVideo.muted = true;
    localVideo.playsInline = true;
    localVideo.style.width = '300px';
    localVideo.style.height = 'auto';

    const container = document.getElementById('modal-group-screen-input-container');
    container.appendChild(localVideo);

  } catch (err) {
    console.error("Error capturing screen + mic:", err);
  }
}
