@extends('layouts.app')

@section('title', 'Calendar')

<style>
    
    .fc-event-title {
        font-size: 10px !important;
    }

	#external-events {
		position: fixed;
		z-index: 2;
		top: 20px;
		left: 20px;
		width: 150px;
		padding: 0 10px;
		border: 1px solid #ccc;
		background: #eee;
	}

	#external-events .fc-event {
		margin: 1em 0;
		cursor: move;
	}

	#calendar-container {
		position: relative;
		z-index: 1;
		margin-left: 200px;
		overflow-y: hidden;
		overflow: hidden;
	}
	#calendar-container::-webkit-scrollbar {
		display: none;
	}

	#calendar {
		max-width: 900px;
		margin: 20px auto;
		overflow-y: hidden;
		overflow: hidden;
	}
	#calendar::-webkit-scrollbar, .modal::-webkit-scrollbar {
		display: none;
	}
	.modal {
		margin-top: 100px;
	}
  
	.booked-event {
		background-color: #145164;
		color: white;
		height: 30px;
		font-size: 14px;
		display: grid;
		place-items: center;
		text-align: center;
	}
  
  	.fc .fc-col-header-cell-cushion {
		padding-top: 5px; /* an override! */
		padding-bottom: 5px; /* an override! */
		border-radius: 8px;
    }
    

    .fc-scroller::-webkit-scrollbar {
       	display: none;
    }
    
    .fc .fc-col-header-cell {
      	background-color: #fff;
    }

    .fc .fc-day {
      	background-color: #1d1d1f;
    }

    h2 {
        color: #000;
    }

    
    .fc-unthemed td.fc-today {
        background: #333;
    }
    
    .fc-more {
        color: #525254;
    }
    a:not([href]):not([tabindex]) {
        color: #525254; 
        text-decoration: none;
    }
    a:not([href]):not([tabindex]):hover {
        color: #fff; 
        text-decoration: none;
    }
    
    .fc-more-popover .fc-event-container {
        padding: 10px;
        background-color: #1d1d1f;
    }
    
    .fc-popover .fc-header .fc-title {
        margin: 0 2px;
        color: #000;
    }
    
    .fc-event {
        border: 0px solid #fff;
        display: grid;
        place-items: center;
    }
    div.fc-content {
		text-align: center;
		align-items: center;
		align-content: center;
		text-align: center;
		height: 30px;
		font-size: 14px;
    }
    @media (min-width: 500px) {
        
        .fc-event {
            border: 0px solid #fff;
            display: grid;
            place-items: center;
            height: 20px;
        }
        div.fc-content {
          text-align: center;
          align-items: center;
          align-content: center;
          text-align: center;
          height: 20px;
          font-size: 12px;
        }
    }
    
    @media (max-width: 500px) {
        div.fc-content {
            font-size: 12px;
        }
        div.fc-left h2 {
            font-size: 20px;
        }
    }
    @media (max-width: 400px) {
        div.fc-content {
            font-size: 10px;
        }
    }
    @media (max-width: 380px) {
        img.rounded {
			display: none !important;
		}
    }
    
    div.fc-time {
        align-self: center;
    }
    .fc-content span.fc-title {
        display: none;
    }
    span.fc-close, fc-icon-x {
        color: #000;
    }
    
    .fc-popover .fc-day-grid-event {
        color: #fff; 
        margin-bottom: 5px;
        width: 150px;
    }
    .fc-popover .fc-day-grid-event .fc-content .fc-time {
        font-size: 16px;
    }
    .fc-popover {
        width: min-content;
    }
    
    .fc-unthemed .fc-content, .fc-unthemed .fc-divider, .fc-unthemed .fc-list-heading td, .fc-unthemed .fc-list-view, .fc-unthemed .fc-popover, .fc-unthemed .fc-row, .fc-unthemed tbody, .fc-unthemed td, .fc-unthemed th, .fc-unthemed thead {
     	border-color: #000; 
	}

	.fc-event:not(.booked-event) {
		background-color: #27323f;
	}

  
	.question {
		width: 100%;
		height: min-content;
		background-color: #1463b9;
		color: #fff;
		padding-left: 14px;
		padding-top: 4px;
		padding-bottom: 4px;
		font-weight: bold;
	}
	.answer {
		margin-bottom: 15px;
	}
  
    #patientEmail, #patientEmail a {
      -webkit-user-select: text; /* Safari */
      -moz-user-select: text; /* Firefox */
      -ms-user-select: text; /* Internet Explorer/Edge */
      user-select: text; /* Non-prefixed version, currently supported by Chrome, Opera and Edge */
    }
    
    #treatment, #doctorName, #practiceName {
        white-space: nowrap;
        word-break: keep-all;
    }
	.fc-event {
		border: solid 1px transparent;
		padding: 3px;
	}

	.fc-content {
		display: flex;
		align-items: center;
		justify-content: space-evenly;
	}

    @media (max-width: 600px) {
        .file-text {
            display: none;
        }
    }
