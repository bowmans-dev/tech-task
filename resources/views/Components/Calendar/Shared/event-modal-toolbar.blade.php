{{-- CALENDAR EVENT MODAL TOOLBAR--}}
<div class="mt-6">
    <div class="flex flex-row flex-grow p-2 mb-6 border border-dashed border-gray-900/25 rounded-lg">

        <!-- SEARCH ICON -->
        <svg
        class="toolbar-toggle cursor-pointer"
        data-toolbar-target="search"
        onclick="handleToolbarClick(event)"
        xmlns="http://www.w3.org/2000/svg"
        height="24px"
        viewBox="0 -960 960 960"
        width="24px"
        fill="#6a7282"
        >
        <path d="M360-240ZM40-160v-112q0-34 17.5-62.5T104-378q62-31 126-46.5T360-440q32 0 64.5 3.5T489-425q-13 17-22.5 35.5T451-351q-23-5-45.5-7t-45.5-2q-56 0-111 13.5T140-306q-9 5-14.5 14t-5.5 20v32h323q4 22 11 42t18 38H40Zm320-320q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47Zm400-160q0 66-47 113t-113 47q-11 0-28-2.5t-28-5.5q27-32 41.5-71t14.5-81q0-42-14.5-81T544-792q14-5 28-6.5t28-1.5q66 0 113 47t47 113Zm-400 80q33 0 56.5-23.5T440-640q0-33-23.5-56.5T360-720q-33 0-56.5 23.5T280-640q0 33 23.5 56.5T360-560Zm0-80Zm320 440q34 0 56.5-20t23.5-60q1-34-22.5-57T680-360q-34 0-57 23t-23 57q0 34 23 57t57 23Zm0 80q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 23-5.5 43.5T818-198L920-96l-56 56-102-102q-18 11-38.5 16.5T680-120Z"/>
        </svg>

        <div class="h-[25px] w-0.5 bg-gray-200 ml-2 mr-2"></div>

        <!-- POLL ICON -->
        <svg 
        class="toolbar-toggle cursor-pointer"
        data-toolbar-target="poll"
        onclick="handleToolbarClick(event)"
        xmlns="http://www.w3.org/2000/svg" 
        height="24px" 
        viewBox="0 -960 960 960" 
        width="24px" 
        fill="#6a7282"
        >
        <path d="M200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h360v80H200v560h560v-360h80v360q0 33-23.5 56.5T760-120H200Zm80-160h80v-280h-80v280Zm160 0h80v-400h-80v400Zm160 0h80v-160h-80v160Zm80-320v-80h-80v-80h80v-80h80v80h80v80h-80v80h-80ZM480-480Z"/>
        </svg>

        <div class="h-[25px] w-0.5 bg-gray-200 ml-2 mr-2"></div>

        <!-- LABEL ICON -->
        <svg
        class="toolbar-toggle cursor-pointer"
        data-toolbar-target="label"
        onclick="handleToolbarClick(event)"
        xmlns="http://www.w3.org/2000/svg"
        height="24px"
        viewBox="0 -960 960 960"
        width="24px"
        fill="#6a7282"
        >
        <path d="M480-160v-80h120l180-240-180-240H160v200H80v-200q0-33 23.5-56.5T160-800h440q19 0 36 8.5t28 23.5l216 288-216 288q-11 15-28 23.5t-36 8.5H480Zm-10-320ZM200-120v-120H80v-80h120v-120h80v120h120v80H280v120h-80Z"/>
        </svg>


    </div>
</div>



