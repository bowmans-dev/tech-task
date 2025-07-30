export function removeVideoPreview(containerId) {
  const container = document.getElementById(containerId);
  const video = container?.querySelector('video');
  if (video) {
    video.pause();
    video.srcObject = null;
    container.removeChild(video);
  }
}