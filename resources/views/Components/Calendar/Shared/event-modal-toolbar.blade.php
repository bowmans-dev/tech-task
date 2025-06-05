<style>
canvas {
    width: 100%;
    height: 38px;
    padding-left: 40px;
    max-width: 90vw;
}
@media (min-width: 500px) {
    canvas, video {
        max-width: 300px;
    }
    video {
        transform: scaleX(-1);
    }
}
</style>

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

        <div class="h-[25px] w-0.5 bg-gray-200 ml-2 mr-2"></div>

        <!-- AUDIO ICON -->
        <svg 
        class="toolbar-toggle cursor-pointer"
        data-toolbar-target="audio"
        onclick="handleToolbarClick(event); toggleGroupAudioRoomWithVisualizer();" 
        xmlns="http://www.w3.org/2000/svg" 
        height="24px" 
        viewBox="0 -960 960 960" 
        width="24px" 
        fill="#6a7282"
        >
        <path d="M480-400q-50 0-85-35t-35-85v-240q0-50 35-85t85-35q50 0 85 35t35 85v240q0 50-35 85t-85 35Zm0-240Zm-40 520v-123q-104-14-172-93t-68-184h80q0 83 58.5 141.5T480-320q83 0 141.5-58.5T680-520h80q0 105-68 184t-172 93v123h-80Zm40-360q17 0 28.5-11.5T520-520v-240q0-17-11.5-28.5T480-800q-17 0-28.5 11.5T440-760v240q0 17 11.5 28.5T480-480Z"/>
        </svg>

        <div class="h-[25px] w-0.5 bg-gray-200 ml-2 mr-2"></div>

        <!-- VIDEO CALL ICON -->
        <svg 
        class="toolbar-toggle cursor-pointer"
        data-toolbar-target="video"
        onclick="handleToolbarClick(event); toggleGroupVideoCall();" 
        xmlns="http://www.w3.org/2000/svg" 
        height="24px" 
        viewBox="0 -960 960 960" 
        width="24px" 
        fill="#6a7282">
        <path d="M360-320h80v-120h120v-80H440v-120h-80v120H240v80h120v120ZM160-160q-33 0-56.5-23.5T80-240v-480q0-33 23.5-56.5T160-800h480q33 0 56.5 23.5T720-720v180l160-160v440L720-420v180q0 33-23.5 56.5T640-160H160Zm0-80h480v-480H160v480Zm0 0v-480 480Z"/>
        </svg>

        <div class="h-[25px] w-0.5 bg-gray-200 ml-2 mr-2"></div>

        <!-- SCREEN SHARE ICON -->
        <svg 
        class="toolbar-toggle cursor-pointer"
        data-toolbar-target="screen"
        onclick="handleToolbarClick(event); toggleGroupScreenShare();" 
        xmlns="http://www.w3.org/2000/svg" 
        height="24px" 
        viewBox="0 -960 960 960" 
        width="24px" 
        fill="#6a7282"
        >
        <path d="M320-400h80v-80q0-17 11.5-28.5T440-520h80v80l120-120-120-120v80h-80q-50 0-85 35t-35 85v80ZM160-240q-33 0-56.5-23.5T80-320v-440q0-33 23.5-56.5T160-840h640q33 0 56.5 23.5T880-760v440q0 33-23.5 56.5T800-240H160Zm0-80h640v-440H160v440Zm0 0v-440 440ZM40-120v-80h880v80H40Z"/>
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


<!-- GROUP AUDIO PANEL -->
<div data-toolbar-panel="audio" id="modal-group-audio-input-container" class="hidden relative mb-6 border border-dashed border-[#2b7fff] rounded-lg">
  <div style="outline: #2b7fff solid 1px; padding: 2px; height: 24px; width: 24px; top: 6px;" class="absolute rounded-full inset-y-0 start-[8px] flex items-center pointer-events-none z-20">
    <svg class="text-gray-400" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#2b7fff"><path d="M711-480Zm209 80H737q-3-21-9.5-41T711-480h126q-4-7-9-12t-12-9q-26-15-59.5-22t-76.5-7h-3q-20-23-43.5-40T582-599q23-5 47.5-8t50.5-3q53 0 99 11t86 32q26 14 40.5 41.5T920-463v63ZM680-640q-50 0-85-35t-35-85q0-50 35-85t85-35q50 0 85 35t35 85q0 50-35 85t-85 35Zm0-80q17 0 28.5-11.5T720-760q0-17-11.5-28.5T680-800q-17 0-28.5 11.5T640-760q0 17 11.5 28.5T680-720Zm0-40ZM249-480ZM40-400v-63q0-35 14.5-62.5T95-567q40-21 86-32t99-11q26 0 50.5 3t47.5 8q-28 12-51.5 29T283-530h-3q-43 0-76.5 7T144-501q-7 4-12 9t-9 12h126q-10 19-16.5 39t-9.5 41H40Zm240-240q-50 0-85-35t-35-85q0-50 35-85t85-35q50 0 85 35t35 85q0 50-35 85t-85 35Zm0-80q17 0 28.5-11.5T320-760q0-17-11.5-28.5T280-800q-17 0-28.5 11.5T240-760q0 17 11.5 28.5T280-720Zm0-40Zm200 480q-33 0-56.5-23.5T400-360v-120q0-33 23.5-56.5T480-560q33 0 56.5 23.5T560-480v120q0 33-23.5 56.5T480-280ZM450-80v-82q-72-11-121-67t-49-131h60q0 58 41 99t99 41q58 0 99-41t41-99h60q0 75-49 131t-121 67v82h-60Z"/></svg>
  </div>
  <canvas class="rounded-lg"></canvas>
