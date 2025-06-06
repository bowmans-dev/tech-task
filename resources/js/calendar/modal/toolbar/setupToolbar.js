let currentActive = null;

export function handleToolbarClick(event, button) {
  const clickedIcon = button
  const target = button.dataset.toolbarTarget;
  const isSame = currentActive === target;

  // Clear all highlights
  document.querySelectorAll('.toolbar-toggle').forEach(icon =>
    icon.classList.remove('button-highlight')
  );

  // Hide all panels
  document.querySelectorAll('[data-toolbar-panel]').forEach(panel =>
    panel.classList.add('hidden')
  );

  if (isSame) {
    // Deselect if same button clicked again
    currentActive = null;
    return;
  }

  // Highlight new icon
  clickedIcon.classList.add('button-highlight');

  // Show corresponding panels
  document.querySelectorAll(`[data-toolbar-panel="${target}"]`).forEach(panel =>
    panel.classList.remove('hidden')
  );

  currentActive = target;
}