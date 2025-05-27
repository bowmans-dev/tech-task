  @php
    $reactions = $message->reactions->groupBy('emoji');
  @endphp
<div class="message-wrapper w-full flex relative {{ $isSender ? 'justify-end' : 'justify-start' }}" id="message-{{ $message->id }}" data-message-id="{{ $message->id }}">
  <div class="message bubble text-left w-[200px] p-2 mb-4 rounded-2xl relative shadow-md cursor-pointer transition-all duration-200 hover:mb-10 {{ $isSender ? 'bg-[#d9fdd3]' : 'bg-[#ffffff]' }}">
    <div class="flex flex-row align-center">
        <img 
            class="rounded-full bg-gray-50 h-8 w-8 left-1 mr-2 flex-shrink-0 object-cover" 
            src="{{ $profilePicture }}" 
            alt="{{ $displayName }}'s profile picture" />
        <div class="flex items-center">{{ $displayName }}:</div>
    </div>
    <p class="text-black mt-2">{{ $message->content }}</p>
    <div class="text-right text-xs text-gray-500 pr-2">
      {{ $message->created_at->format('H:i') }}
    </div>
    @include('Components.messages._reactions', ['message' => $message])
  </div>
</div>
