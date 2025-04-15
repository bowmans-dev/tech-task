<ul role="list" class="divide-y h-full divide-gray-100 p-3">
    @foreach ($users as $user)
    <li class="rounded-full flex flex-row p-1 h-10 mb-3 cursor-pointer hover:bg-gray-200"> <!-- Make it relative for children -->
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
        <div class="left-1/2 pl-24 w-40">
            <p class="text-xs text-gray-500 w-20 truncate">{{ $user->country ?? 'N/A' }}</p>
            <p class="text-xs text-gray-500 no-wrap w-40">{{ $user->phone ?? 'N/A' }}</p>
        </div>
      
        <!-- Right Block -->
        <div class="hidden sm:block right-2 w-full text-right ">
          <p class="text-xs text-gray-500 truncate">Created: {{ $user->created_at->diffForHumans() }}</p>
          <p class="text-xs text-gray-900 truncate">Updated: {{ $user->updated_at->diffForHumans() }}</p>
        </div>
      </a>
    </li>
    @endforeach
</ul>
<!-- Pagination Links -->
<div class="mt-4">
  {{ $users->withQueryString()->links() }}
</div>
