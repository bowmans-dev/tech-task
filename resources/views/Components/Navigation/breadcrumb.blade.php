<div class="sticky top-0 inset-x-0 z-20 bg-white border-y border-gray-200 px-4  dark:bg-neutral-800 dark:border-neutral-700">
    <div class="flex items-center py-2">
        <!-- Navigation Toggle -->
        <button 
            type="button" 
            class="rotate-180 size-8 flex justify-center items-center gap-x-2 border border-gray-200 text-gray-800 hover:text-gray-500 rounded-lg focus:outline-hidden focus:text-gray-500 disabled:opacity-50 disabled:pointer-events-none dark:border-neutral-700 dark:text-neutral-200 dark:hover:text-neutral-500 dark:focus:text-neutral-500" 
            onclick="toggleSidebar(this)">
            <span class="sr-only">Toggle Navigation</span>
            <svg 
                class="shrink-0 size-4 transform" 
                xmlns="http://www.w3.org/2000/svg" 
                width="24" 
                height="24" 
                viewBox="0 0 24 24" 
                fill="none" 
                stroke="currentColor" 
                stroke-width="2" 
                stroke-linecap="round" 
                stroke-linejoin="round">
                <rect width="18" height="18" x="3" y="3" rx="2"/>
                <path d="M15 3v18"/>
                <path d="m8 9 3 3-3 3"/>
            </svg>
        </button>
        <!-- End Navigation Toggle -->

        <!-- Breadcrumb -->
        <ol class="ms-3 flex items-center whitespace-nowrap">
            @foreach ($breadcrumbs as $breadcrumb)
                <li class="flex items-center text-sm {{ $loop->last ? 'font-semibold text-gray-800 truncate' : 'text-gray-800 dark:text-neutral-400' }}">
                    @if (!$loop->last)
                        <a href="{{ Auth::guard('web')->check() ? '/profile' : $breadcrumb['url'] }}" class="hover:underline">{{ Auth::guard('web')->check() ? 'Profile' : $breadcrumb['name'] }}</a>
                        <svg class="shrink-0 mx-3 overflow-visible size-2.5 text-gray-400 dark:text-neutral-500" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M5 1L10.6869 7.16086C10.8637 7.35239 10.8637 7.64761 10.6869 7.83914L5 14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    @else
                        {{ $breadcrumb['name'] }}
                    @endif
                </li>
            @endforeach
        </ol>
        <!-- End Breadcrumb -->

        <div class="absolute flex right-0">
            <!-- Users Button -->
            <a href="{{ Auth::guard('admin')->check() ? '/users' : (Auth::guard('web')->check() ? '/profile' : '#') }}" 
                class="mr-2 size-8 flex justify-center items-center gap-x-2 border 
                {{ request()->is('users') || request()->is('users/*') ? 'border-black' : 'border-gray-200' }} 
                text-black dark:text-white hover:text-gray-300 rounded-lg focus:outline-hidden focus:text-gray-300 disabled:opacity-50 disabled:pointer-events-none">
                <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                    <circle cx="10" cy="7" r="4" />
                </svg>
            </a>
        
            @auth('admin') <!-- Check if the user is authenticated as an admin -->
                <!-- Create Button -->
                <a href="/create" class="mr-2 size-8 flex justify-center items-center gap-x-2 border {{ request()->is('create') ? 'border-black' : 'border-gray-200' }} 
                text-black dark:text-white hover:text-gray-300 rounded-lg focus:outline-hidden focus:text-gray-300 disabled:opacity-50 disabled:pointer-events-none">
                    <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19" />
                        <line x1="5" y1="12" x2="19" y2="12" />
                    </svg>
                </a>
            @endauth
        
            <!-- Log Out Button -->
            <form method="POST" action="{{ route('logout') }}" class="mr-2 size-8 flex justify-center items-center gap-x-2 border {{ request()->is('logout') ? 'border-black' : 'border-gray-200' }} 
            text-black dark:text-white hover:text-gray-300 rounded-lg focus:outline-hidden focus:text-gray-300 disabled:opacity-50 disabled:pointer-events-none">
                @csrf
                <button type="submit" class="flex items-center gap-x-2">
                    <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21V19M16 3V5M12 12H21" />
                        <path d="M5 16L3 12L5 8M3 12H12" />
                    </svg>
                </button>
            </form>
        
            
        </div>
    </div>
</div>