@foreach($messages as $message)
<div class="message relative cursor-pointer" id="message-{{ $message->id }}" data-message-id="{{ $message->id }}">
    <div class="w-full text-left flex flex-row align-center">
        <img 
          class="rounded-full bg-gray-50 h-8 w-8 left-1 mr-2 flex-shrink-0 object-cover" 
          src="{{ $message->sender->profile_picture ? asset('storage/' . $message->sender->profile_picture) : asset('storage/default_profile_image.png') }}" 
          alt="{{ $message->sender->first_name }} {{ $message->sender->last_name }}'s profile picture" />
        <div class="flex items-center">{{ $message->sender->first_name }} {{ $message->sender->last_name }}:</div>
    </div>
    <p class="text-left text-black mt-2">{{ $message->content }}</p>
    <div class="text-right text-xs text-gray-500">
      {{ $message->created_at->format('H:i') }}
    </div>
</div>
@endforeach