<!-- LABEL INPUT PANEL -->
<div data-toolbar-panel="label" id="modal-label-input-container" class="hidden relative mb-6 border border-dashed border-gray-900/25 rounded-lg">
    <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none z-20 ps-3.5">
        <svg class="shrink-0 size-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#6a7282"><path d="M480-160v-80h120l180-240-180-240H160v200H80v-200q0-33 23.5-56.5T160-800h440q19 0 36 8.5t28 23.5l216 288-216 288q-11 15-28 23.5t-36 8.5H480Zm-10-320ZM200-120v-120H80v-80h120v-120h80v120h120v80H280v120h-80Z"/></svg>
    </div>
    <input type="text" id="eventLabel"  class="py-2 ps-10 pe-16 block w-full bg-white border-gray-200 rounded-lg text-sm focus:outline-hidden focus:border-blue-500 focus:ring-blue-500" placeholder="Add event label" />
</div>



<!-- USER SEARCH MODAL PANEL -->
<div data-toolbar-panel="search" id="modal-user-search-box" class="hidden relative mb-6 border border-dashed border-gray-900/25 rounded-lg">
    <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none z-20 ps-3.5">
        <svg class="shrink-0 size-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8" /><path d="m21 21-4.3-4.3" /></svg>
    </div>
    <input type="text" id="searchModal" class="py-2 ps-10 pe-16 block w-full bg-white border-gray-200 rounded-lg text-sm focus:outline-hidden focus:border-blue-500 focus:ring-blue-500" placeholder="Search by name or email" />
</div>
<!-- USER LIST BOX PANEL -->
<div data-toolbar-panel="search" id="user-search-box" class="hidden mt-4 z-50 rounded-lg bg-white p-4 pr-0 border border-dashed border-gray-900/25">
    <div class="h-[175px] min-h[175px] max-h-[175px] contain-content overflow-y-scroll">
        @include('Components.List.user-list-modal', ['users' => $users])
    </div>
</div>



<!-- POLL BOX PANEL -->
<div data-toolbar-panel="poll" id="user-poll-box" class="hidden mt-4 z-50 rounded-lg bg-white p-4 pr-0 border border-dashed border-gray-900/25">
    
    <div class="h-[240px] min-h[240px] max-h-[240px] contain-content overflow-y-scroll">

        <!-- Question Label -->
        <label style="font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji'; font-size: 14px; color: #cccccc;">Poll</label>
        
        <!-- Question Container -->
        <div class="relative border border-dashed border-gray-300 rounded-lg p-2 mb-2">
            <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none z-20 ps-3.5">
                <svg  class="shrink-0 size-6 text-gray-400" xmlns="http://www.w3.org/2000/svg"  height="24px"  viewBox="0 -960 960 960"  width="24px"  fill="#6a7282"><path d="M200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h360v80H200v560h560v-360h80v360q0 33-23.5 56.5T760-120H200Zm80-160h80v-280h-80v280Zm160 0h80v-400h-80v400Zm160 0h80v-160h-80v160Zm80-320v-80h-80v-80h80v-80h80v80h80v80h-80v80h-80ZM480-480Z"/></svg>
            </div>
            <input type="text" id="modal-user-poll-input" class="w-full text-sm py-2 px-3 pl-8 border rounded-md focus:outline-none focus:ring-1 focus:ring-[#2b7fff] shadow-[0_0_5px_#2b7fff]" placeholder="Ask a question" />
        </div>

        <!-- Options Label -->
        <label style="font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji'; font-size: 14px; color: #cccccc;">Options</label>
        
        <!-- Options Container -->
        <div id="poll-options-container" class="space-y-2">
            <!-- Options will be added here -->
        </div>
    </div>
</div>

<script>
let optionCount = 0;
let livePreviewClone = null;

// Create poll option input
function createPollOptionInput(index) {
  const wrapper = document.createElement('div');
  wrapper.className = 'relative border border-dashed border-gray-300 rounded-lg p-2';

  const input = document.createElement('input');
  input.type = 'text';
  input.className = 'w-full text-sm py-2 px-3 border rounded-md focus:outline-none focus:ring-1 focus:ring-[#2b7fff] focus:shadow-[0_0_5px_#2b7fff]';
  input.placeholder = "+ Add";
  input.dataset.index = index;

  input.addEventListener('input', handlePollOptionInput);

  wrapper.appendChild(input);
  return wrapper;
}

