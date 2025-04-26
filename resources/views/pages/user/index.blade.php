@extends('layouts.app') <!-- Extend the layout -->

@section('title', 'User List') <!-- Set the page title -->



@section('content')

    <x-navigation.breadcrumb :groups="$groups" :breadcrumbs="[
        ['name' => 'Users', 'url' => route('users.index')], ['name' => '', 'url' => '']
    ]" />

    <!-- Sidebar -->
    <x-navigation.sidebar :groups="$groups" />
    
    <main class="container mx-auto py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl lg:mx-0 lg:[max-width:calc(100vw-400px)] lg:[margin-left:250px]">
                <!-- Search Input -->
                <div class="relative mb-6 m-4">
                    <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none z-20 ps-3.5">
                        <svg class="shrink-0 size-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8" />
                            <path d="m21 21-4.3-4.3" />
                        </svg>
                    </div>
                    <input 
                        type="text" 
                        id="search" 
                        class="py-2 ps-10 pe-16 block w-full bg-white border-gray-200 rounded-lg text-sm focus:outline-hidden focus:border-blue-500 focus:ring-blue-500" 
                        placeholder="Search by name or email" />
                </div>

                <!-- User List -->
                <div id="user-list">
                    @include('Components.users.user-list', ['users' => $users])
                </div>
            </div>
        </div>
    </main>
@endsection
<script>

      function adjustGroupBadgeWidth() {
  // Loop through all user rows
  const userRows = document.querySelectorAll('.user-row li');

  userRows.forEach((row) => {
    // Get the Center Block, Group Badges Container, and Right Block
    const centerBlock = row.querySelector('.center-block');
    const rightBlock = row.querySelector('.right-block');
    const badgesContainer = row.querySelector('.group-badges-container');

    if (centerBlock && rightBlock && badgesContainer) {
      // Calculate the available space between the center block and right block
      const centerBlockRect = centerBlock.getBoundingClientRect();
      const rightBlockRect = rightBlock.getBoundingClientRect();

      const availableSpace = rightBlockRect.left - centerBlockRect.right;

      // Dynamically set the width and max-width of the badges container
      const adjustedWidth = Math.max(0, availableSpace - 34); // Add padding/margin buffer if needed
      badgesContainer.style.width = `${adjustedWidth}px`;
      badgesContainer.style.maxWidth = `${adjustedWidth}px`;
    }
  });
}

// Run the function on page load and window resize
window.addEventListener('load', adjustGroupBadgeWidth);
window.addEventListener('resize', adjustGroupBadgeWidth);
</script>