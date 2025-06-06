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