</div>


<!-- GROUP VIDEO PANEL -->
<div data-toolbar-panel="video" id="modal-group-video-input-container" class="hidden relative min-w-[300px] mb-6 border border-dashed border-[#2b7fff] rounded-lg">
    <div style="outline: #2b7fff solid 1px; padding: 2px; height: 24px; width: 24px; top: 6px;" class="absolute rounded-full inset-y-0 start-[8px] flex items-center pointer-events-none z-20">
        <svg class="text-gray-400" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#2b7fff"><path d="M711-480Zm209 80H737q-3-21-9.5-41T711-480h126q-4-7-9-12t-12-9q-26-15-59.5-22t-76.5-7h-3q-20-23-43.5-40T582-599q23-5 47.5-8t50.5-3q53 0 99 11t86 32q26 14 40.5 41.5T920-463v63ZM680-640q-50 0-85-35t-35-85q0-50 35-85t85-35q50 0 85 35t35 85q0 50-35 85t-85 35Zm0-80q17 0 28.5-11.5T720-760q0-17-11.5-28.5T680-800q-17 0-28.5 11.5T640-760q0 17 11.5 28.5T680-720Zm0-40ZM249-480ZM40-400v-63q0-35 14.5-62.5T95-567q40-21 86-32t99-11q26 0 50.5 3t47.5 8q-28 12-51.5 29T283-530h-3q-43 0-76.5 7T144-501q-7 4-12 9t-9 12h126q-10 19-16.5 39t-9.5 41H40Zm240-240q-50 0-85-35t-35-85q0-50 35-85t85-35q50 0 85 35t35 85q0 50-35 85t-85 35Zm0-80q17 0 28.5-11.5T320-760q0-17-11.5-28.5T280-800q-17 0-28.5 11.5T240-760q0 17 11.5 28.5T280-720Zm0-40Zm200 480q-33 0-56.5-23.5T400-360v-120q0-33 23.5-56.5T480-560q33 0 56.5 23.5T560-480v120q0 33-23.5 56.5T480-280ZM450-80v-82q-72-11-121-67t-49-131h60q0 58 41 99t99 41q58 0 99-41t41-99h60q0 75-49 131t-121 67v82h-60Z"/></svg>
    </div>
    <canvas class="video-mic-canvas rounded-lg"></canvas>
</div>

<!-- GROUP SCREEN SHARE PANEL -->
<div data-toolbar-panel="screen" id="modal-group-screen-input-container" class="hidden relative max-w-[300px] min-w-[300px] mb-6 border border-dashed border-[#2b7fff] rounded-lg">
    <div style="outline: #2b7fff solid 1px; padding: 2px; height: 24px; width: 24px; top: 6px;" class="absolute rounded-full inset-y-0 start-[8px] flex items-center pointer-events-none z-20">
        <svg class="text-gray-400" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#2b7fff"><path d="M711-480Zm209 80H737q-3-21-9.5-41T711-480h126q-4-7-9-12t-12-9q-26-15-59.5-22t-76.5-7h-3q-20-23-43.5-40T582-599q23-5 47.5-8t50.5-3q53 0 99 11t86 32q26 14 40.5 41.5T920-463v63ZM680-640q-50 0-85-35t-35-85q0-50 35-85t85-35q50 0 85 35t35 85q0 50-35 85t-85 35Zm0-80q17 0 28.5-11.5T720-760q0-17-11.5-28.5T680-800q-17 0-28.5 11.5T640-760q0 17 11.5 28.5T680-720Zm0-40ZM249-480ZM40-400v-63q0-35 14.5-62.5T95-567q40-21 86-32t99-11q26 0 50.5 3t47.5 8q-28 12-51.5 29T283-530h-3q-43 0-76.5 7T144-501q-7 4-12 9t-9 12h126q-10 19-16.5 39t-9.5 41H40Zm240-240q-50 0-85-35t-35-85q0-50 35-85t85-35q50 0 85 35t35 85q0 50-35 85t-85 35Zm0-80q17 0 28.5-11.5T320-760q0-17-11.5-28.5T280-800q-17 0-28.5 11.5T240-760q0 17 11.5 28.5T280-720Zm0-40Zm200 480q-33 0-56.5-23.5T400-360v-120q0-33 23.5-56.5T480-560q33 0 56.5 23.5T560-480v120q0 33-23.5 56.5T480-280ZM450-80v-82q-72-11-121-67t-49-131h60q0 58 41 99t99 41q58 0 99-41t41-99h60q0 75-49 131t-121 67v82h-60Z"/></svg>
    </div>
    <canvas class="video-mic-canvas rounded-lg"></canvas>
</div>