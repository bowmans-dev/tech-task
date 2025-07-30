export function setupAudioVisualizer(stream) {
  const audioCtx = new AudioContext();
  const analyser = audioCtx.createAnalyser();
  const source = audioCtx.createMediaStreamSource(stream);
  source.connect(analyser);
  analyser.fftSize = 2048;
  return { audioCtx, analyser, source };
}