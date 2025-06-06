export function setUpMessagesBackground() {

  let messagesDiv = document.getElementById("messages");
  let dropZone = document.getElementById("drop-zone");

  if (!messagesDiv || !dropZone) {
    console.error("❌ Missing #messages or #drop-zone element.");
    return;
  }

  function updateMessagesBackground() {
    const count = messagesDiv.childElementCount;
    if (count > 4) {
      messagesDiv.style.backgroundImage = "url('/storage/wallpaper.webp')";
    } else {
      messagesDiv.style.backgroundImage = "none";
    }
  }

  // MutationObserver to detect DOM changes
  const mutationObserver = new MutationObserver(() => {
    updateMessagesBackground();
  });

  mutationObserver.observe(messagesDiv, { childList: true, subtree: false });

  // IntersectionObserver to detect visibility
  const observerOptions = {
    root: dropZone,
    threshold: 0.1
  };

  const observerCallback = (entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        updateMessagesBackground();
        messagesDiv.classList.remove("with-overlay");
        messagesDiv.classList.add("no-overlay");
      } else {
        messagesDiv.classList.remove("no-overlay");
        messagesDiv.classList.add("with-overlay");
      }
    });
  };

  let observer = new IntersectionObserver(observerCallback, observerOptions);
  observer.observe(messagesDiv);
}