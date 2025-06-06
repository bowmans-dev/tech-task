export function setUpEmojiPicker() {
  function openReactionPicker(event, el) {
    event.preventDefault(); // Prevent context menu
    const messageId = el.dataset.messageId;
    showEmojiPicker(el, messageId);
  }

  function showEmojiPicker(targetEl, messageId) {
    document.querySelectorAll(".emoji-picker").forEach(p => p.remove());
    const messageElement = document.getElementById(`message-${messageId}`);
    const emojis = ['❤️', '😂', '🤷‍♂️', '👍', '🤙', '🔥'];

    const picker = document.createElement("div");
    picker.className = "emoji-picker absolute bg-white shadow p-2 rounded flex space-x-2";
    picker.style.top = `-55px`;
    picker.style.zIndex = "9999";

    picker.addEventListener("click", e => e.stopPropagation());

    emojis.forEach(emoji => {
      const btn = document.createElement("button");
      btn.textContent = emoji;
      btn.className = "text-2xl hover:scale-125 transition cursor-pointer";
      btn.onclick = e => {
        e.stopPropagation();
        sendReaction(messageId, emoji);
        removeEmojiPicker();
      };
      picker.appendChild(btn);
    });

    messageElement.appendChild(picker);
  }

  function removeEmojiPicker() {
    setTimeout(() => {
      document.querySelectorAll(".emoji-picker").forEach(p => p.remove());
    }, 300);
  }

  function sendReaction(messageId, emoji) {
    fetch("/message/react", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": document.querySelector("meta[name='csrf-token']").content,
      },
      body: JSON.stringify({ message_id: messageId, emoji }),
    }).then(() => {
      console.log("Reaction sent");
    });
  }

  // Add event listeners once per setup
  document.body.addEventListener("contextmenu", e => {
    const messageEl = e.target.closest("[data-message-id]");
    if (messageEl) openReactionPicker(e, messageEl);
  });

  document.body.addEventListener("click", e => {
    if (e.target.closest(".bubble")) {
      const messageEl = e.target.closest("[data-message-id]");
      if (messageEl) openReactionPicker(e, messageEl);
    } else if (!e.target.closest(".emoji-picker")) {
      removeEmojiPicker();
    }
  });
}