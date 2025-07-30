export function drawVisualizer(analyser, canvasCtx, width, height, isActiveRef) {
  const bufferLength = analyser.frequencyBinCount;
  const dataArray = new Uint8Array(bufferLength);

  function draw() {
    if (typeof isActiveRef === 'function' && !isActiveRef()) return;
    requestAnimationFrame(draw);

    analyser.getByteFrequencyData(dataArray);
    
    canvasCtx.clearRect(0, 0, width, height);

    const barWidth = (width / bufferLength) * 3;
    let x = 0;

    for (let i = 0; i < bufferLength; i++) {
      const barHeight = Math.pow(dataArray[i] / 16, 2) * 4;
      canvasCtx.fillStyle = 'rgb(43, 127, 255)';
      canvasCtx.fillRect(x, (height / 2) - barHeight, barWidth, barHeight * 2);
      x += barWidth + 20;
    }
  }

  draw();
}