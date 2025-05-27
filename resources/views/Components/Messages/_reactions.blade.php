@if ($message->reactions->count())
  @php
    $reactions = $message->reactions->groupBy('emoji');
    $isSender = $message->user_id === auth()->id();
  @endphp
  <div class="reaction-block flex gap-1 absolute bottom-[-10px] left-0 z-40 cursor-default {{ $isSender ? 'justify-end' : 'justify-start' }}">
    @foreach ($reactions as $emoji => $users)
      <div class="relative group z-50">
        <div class="px-2 py-1 rounded-full bg-gray-100 text-sm">
          {{ $emoji }} {{ $users->count() }}
        </div>
        <div class="absolute top-full mb-1 hidden group-hover:block bg-black text-white text-xs rounded px-2 py-1 whitespace-nowrap z-50">
          {{ $users->map(fn($reaction) => $reaction->user_type === 'App\\Models\\Admin' ? '(Admin) ' . $reaction->user->name : ($reaction->user->first_name))->join(', ') }}
        </div>
      </div>
    @endforeach
  </div>
@endif