</style>

@section('content')

    <x-navigation.breadcrumb :groups="$groups" :breadcrumbs="[
        ['name' => 'Users', 'url' => route('users.index')], ['name' => '', 'url' => '']
    ]" />

    <x-navigation.sidebar :groups="$groups" />

    <div id="event-modal" class="fixed bottom-0 lg:left-[250px] lg:w-fullitems-center justify-center z-[9999] hidden">

        <button
            onclick="closeModal()" class="absolute cursor-pointer top-2 right-2 text-gray-500 hover:text-gray-700" aria-label="Close Modal">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <div class="bg-white rounded-lg shadow-lg p-6 lg:min-w-[800px]">
            <div class="flex items-center mb-4 w-full">
                <div>
                    <div class="flex items-center mb-4 w-full">
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
                    <div class="mt-6">
                        <button onclick="saveCalendarEvent()" 
                                class="w-full max-w-[300px] py-2 px-4 text-white bg-blue-500 hover:bg-blue-600 rounded-lg shadow focus:outline-none focus:ring-2 focus:ring-blue-300 focus:ring-offset-2">
                            Save Event
                        </button>
                    </div>
                </div>
                <div id="drop-zone" 
                    class="mt-2 ml-4 flex-grow h-full justify-center rounded-lg border border-dashed border-gray-900/25 px-6 py-10 text-center text-[#cccccc]"
                    ondragover="event.preventDefault()" 
                    ondrop="handleDrop(event)">
                    <p class="pl-1">Drag and drop files</p>
                    <div id="file-preview" class="mt-4 space-y-4"></div>
                </div>

            </div>
        </div>
    </div>

    <div class="lg:ml-[250px]" style="calendar-wrapper max-width: 900px; margin-bottom: 230px;">
        <div id='calendar' style="height: auto; padding: 17px; padding-bottom: 0px;"></div>
        <div class="text-center text-gray-400">Drag and Drop a Group Member to Create a New Event</div>
    </div>
    <br><br>

  	<script>

        function closeModal () {
            const modal = document.getElementById('event-modal');
            modal.classList.add('hidden');
        };

        function saveCalendarEvent() {
            const formData = new FormData();
            const modal = document.getElementById('event-modal');

            const eventName = document.getElementById('eventName').value.trim();
            const id = document.getElementById('eventId').value;

            const userId = modal.dataset.userId;
            const date = modal.dataset.date;
            const time = document.getElementById('modal-event-time').value.trim();

            const allDay = time === '';
            const eventId = allDay ? date : `${date}T${time}`;

            formData.append('event_name', eventName);
            formData.append('user_id', userId);
            formData.append('date', date);
            formData.append('time', time);
            formData.append('allDay', allDay);

            if (id) {
                formData.append('id', id);
            }

            droppedFiles.forEach(file => {
                const customFileName = `${eventId}/${userId}/${file.name}`;
                formData.append('files[]', file, customFileName);
            });

            fetch('/calendar/events/save', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log("Response status:", response.status);
            console.log("Response headers:", response.headers);
                if (!response.ok) throw new Error('Upload failed');
                return response.json();
            })
            .then(data => {
                alert('Event saved!');
                
                document.getElementById('eventName').value = '';
                document.getElementById('file-preview').innerHTML = '';
                document.getElementById('modal-profile-picture').src = '';
                document.getElementById('modal-user-name').textContent = '';
                document.getElementById('modal-event-date').textContent = '';
                document.getElementById('modal-event-time').value = '';

                window.droppedFiles = [];

                // Close modal
                modal.classList.remove('flex');
                modal.classList.add('hidden');

                window.location.reload();
            })
            .catch(error => {
                console.error('Error saving event:', error);
                alert('There was a problem saving the event.');
            });


        }
  	</script>
  
</body>
</html>