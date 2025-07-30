export function renderVideoPreview(containerId, stream, width = '100%', height = 'auto', flip = false) {
  const video = document.createElement('video');
  video.srcObject = stream;
  video.autoplay = true;
  video.muted = true;
  video.playsInline = true;
  video.style.width = width;
  video.style.height = height;
  if (flip) video.style.transform = 'scaleX(-1)';

  const container = document.getElementById(containerId);
  container.appendChild(video);
}