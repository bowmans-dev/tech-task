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
        <div id="event-modal" class="relative w-full bg-white z-[99] hidden shadow-lg overflow-visible scale-90">
    
            <button
                onclick="closeModal()" class="absolute cursor-pointer top-2 right-4 text-gray-500 hover:text-gray-700 z-50" aria-label="Close Modal">
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
                            
                        </div>
                        <x-input.form-input class="h-8" type="text" name="event_name" id="eventName" label="Event Name" value="{{ old('event_name') }}"  required />
                        <div class="mt-6">
                            <div class="flex flex-row flex-grow p-2 mb-6 border border-dashed border-gray-900/25 rounded-lg">
                                <svg class="cursor-pointer" onclick="document.getElementById('user-search-box').classList.toggle('hidden'); document.getElementById('modal-user-search-box').classList.toggle('hidden'); this.classList.toggle('button-highlight'); " xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#6a7282"><path d="M360-240ZM40-160v-112q0-34 17.5-62.5T104-378q62-31 126-46.5T360-440q32 0 64.5 3.5T489-425q-13 17-22.5 35.5T451-351q-23-5-45.5-7t-45.5-2q-56 0-111 13.5T140-306q-9 5-14.5 14t-5.5 20v32h323q4 22 11 42t18 38H40Zm320-320q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47Zm400-160q0 66-47 113t-113 47q-11 0-28-2.5t-28-5.5q27-32 41.5-71t14.5-81q0-42-14.5-81T544-792q14-5 28-6.5t28-1.5q66 0 113 47t47 113Zm-400 80q33 0 56.5-23.5T440-640q0-33-23.5-56.5T360-720q-33 0-56.5 23.5T280-640q0 33 23.5 56.5T360-560Zm0-80Zm320 440q34 0 56.5-20t23.5-60q1-34-22.5-57T680-360q-34 0-57 23t-23 57q0 34 23 57t57 23Zm0 80q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 23-5.5 43.5T818-198L920-96l-56 56-102-102q-18 11-38.5 16.5T680-120Z"/></svg>
                                <div class="h-[25px] w-0.5 bg-gray-200 ml-2 mr-2"></div>
                            </div>
                        </div>
                        <div id="modal-user-search-box" class="hidden relative mb-6 border border-dashed border-gray-900/25 rounded-lg">
                            <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none z-20 ps-3.5">
                                <svg class="shrink-0 size-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="11" cy="11" r="8" />
                                    <path d="m21 21-4.3-4.3" />
                                </svg>
                            </div>
                            <input 
                                type="text" 
                                id="searchModal" 
                                class="py-2 ps-10 pe-16 block w-full bg-white border-gray-200 rounded-lg text-sm focus:outline-hidden focus:border-blue-500 focus:ring-blue-500" 
                                placeholder="Search by name or email" />
                        </div>
                        <div id="user-search-box" class="hidden mt-4 z-50 rounded-lg bg-white p-4 border border-dashed border-gray-900/25">
                            <div class="h-[175px] min-h[175px] max-h-[175px] contain-content overflow-y-scroll">
                                @include('Components.List.user-list-modal', ['users' => $users])
                            </div>
                        </div>
                        
                    </div>
                    <div class="flex-grow">
                        <div id="drop-zone" data-event-id=""
                            class="mt-2 ml-4 flex-grow h-full justify-center rounded-lg border border-dashed border-gray-900/25 px-6 py-10 text-center text-[#cccccc]"
                            ondragover="event.preventDefault()" 
                            ondrop="handleDrop(event)">
                            <p class="pl-1">Drag and drop files</p>
                            <div id="file-preview" class="mt-4 space-y-4"></div>
                            
                            <div id="team-members" class="mt-8">Team Members</div>
    
                            <div class="mt-8 mb-8">Messages</div>
                            <div id="messages" class="space-y-2">
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

    </div>
</main>