@extends('role.users.layouts.calendar')
<!-- Extend the layout -->

@section('title', 'Your Profile')
<!-- Set the page title -->

@section('content')
    <x-navigation.breadcrumb :breadcrumbs="[
    ['name' => 'Users', 'url' => route('users.index')],
    ['name' => Auth::user()->first_name . ' ' . Auth::user()->last_name, 'url' => route('users.show', Auth::user()->id)],
]" />
<main class="container mx-auto py-8">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl">

            <!-- Profile Picture and Name -->
            <div class="pt-10 grid grid-cols-1 lg:col-span-2 place-items-center">
                <div class="w-full grid place-items-center mb-2">
                    <img 
                        class="rounded-full bg-gray-50 h-36 w-36 flex-shrink-0 object-cover"
                        src="{{ Auth::user()->profile_picture ? asset('storage/' . Auth::user()->profile_picture) : asset('storage/default_profile_image.webp') }}" 
                        alt="{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}'s profile picture" />
                </div>

                <h2 class="lg:col-span-2 text-base font-semibold text-gray-900 text-center mb-2">
                    {{ Auth::user()->first_name }} {{ Auth::user()->last_name }}
                </h2>

            </div>

            <!-- User Details -->
            <div class="space-y-12">
                <div class="border-b border-gray-900/10 pb-6">

                    <div class="profile-details flex flex-row flex-wrap w-full justify-evenly mb-2">

                        <div class="w-[200px] text-center">
                            <label class="block text-sm/6 font-medium text-gray-900">Email</label>
                            <p class='block w-full rounded-md px-3 py-1.5 text-base text-gray-900 sm:text-sm/6'>
                                {{ Auth::user()->email }}
                            </p>
                        </div>

                        <div class="w-[200px] text-center">
                            <label class="block text-sm/6 font-medium text-gray-900">Country</label>
                            <p class='block w-full rounded-md px-3 py-1.5 text-base text-gray-900 sm:text-sm/6'>
                                {{ Auth::user()->country }}
                            </p>
                        </div>

                        <div class="w-[200px] text-center">
                            <label class="block text-sm/6 font-medium text-gray-900">Phone Number</label>
                            <p class='block w-full rounded-md px-3 py-1.5 text-base text-gray-900 sm:text-sm/6'>
                                {{ Auth::user()->phone }}
                            </p>
                        </div>           
            
                    </div>

                    <!-- Edit Profile Button -->
                    <div class="grid place-items-center">
                        <a href="/edit_profile" class="w-max">
                            <div class="rounded-full p-0.5 outline-[#6a7282] outline-2 w-max">
                                <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="#6a7282">
                                    <path d="M200-200h57l391-391-57-57-391 391v57Zm-80 80v-170l528-527q12-11 26.5-17t30.5-6q16 0 31 6t26 18l55 56q12 11 17.5 26t5.5 30q0 16-5.5 30.5T817-647L290-120H120Zm640-584-56-56 56 56Zm-141 85-28-29 57 57-29-28Z"/>
                                </svg>
                            </div>
                        </a>
                    </div>

                </div>
            </div>
            
        </div>
        
        @include('Components.Calendar.Users.calendar', ['users' => $users])
        
@endsection