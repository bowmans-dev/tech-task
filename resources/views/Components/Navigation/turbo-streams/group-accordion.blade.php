
        <li class="hs-accordion" id="groups-accordion">
            <button type="button"
                class="hs-accordion-toggle w-full text-start flex items-center gap-x-3.5 py-2 px-2.5 text-sm text-gray-800 rounded-lg hover:bg-gray-100 focus:outline-hidden focus:bg-gray-100 dark:bg-neutral-800 dark:hover:bg-neutral-700 dark:focus:bg-neutral-700 dark:text-neutral-200"
                aria-expanded="true" aria-controls="groups-accordion-child">
                <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                </svg>
                Groups

                <svg class="hs-accordion-active:block ms-auto hidden size-4" xmlns="http://www.w3.org/2000/svg" width="24"
                    height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="m18 15-6-6-6 6" />
                </svg>

                <svg class="hs-accordion-active:hidden ms-auto block size-4" xmlns="http://www.w3.org/2000/svg" width="24"
                    height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="m6 9 6 6 6-6" />
                </svg>
            </button>

            <div id="groups-accordion-child"
                class="hs-accordion-content w-full overflow-hidden transition-[height] duration-300 hidden" role="region"
                aria-labelledby="groups-accordion">
                @isset($groups)
                    @foreach ($groups as $group)
                        <ul class="relative hs-accordion-group ps-8 pt-1 space-y-1" data-hs-accordion-always-open>
                
                            <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#5D0E07" onclick="deleteGroup({{ $group->id }})" class="absolute left-2 top-4 cursor-pointer hover:fill-red-600">
                                <path d="M288-444h384v-72H288v72ZM480.28-96Q401-96 331-126t-122.5-82.5Q156-261 126-330.96t-30-149.5Q96-560 126-629.5q30-69.5 82.5-122T330.96-834q69.96-30 149.5-30t149.04 30q69.5 30 122 82.5T834-629.28q30 69.73 30 149Q864-401 834-331t-82.5 122.5Q699-156 629.28-126q-69.73 30-149 30Zm-.28-72q130 0 221-91t91-221q0-130-91-221t-221-91q-130 0-221 91t-91 221q0 130 91 221t221 91Zm0-312Z"></path>
                            </svg>

                            <li>
                                <button type="button"
                                    class="hs-accordion-toggles w-full text-start flex items-center gap-x-3.5 py-2 px-2.5 text-sm text-gray-800 rounded-lg hover:bg-gray-100 focus:outline-hidden focus:bg-gray-100 dark:bg-neutral-800 dark:hover:bg-neutral-700 dark:focus:bg-neutral-700 dark:text-neutral-200"
                                    aria-expanded="false"
                                    aria-controls="group-{{ $group->id }}-content"
                                    data-users="{{ json_encode($group->users) }}" data-turbo-permanent>
                                    {{ $group->name }}
                                    <svg class="hs-accordion-active:block ms-auto hidden size-4" xmlns="http://www.w3.org/2000/svg"
                                        width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path d="m18 15-6-6-6 6" />
                                    </svg>
                                    <svg class="hs-accordion-active:hidden ms-auto block size-4" xmlns="http://www.w3.org/2000/svg"
                                        width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path d="m6 9 6 6 6-6" />
                                    </svg>
                                </button>
                            

                                <div id="group-{{ $group->id }}-content"
                                    class="hs-accordion-content w-full overflow-hidden transition-[height] duration-300 hidden"
                                    role="region" aria-labelledby="group-{{ $group->id }}">
                                    <ul class="pt-1 space-y-1" id="user-list-{{ $group->id }}">
                                        @foreach($group->users as $user)
                                            <li class="flex items-center gap-x-4 py-2 px-2.5">
                                                <a href="{{ route('users.show', $user->id) }}" class="flex items-center w-full">
                                                    <img src="{{ $user->profile_picture ? '/storage/' . $user->profile_picture : '/storage/default_profile_image.png' }}" 
                                                        alt="{{ $user->first_name ?? $user->email }}" 
                                                        class="w-8 h-8 rounded-full flex-shrink-0 object-cover">
                                                    <span 
                                                        class="ml-2 text-sm text-gray-800 dark:text-neutral-200 truncate" 
                                                        style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 130px;">
                                                        {{ ($user->first_name && $user->last_name) ? $user->first_name . ' ' . $user->last_name : $user->email }}
                                                    </span>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </li>
                        
                        </ul>
                    @endforeach
                @endisset
            </div>
        </li>