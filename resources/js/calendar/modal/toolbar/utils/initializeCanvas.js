export function initializeCanvas(selector, width, height) {
  const canvas = document.querySelector(selector);
  canvas.width = width;
  canvas.height = height;
  return canvas.getContext("2d");
}