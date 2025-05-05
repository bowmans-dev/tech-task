<div class="message" id="message-{{ $message->id }}">
    <div class="w-full text-left flex flex-row align-center">
        <img 
          class="rounded-full bg-gray-50 h-8 w-8 left-1 mr-4 flex-shrink-0 object-cover" 
          src="{{ $message->sender->profile_picture ? asset('storage/' . $message->sender->profile_picture) : asset('storage/default_profile_image.png') }}" 
          alt="{{ $message->sender->first_name }} {{ $message->sender->last_name }}'s profile picture" />
        <p>{{ $message->sender->first_name }} {{ $message->sender->last_name }}:</p>
    </div>
    <p class="text-left text-black mb-4">{{ $message->content }}</p>
</div>