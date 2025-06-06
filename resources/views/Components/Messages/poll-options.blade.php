@if ($message->is_poll && isset($options) && count($options))
    @php
        $totalVotes = $options->flatMap->votes->count();
    @endphp
    <div class="poll-block">
        <ul class="mt-2 space-y-1">
        @foreach ($options as $option)
            @php
                $voteCount = $option->votes->count();
                $percentage = $totalVotes > 0 ? round(($voteCount / $totalVotes) * 100) : 0;
            @endphp
            <li class="option-group">
            <button
                data-action="submitPollVote"
                class="poll-vote pl-[3px] flex flex-col align-center cursor-pointer w-full text-left px-2 py-1 rounded-md bg-white hover:bg-gray-200 hover:text-blue-600"
                data-message-id="{{ $message->id }}"
                data-option-id="{{ $option->id }}"
                >
                <div class="flex flex-row justify-between w-full">
                <div class="flex flex-row">
                    <!-- dynamically determin whether to display a check mark for the users selected option-->
                    @if ($selectedOptionId === $option->id)
                        <div class="h-6 w-6 rounded-full border-2 border-transparent mr-2">
                            <div class="selected-option w-full h-full rounded-full bg-[#1cac62] grid items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="#fff" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        </div>
                    @else
                        <div class="h-6 w-6 rounded-full border-2 border-transparent mr-2">
                            <div class="selected-option w-full h-full rounded-full border-2 border-[#333] grid items-center">
                            </div>
                        </div>
                    @endif
                    <!-- the text for the option -->
                    <div class="text-left">
                        {{ $option->option_text }}
                    </div>
                    </div>
                    <!-- voters profile pictures stack and vote count section -->
                    <span class="text-sm text-gray-500 ml-4">
                    <div class="flex flex-row items-center">
                        <div class="flex -space-x-2">
                            @foreach($option->votes as $index => $vote)
                                @php
                                    $voter = $vote->voter;
                                    $profilePic = $voter && $voter->profile_picture
                                        ? asset('storage/' . $voter->profile_picture)
                                        : asset('storage/default_profile_image.webp');
                                    $voterName = $voter instanceof \App\Models\Admin
                                        ? '(Admin) ' . $voter->name
                                        : $voter->first_name . ' ' . $voter->last_name;
                                @endphp
                                <div class="w-4 h-4 rounded-full bg-white" style="z-index: {{ count($option->votes) - $loop->index }} ;">
                                    <img
                                    src="{{ $profilePic }}"
                                    alt="{{ $voterName }}"
                                    class="w-4 h-4 rounded-full border-1 border-white object-cover"
                                    >
                                </div>
                            @endforeach
                        </div>
                        <span class="ml-2">{{ $voteCount }}</span>
                    </div>
                </span>

                </div>
                @if ($voteCount > 0)
                    <div style="width: {{ $percentage }}%;" class="h-2 mb-2 mt-1 rounded-2xl bg-[#1cac62]"></div>
                @endif
            </button>

            <div class="voters bg-white px-2 py-1 rounded-md max-h-[0px] contain-content transition-all duration-2000">
                @foreach($option->votes as $vote)
                <div class="flex flex-row align-center text-sm text-black pt-1 pb-1">
                    @php
                        $voter = $vote->voter;
                        $profilePic = $voter && $voter->profile_picture
                            ? asset('storage/' . $voter->profile_picture)
                            : asset('storage/default_profile_image.webp');
                        $voterName = $voter instanceof \App\Models\Admin
                            ? '(Admin) ' . $voter->name
                            : $voter->first_name . ' ' . $voter->last_name;
                    @endphp
                    <img class="rounded-full object-cover mr-2 w-6 h-6" src="{{ $profilePic }}" alt="{{ $voterName }}" width="24" height="24">
                    {{ $voterName }}
                </div>
                @endforeach
            </div>

            </li>
        @endforeach
        </ul>
@endif

<div class="text-right text-xs text-gray-500 pr-2 mt-1">
    {{ $message->created_at->format('H:i') }}
</div>

@if ($message->is_poll && isset($options) && count($options))

    @php
        $totalVotes = $options->flatMap->votes->count();
    @endphp

    @if ($totalVotes > 0)
        <button class="cursor-pointer w-full pb-3 pt-1 grid items-center font-bold text-[#1cac62]" 
        onclick="event.preventDefault(); event.stopPropagation(); let btn = this; btn.closest('.bubble').querySelectorAll('.voters').forEach(el => el.style.maxHeight = el.style.maxHeight === '500px' ? '0px' : '500px'); setTimeout(() => { btn.innerText = btn.innerText === 'View votes' ? 'Hide votes' : 'View votes'; }, 500);">
            View votes
        </button>
    @endif
    
    </div> <!-- end of <div class="poll-block"> -->
@endif