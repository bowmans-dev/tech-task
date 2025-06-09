@if ($message->is_task_list && isset($tasks) && count($tasks))
    @php
        $totalTasks = count($tasks);
        // Ensure completedTaskIds tracks all globally completed tasks
        $completedTaskIdsGlobal = collect($taskCompletions)->keys()->toArray();
        // Calculate completed tasks and percentage based on all users' completions
        $completedTasks = count($completedTaskIdsGlobal);
        $completionPercentage = $totalTasks > 0 ? min(100, round(($completedTasks / $totalTasks) * 100)) : 0;
    @endphp
    <!-- Completion Progress Bar -->
    
    <div class="task-list-block mt-2">
        <ul class="mt-2 space-y-1">
            @if ($completedTasks > 0)
                <div class="text-green-900 m-0 p-0 text-[10px]">({{  $completedTasks }}/{{ $totalTasks }}) {{ $completionPercentage }}% </div>
                <div style="width: {{ $completionPercentage }}%;" class="h-2 mb-2 mt-1 rounded-2xl bg-[#1cac62]"></div>
            @endif
            @foreach ($tasks as $task)
                @php
                    $completedTaskIds = is_array($completedTaskIds) ? $completedTaskIds : $completedTaskIds->toArray();
                    $isCompleted = in_array($task['id'], $completedTaskIds);
                    $taskCompletionsList = $taskCompletions[$task['id']] ?? [];
                @endphp
                <li class="task-group">
                    <button
                        data-action="submitTaskCompletion"
                        class="task-complete pl-[3px] flex flex-col align-center cursor-pointer w-full text-left px-2 py-1 rounded-md bg-white hover:bg-gray-200 hover:text-blue-600"
                        data-message-id="{{ $message->id }}"
                        data-task-id="{{ $task['id'] }}"
                    >
                        <div class="flex flex-row justify-between w-full">
                            <div class="flex flex-row">
                                <!-- Checkmark for completed tasks -->
                                @if ($isCompleted)
                                    <div class="h-6 w-6 rounded-full border-2 border-transparent mr-2">
                                        <div class="selected-task w-full h-full rounded-full bg-[#1cac62] grid items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="#fff" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                    </div>
                                @else
                                    <div class="h-6 w-6 rounded-full border-2 border-transparent mr-2">
                                        <div class="selected-task w-full h-full rounded-full border-2 border-[#333] grid items-center">
                                        </div>
                                    </div>
                                @endif
                                <!-- Task text -->
                                <div class="text-left">
                                    {{ $task['text'] }}
                                </div>
                            </div>
                            <!-- Completed users -->
                            <span class="text-sm text-gray-500 ml-4">
                                <div class="flex flex-row items-center">
                                    <div class="flex -space-x-2">
                                        @foreach($taskCompletionsList as $completion)
                                            @php
                                                $worker = $completion ?? null;
                                            @endphp

                                            @if (!empty($worker))
                                                @php
                                                    $profilePic = $worker['profile_picture'] ?? asset('storage/default_profile_image.webp');
                                                    $workerName = $worker['worker_type'] === 'Admin'
                                                        ? '(Admin) ' . ($worker['name'] ?? 'Unknown')
                                                        : ($worker['name'] ?? 'Unknown');
                                                @endphp

                                                <div class="w-4 h-4 rounded-full bg-white" style="z-index: {{ count($taskCompletionsList) - $loop->index }};">
                                                    <img
                                                        src="{{ $profilePic }}"
                                                        alt="{{ $workerName }}"
                                                        class="w-4 h-4 rounded-full border-1 border-white object-cover"
                                                    >
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                    <span class="ml-2">{{ count($taskCompletionsList) }}</span>
                                </div>
                            </span>
                        </div>
                    </button>

                    <!-- Completed Users Section (Hidden Initially) -->
                    <div class="completed-users bg-white px-[5px] rounded-md max-h-[0px] contain-content transition-all duration-2000">
                        @foreach($taskCompletionsList as $completion)
                            <div class="flex flex-row align-center text-sm text-black pt-1 pb-1">
                                @php
                                    $worker = $completion ?? null;
                                    $profilePic = $worker['profile_picture'] ?? asset('storage/default_profile_image.webp');
                                    $workerName = $worker['worker_type'] === 'Admin'
                                        ? '(Admin) ' . ($worker['name'] ?? 'Unknown')
                                        : ($worker['name'] ?? 'Unknown');
                                @endphp
                                <img class="rounded-full object-cover mr-2 w-[21px] h-[21px]" src="{{ $profilePic }}" alt="{{ $workerName }}" width="21" height="21">
                                {{ $workerName }}
                            </div>
                        @endforeach
                    </div>
                </li>
            @endforeach
        </ul>

        <div class="text-right text-xs text-gray-500 pr-2 mt-1">
            {{ $message->created_at->format('H:i') }}
        </div>

        @php
            $totalCompleted = count($completedTaskIds);
        @endphp

        @if ($totalCompleted > 0)
            <button class="cursor-pointer w-full pb-3 pt-1 grid items-center font-bold text-[#1cac62]"
                onclick="event.preventDefault(); event.stopPropagation(); let btn = this; btn.closest('.bubble').querySelectorAll('.completed-users').forEach(el => el.style.maxHeight = el.style.maxHeight === '500px' ? '0px' : '500px'); setTimeout(() => { btn.innerText = btn.innerText === 'View completed' ? 'Hide completed' : 'View completed'; }, 500);">
                View completed
            </button>
        @endif
    </div>
@endif