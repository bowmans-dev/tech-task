<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Default Title')</title>
    @vite(['resources/js/calendar/Calendar.js'])
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

        /* @media (max-width: 600px) {
            .file-text {
                display: none;
            }
        } */
        #drop-zone {
            max-height: 500px;
            height: 99%;
            overflow-x: hidden;
            overflow-y: scroll;
            contain: content;
        }
        @media (max-width: 768px) {
            .modal-content {
                display: block;
                width: 100% !important;
                width: 100% !important;
            }
            .drop-zone-container {
                margin-left: 0px !important;
            }
            #drop-zone {
                height: 100%;
            }
            form#message-form {
                padding-left: 0px;
                padding-right: 0px; 
            }
            .save-event-button {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .profile-details {
                display: none;
            }
        }

        .button-highlight {
            fill: #2b7fff;
        }
        .fc-col-header, .fc,  .fc-daygrid-body, .fc-daygrid-body-unbalanced, .fc-scrollgrid-sync-table {
            width: 100% !important;
            height: 100% !important;
        }
        .fc .fc-daygrid-day-frame {
            overflow: hidden;
        }

        @media (min-width: 740px) {

            .modal-content {
                contain: content;
            }

        }

        #notification-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 300px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            z-index: 1000;
            cursor: pointer;
        }

        .notification {
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #2D89EF, #005A9E);
            color: white;
            padding: 12px;
            border-radius: 8px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.2);
            opacity: 0;
            transform: translateX(50px);
            transition: opacity 0.5s ease, transform 0.5s ease;
        }

        .notification img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #fff;
        }

        .notification-content {
            display: flex;
            flex-direction: column;
        }

        .notification-title {
            font-weight: bold;
            margin-left: 10px;
        }

        .notification-message {
            font-size: 14px;
        }

        /* Animation for notification appearing */
        .show {
            opacity: 1;
            transform: translateX(0);
        }

        .message p {
            color: black;
        }

        @keyframes highlightGradient {
            0% { background-color: #fff; color: #ccc; } /* Applies to container */
            50% { background-color: #2D89EF; color: #fff; }
            100% { background-color: #fff; color: #ccc; } /* Resets to default */
        }

        @keyframes messageTextColor {
            0% { color: black; } /* Ensure text starts black */
            50% { color: white; } /* Change to white during highlight */
            100% { color: black; } /* Return to black */
        }

        .highlight-message {
            animation: highlightGradient 4s ease-in-out 1;
        }

        /* Apply separate animation for message text */
        .highlight-message p {
            animation: messageTextColor 4.5s ease-in-out 1;
        }

        @media (max-width: 785px) {
            .delete-event-button {
                display: none;
            }
        }
        #messages {
            position: relative;
        }
        
        /* Create a white overlay using a pseudo-element */
        #messages::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: white;
            transition: opacity 0.5s ease-in-out; 
            z-index: 1;
        }

        .message {
            position: relative; 
            z-index: 2; /* Higher than the pseudo-element */
        }

        /* The .with-overlay class shows the white overlay */
        #messages.with-overlay::before {
            opacity: 1;
        }

        /* Removing the white overlay (or fading out) */
        #messages.no-overlay::before {
            opacity: 0;
        }


    </style>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100 h-full pt-16">

    @yield('content')
    
</body>
</html>