// Handle input changes in poll options
function handlePollOptionInput() {
  const container = document.getElementById('poll-options-container');
  let inputs = Array.from(container.querySelectorAll('input'));

  // Remove empty inputs that are not the last
  inputs.forEach((input, i) => {
    if (input.value.trim() === '' && i < inputs.length - 1) {
      input.parentElement.remove();
    }
  });

  // Re-check inputs after removals
  inputs = Array.from(container.querySelectorAll('input'));
  const allFilled = inputs.every(input => input.value.trim() !== '');

  if (allFilled) {
    container.appendChild(createPollOptionInput(optionCount++));
  }

  updatePollPreview();
}

// Update poll preview and append to #messages
function updatePollPreview() {
  const questionInput = document.getElementById('modal-user-poll-input');
  const container = document.getElementById('poll-options-container');
  const question = questionInput.value.trim();

  const options = Array.from(container.querySelectorAll('input'))
    .map(input => input.value.trim())
    .filter(value => value !== '');

  // If nothing is filled, remove preview
  if (!question && options.length === 0) {
    if (livePreviewClone) {
      livePreviewClone.remove();
      livePreviewClone = null;
    }
    return;
  }

  // Create or update the preview clone
  if (!livePreviewClone) {
    const template = document.getElementById('poll-message-preview-template');
    livePreviewClone = template.cloneNode(true);
    livePreviewClone.id = 'poll-message-preview-live'; // avoid ID conflict
    livePreviewClone.classList.remove('hidden');
    livePreviewClone.classList.add('flex');
    document.getElementById('messages').appendChild(livePreviewClone);
    setTimeout(() => {
        const dropZone = document.getElementById("drop-zone");
        if (dropZone) {
            dropZone.scrollTo({ top: dropZone.scrollHeight, behavior: "smooth" });
        }
    }, 1000);

    setTimeout(() => {
        const messageContainer = document.getElementById("messages");
        const messages = messageContainer.querySelectorAll(".bubble");
        const lastMessage = messages[messages.length - 1] || null;
        const messageText = lastMessage.querySelector("p");

        if (lastMessage) {
            lastMessage.classList.add("highlight-message");
            setTimeout(() => lastMessage.classList.remove("highlight-message"), 4000);
        }
        if (messageText) {
            messageText.classList.add("message-text-color");
            setTimeout(() => messageText.classList.remove("message-text-color"), 4500);
        }
        if (event) {
            event.setProp("backgroundColor", null);
        }
    }, 1000);
  }

  // Update content inside the clone
  const previewQuestion = livePreviewClone.querySelector('#poll-preview-question');
  const previewOptions = livePreviewClone.querySelector('#poll-preview-options');
  const submitContainer = livePreviewClone.querySelector('.poll-submit-container');

  previewQuestion.textContent = question || 'Your question will appear here';
  previewOptions.innerHTML = '';

  options.forEach(optionText => {
    const li = document.createElement('li');
    li.textContent = optionText;
    li.className = 'cursor-pointer px-2 py-1 rounded-md bg-white hover:bg-gray-200 hover:text-blue-600';
    previewOptions.appendChild(li);
  });

  // Fade in Submit button when 2+ options
  if (options.length >= 2) {
    submitContainer.style.opacity = '1';
    submitContainer.style.transition = 'opacity 0.5s ease';
  } else {
    submitContainer.style.opacity = '0';
  }
}

// Initialize inputs on page load
document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('poll-options-container');
  container.innerHTML = '';
  container.appendChild(createPollOptionInput(optionCount++));
  container.appendChild(createPollOptionInput(optionCount++));

  document.getElementById('modal-user-poll-input')
    .addEventListener('input', updatePollPreview);
});
</script>
