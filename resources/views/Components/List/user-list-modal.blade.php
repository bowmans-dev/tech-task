<ul class="pt-1 space-y-1" id="user-list-modal">
    @foreach($users as $user)
        <li class="flex items-center gap-x-4 py-2 px-2.5">
            <a onclick="clickAddUserToDropZone({{ json_encode([
                    'userId' => $user->id,
                    'profilePicture' => $user->profile_picture ? '/storage/' . $user->profile_picture : '/storage/default_profile_image.webp',
                    'firstName' => $user->first_name,
                    'lastName' => $user->last_name
                ]) }})" 
                data-action="clickAddUserToDropZone"
                class="user-link cursor-pointer flex items-center w-full" 
                draggable="true" 
                data-user-id="{{ $user->id }}" 
                data-profile-picture="./storage/{{ $user->profile_picture }}" 
                data-first-name="{{ $user->first_name }}" 
                data-last-name="{{ $user->last_name }}">
                <img src="{{ $user->profile_picture ? '/storage/' . $user->profile_picture : '/storage/default_profile_image.webp' }}" 
                    alt="{{ $user->first_name ?? $user->email }}" 
                    class="w-8 h-8 rounded-full flex-shrink-0 object-cover" loading="lazy">
                <span 
                    class="ml-2 text-sm text-gray-800 dark:text-neutral-200 truncate" 
                    style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 130px;">
                    {{ ($user->first_name && $user->last_name) ? $user->first_name . ' ' . $user->last_name : $user->email }}
                </span>
            </a>
        </li>
    @endforeach
</ul>