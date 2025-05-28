{{-- CALENDAR EVENT MODAL--}}
<div id="event-modal" class="relative w-full bg-white z-[99] hidden shadow-lg overflow-visible scale-90">

    <button
        onclick="closeModal()" style="right: 1.25rem;" class="absolute cursor-pointer top-4 md:top-2 lg:top-2 right-4 text-gray-500 hover:text-gray-700 z-50" aria-label="Close Modal">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 z-50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>

    <div class="bg-white rounded-lg shadow-lg p-6 contain-content w-full">
        <div class="modal-content flex mb-4 w-full">
            <div class="min-w-[300px]">
                <div id="eventOrganiserNameAndProfilePicture" class="flex items-center mb-4 w-full">
                    <img id="modal-profile-picture" 
                        class="w-16 h-16 rounded-full object-cover mr-4" 
                        src="" 
                        alt="User Profile">
                    <div>
                        <h2 id="modal-user-name" class="text-lg font-semibold text-gray-700"></h2>
                        <p class="text-sm text-gray-500">Event Date: <span id="modal-event-date" class="font-medium"></span>&nbsp;&nbsp;&nbsp;<input type="time" name="time" id="modal-event-time"></p>
                    </div>
                    <input type="hidden" name="event_id" id="eventId">
                </div>
                <x-input.form-input class="h-8" type="text" name="event_name" id="eventName" label="Event Name" value="{{ old('event_name') }}"  required />
                @include('Components.Calendar.Shared.event-modal-toolbar', ['users' => $users])
            </div>
            <div class="drop-zone-container flex-grow ml-4">
                <div id="drop-zone" data-event-id="" 
                    class="w-full mt-2 pt-10 flex-grow h-full justify-center rounded-lg border border-dashed border-gray-900/25 text-center text-[#cccccc]"
                    ondragover="event.preventDefault()" 
                    ondrop="handleDrop(event)">
                    <p class="pl-1 pt-10 pb-5">Drag and drop files</p>
                    <div id="file-preview" class="mt-4 space-y-4 px-6"></div>
                    
                    <div id="team-members" class="mt-8 px-6">Team Members</div>

                    <!-- LIVE POLL PREVIEW (JS will control visibility and content) -->
                    <div id="poll-message-preview-template" class="message-wrapper w-full justify-end hidden">
                    <div class="message bubble text-left w-[200px] p-2 mb-4 rounded-2xl relative shadow-md bg-[#d9fdd3]">
                        <div class="flex flex-row align-center mb-2">
                        {{-- <img 
                            class="rounded-full bg-gray-50 h-8 w-8 left-1 mr-2 flex-shrink-0 object-cover" 
                            src="{{  }}" 
                            alt="You" /> --}}
                        <div class="flex items-center">You:</div>
                        </div>
                        <div class="text-black" id="poll-preview-question"></div>
                        <ul id="poll-preview-options" class="mt-2 space-y-1"></ul>
                        <div class="text-right text-xs text-gray-500 pr-2">
                        {{ now()->format('H:i') }}
                        </div>
                    </div>
                    </div>


                    <div class="mt-8 mb-8">Messages</div>
                    <div id="messages" class="bg-opacity-0 space-y-2 bg-contain bg-center px-6">
                        <!-- Messages will be appended here via Turbo Streams -->
                    </div>
                    
                </div>
            </div>

        </div>
        <div class="flex flex-row items-center">
            <button data-user-id="{{ auth('web')->id() }}" onclick="deleteCalendarEvent()" 
                    class="delete-event-button w-full max-w-[300px] py-2 px-4 text-white bg-blue-500 hover:bg-blue-600 rounded-lg shadow focus:outline-none focus:ring-2 focus:ring-blue-300 focus:ring-offset-2">
                Delete Event
            </button>
            <form method="POST" id="message-form" class="flex items-center flex-grow 0 rounded-lg p-4">
                @csrf
                <input type="hidden" name="event_id" id="event-id" value="{{ $eventId ?? '' }}">
                
                <!-- Textarea for message input -->
                <textarea 
                    name="content" 
                    id="message-content"
                    placeholder="Type your message..." 
                    class="flex-grow bg-white border border-gray-300 rounded-lg p-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                    rows="1"></textarea>
                
                <!-- Send button -->
                <div
                    id="send-message-button"
                    class="cursor-pointer ml-3 bg-blue-500 hover:bg-blue-600 text-white font-medium px-4 py-2 rounded-lg shadow-md flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Send
                </div>
            </form>
        </div>

    </div>
</div>