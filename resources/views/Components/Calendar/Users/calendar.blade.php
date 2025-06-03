<main>
    {{-- CALENDAR --}}
    <div id="calendar-wrapper" 
        style="calendar-wrapper max-width: 900px; margin-bottom: 30px;" 
        data-user-id="{{ auth()->user()->id }}"
        data-profile-picture="{{ auth()->user()->profile_picture }}"
        data-first-name="{{ auth()->user()->first_name }}"
        data-last-name="{{ auth()->user()->last_name }}">
        
        <div id='calendar' style="min-height: auto; padding: 17px; padding-bottom: 0px;"></div>
    </div>

    {{-- CALENDAR EVENT MODAL--}}
    <x-Calendar.Shared.event-modal :users="$users" />

    <div id="notification-container"></div>
</main>

<script>
  document.addEventListener("turbo:load", () => {
    
  function openReactionPicker(event, el) {
    event.preventDefault(); // Prevent context menu
    const messageId = el.dataset.messageId;
  
    // Show emoji picker next to the message
    showEmojiPicker(el, messageId);
  }
  
  function showEmojiPicker(targetEl, messageId) {
  
    document.querySelectorAll('.emoji-picker').forEach(p => p.remove());
    let messageElement = document.getElementById(`message-${messageId}`);
  
    const emojis = ['❤️', '😂', '🤷‍♂️', '👍', '🤙', '🔥'];
    const picker = document.createElement('div');
    picker.className = 'emoji-picker absolute bg-white shadow p-2 rounded flex space-x-2';
    picker.style.top = `-55px`;
    picker.style.zIndex = '9999';
  
    // Stop propagation so clicks inside picker don't trigger the document click listener
    picker.addEventListener('click', (e) => {
      e.stopPropagation();
    });
  
    emojis.forEach(emoji => {
      const btn = document.createElement('button');
      btn.textContent = emoji;
      btn.className = 'text-2xl hover:scale-125 transition cursor-pointer';
      btn.onclick = (e) => {
        e.stopPropagation(); // Prevent the document listener from firing
        sendReaction(messageId, emoji);
        removeEmojiPicker(); // Manually remove picker after clicking an emoji
      };
      picker.appendChild(btn);
    });
  
    messageElement.appendChild(picker);
  
  }
  
  function removeEmojiPicker() {
      setTimeout(() => {
          document.querySelectorAll('.emoji-picker').forEach(picker => picker.remove());
      }, 300);
  }
  
  function sendReaction(messageId, emoji) {
    fetch('/message/react', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      },
      body: JSON.stringify({ message_id: messageId, emoji }),
    }).then(() => {
      console.log('Reaction sent');
      // Optionally: update the UI immediately
    });
  }

  document.body.addEventListener('contextmenu', (e) => {
    const messageEl = e.target.closest('[data-message-id]');
    if (messageEl) {
      openReactionPicker(e, messageEl);
    }
  });

  document.body.addEventListener('click', (e) => {
    // Only open picker when clicking the bubble
    if (e.target.closest('.bubble')) {
      const messageEl = e.target.closest('[data-message-id]');
      if (messageEl) {
        openReactionPicker(e, messageEl);
      }
    } else {
      // If clicked outside picker, remove it
      if (!e.target.closest('.emoji-picker')) {
        removeEmojiPicker();
      }
    }
  });

  let messagesDiv = document.getElementById("messages");
  let dropZone = document.getElementById("drop-zone");

  if (!messagesDiv || !dropZone) {
    console.error("❌ Missing #messages or #drop-zone element.");
    return;
  }

  function updateMessagesBackground() {
    const count = messagesDiv.childElementCount;
    if (count > 4) {
      messagesDiv.style.backgroundImage = "url('{{ asset('storage/wallpaper.webp') }}')";
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
});
</script>