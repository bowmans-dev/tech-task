<ul role="list" class="divide-y h-full divide-gray-100 p-3 user-row">
    @foreach ($users as $user)
    <li class="relative rounded-full flex flex-row p-1 h-10 mb-3 cursor-pointer hover:bg-gray-200"> <!-- Make it relative for children -->
      <a href="{{ route('users.show', $user->id) }}" class="flex items-center w-full">
        <!-- Profile Image (optional, aligned to the left) -->
        <img 
          class="rounded-full bg-gray-50 h-8 w-8 left-1 flex-shrink-0 object-cover" 
          src="{{ $user->profile_picture ? asset('storage/' . $user->profile_picture) : asset('storage/default_profile_image.png') }}" 
          alt="{{ $user->first_name }} {{ $user->last_name }}'s profile picture" />
      
        <!-- Left Block -->
        <div class="left-10 pl-8 w-40">
          <p class="text-xs font-semibold text-gray-900 no-wrap w-40">
            {{ $user->first_name }} {{ $user->last_name }}
          </p>
          <p class="text-xs text-gray-500 w-20">{{ $user->email }}</p>
        </div>
      
        <!-- Center Block -->
        <div class="center-block left-1/2 pl-24 w-40">
            <p class="text-xs text-gray-500 w-20 truncate">{{ $user->country ?? 'N/A' }}</p>
            <p class="text-xs text-gray-500 no-wrap w-40">{{ $user->phone ?? 'N/A' }}</p>
        </div>

        <!-- Center Right LG Screen Group Badge Block -->
        <div data-user-id="{{ $user->id }}"  class="group-badges-container hidden xl:flex xl:flex-wrap ml-[2rem] w-[250px] max-w-[250px]">
          @isset($groups)
            @foreach ($groups as $group)
            <p data-group-id="{{ $group->id }}" data-user-id="{{ $user->id }}" 
              class="group-badge {{ in_array($group->id, $user->groupIds) ? 'whitespace-nowrap inline-block text-[0.58rem] font-semibold text-white bg-gray-400 rounded-full mb-0.25 px-1.5 py-[0.1px] m-[1px] ml-2' : 'hidden' }}">
              {{ in_array($group->id, $user->groupIds) ? $group->name : '' }}
           </p>
            @endforeach
          @endisset
        </div>
      
        <!-- Right Block -->
        <div class="right-block absolute hidden sm:block right-12 text-right">
          <p class="text-xs text-gray-500 truncate">Created: {{ $user->created_at->diffForHumans() }}</p>
          <p class="text-xs text-gray-900 truncate">Updated: {{ $user->updated_at->diffForHumans() }}</p>
        </div>

      </a>
      <!-- Options / Context Menu Button -->
        <div class="absolute right-0 top-0">
          <div class="relative">
            <button onclick="toggleContextMenu(event)"
            class="flex items-center justify-center bg-transparent rounded-full p-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-500" viewBox="0 -960 960 960" fill="currentColor">
                <path d="M480-160q-33 0-56.5-23.5T400-240q0-33 23.5-56.5T480-320q33 0 56.5 23.5T560-240q0 33-23.5 56.5T480-160Zm0-240q-33 0-56.5-23.5T400-480q0-33 23.5-56.5T480-560q33 0 56.5 23.5T560-480q0 33-23.5 56.5T480-400Zm0-240q-33 0-56.5-23.5T400-720q0-33 23.5-56.5T480-800q33 0 56.5 23.5T560-720q0 33-23.5 56.5T480-640Z"/>
              </svg>
            </button>
            <div class="absolute hidden bg-white shadow-md rounded-md py-2 text-sm text-gray-700 top-0 right-0 z-30 w-auto context-menu">
              <a href="{{ route('users.show', $user->id) }}" class="block px-4 py-2 hover:bg-gray-100 whitespace-nowrap">View / Edit Profile</a>
              <!-- Open Sub-context menu button-->
              <div class="relative block px-4 py-2 hover:bg-gray-100 whitespace-nowrap" onclick="toggleSubMenu(event)">
                Add to Group
                <!-- Sub-context menu -->
                <div data-user-id="{{ $user->id }}" class="absolute hidden bg-white shadow-md rounded-md py-2 text-sm text-gray-700 top-0 right-full w-auto sub-context-menu">
                  <!-- Create New Group -->
                  <div class="flex items-center px-4 py-2">
                    <input
                        type="text"
                        id="new-group-name-{{ $user->id }}"

                        placeholder="Create New Group"
                        class="flex-grow border rounded-md px-2 py-1 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        value=""
                    />
                    <button
                        onclick="createGroup(event, {{ $user->id }})"
                        class="ml-2 bg-blue-500 text-white px-2 py-1 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                    <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#fff"><path d="M200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h560q33 0 56.5 23.5T840-760v80h-80v-80H200v560h560v-80h80v80q0 33-23.5 56.5T760-120H200Zm480-160-56-56 103-104H360v-80h367L624-624l56-56 200 200-200 200Z"/></svg>
                    </button>
                  </div>
                  <!-- Existing Groups -->
                  @isset($groups)
                    @foreach ($groups as $group)
                    <div data-group-id="{{ $group->id }}" class="group-{{ $group->id }} relative block px-4 py-2 whitespace-nowrap {{ in_array($group->id, $user->groupIds) ? 'bg-green-100' : 'bg-white-100' }}" onclick="addUserToGroup(event, {{ $group->id }}, {{ $user->id }}, '{{ addslashes($group->name) }}')">
                      {{ $group->name }}
                      <div class="absolute right-6 top-0 bottom-0 flex items-center remove-user-from-group" style="{{ in_array($group->id, $user->groupIds) ? 'display: flex;' : 'display: none;' }}">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                height="20px"
                                viewBox="0 -960 960 960"
                                width="20px"
                                fill="#5D0E07"
                                onclick="removeUserFromGroup(event, {{ $group->id }}, {{ $user->id }})"
                                class="cursor-pointer hover:fill-red-600"
                            >
                                <path d="M288-444h384v-72H288v72ZM480.28-96Q401-96 331-126t-122.5-82.5Q156-261 126-330.96t-30-149.5Q96-560 126-629.5q30-69.5 82.5-122T330.96-834q69.96-30 149.5-30t149.04 30q69.5 30 122 82.5T834-629.28q30 69.73 30 149Q864-401 834-331t-82.5 122.5Q699-156 629.28-126q-69.73 30-149 30Zm-.28-72q130 0 221-91t91-221q0-130-91-221t-221-91q-130 0-221 91t-91 221q0 130 91 221t221 91Zm0-312Z" />
                            </svg>
                      </div>
                    </div>
                    @endforeach
                  @endisset
                </div>

              </div>
              <a href="#" class="block px-4 py-2 hover:bg-gray-100 whitespace-nowrap">Delete</a>
            </div>
          </div>
        </div>

        @endforeach
    </li>
</ul>
<!-- Pagination Links -->
<div class="mt-4">
  {{ $users->withQueryString()->links() }}
</div>