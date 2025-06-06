@extends('role.admin.layouts.calendar')

@section('title', 'Calendar')

@section('content')

    <x-navigation.breadcrumb :groups="$groups" :breadcrumbs="[
        ['name' => 'Users', 'url' => route('users.index')], ['name' => '', 'url' => '']
    ]" />

    <x-navigation.sidebar :groups="$groups" />


    <div class="lg:ml-[250px]" style="calendar-wrapper max-width: 900px; margin-bottom: 230px;">
        
        <div id='calendar' style="padding: 17px; padding-bottom: 0px;"></div>
        <div class="text-center text-gray-400">Drag and Drop a Group Member to Create a New Event</div>

        <br><br>
        <x-Calendar.Shared.event-modal :users="$users" />
      </div>
      <br><br>
      <div id="notification-container"></div>

<script>