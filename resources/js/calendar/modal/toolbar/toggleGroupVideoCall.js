import { startMediaStream } from "../../state";

const WIDTH = 1440;
const HEIGHT = WIDTH * 2 / 3;

const canvas = document.querySelector("canvas");
canvas.width = WIDTH;
canvas.height = HEIGHT;
const canvasCtx = canvas.getContext("2d");

let isMicInitialized = false;
let stream = null;
let audioCtx = null;

export async function toggleGroupVideoCall() {
  // If already initialized: stop the mic and reset
  if (isMicInitialized) {
    if (stream) {
      stream.getTracks().forEach(track => track.stop()); // stop mic
      stream = null;
    }
    if (audioCtx) {
      audioCtx.close();
      audioCtx = null;
    }
    isMicInitialized = false;
    canvasCtx.clearRect(0, 0, WIDTH, HEIGHT); // clear canvas
    return;
  }

  try {
    stream = await navigator.mediaDevices.getUserMedia({ audio: true, video: true });
    audioCtx = new AudioContext();
    const analyser = audioCtx.createAnalyser();
    const source = audioCtx.createMediaStreamSource(stream);

    source.connect(analyser);
    // source.connect(audioCtx.destination);

    analyser.fftSize = 2048;
    const bufferLength = analyser.frequencyBinCount;
    const dataArray = new Uint8Array(bufferLength);

    function draw() {
      if (!isMicInitialized) return;
      requestAnimationFrame(draw);

      analyser.getByteFrequencyData(dataArray);

      const gradient = canvasCtx.createLinearGradient(0, 0, WIDTH, HEIGHT);
      gradient.addColorStop(0, "#fff");
      gradient.addColorStop(1, "#fff");
      canvasCtx.fillStyle = gradient;
      canvasCtx.fillRect(0, 0, WIDTH, HEIGHT);

      const barWidth = (WIDTH / bufferLength) * 3;
      let barHeight;
      let x = 0;

      for (let i = 0; i < bufferLength; i++) {
        barHeight = Math.pow(dataArray[i] / 16, 2) * 4;
        canvasCtx.fillStyle = `rgb(43, 127, 255)`;
        canvasCtx.fillRect(x, (HEIGHT / 2) - barHeight, barWidth, barHeight * 2);
        x += barWidth + 20;
      }
    }

    isMicInitialized = true;
    draw();

    startMediaStream({ stream, type: "video" });

    // Preview the local video stream
    const localVideo = document.createElement('video');
    localVideo.srcObject = stream;
    localVideo.autoplay = true;
    localVideo.muted = true;
    localVideo.playsInline = true;
    localVideo.style.width = '300px';
    localVideo.style.height = 'auto';

    const container = document.getElementById('modal-group-video-input-container');

    container.appendChild(localVideo);

  } catch (err) {
    console.error("Microphone access denied:", err);
  }
}