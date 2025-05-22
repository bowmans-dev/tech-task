<div class="w-full flex {{ $isSender ? 'justify-end' : 'justify-start' }}">
  <div class="message mb-4 mt-4 text-left w-[200px] p-2 rounded-2xl shadow-md {{ $isSender ? 'bg-[#d9fdd3]' : 'bg-[#ffffff]' }}" id="message-{{ $message->id }}">
    <div class="flex flex-row align-center">
        <img 
            class="rounded-full bg-gray-50 h-8 w-8 left-1 mr-2 flex-shrink-0 object-cover" 
            src="{{ $profilePicture }}" 
            alt="{{ $displayName }}'s profile picture" />
        <p class="flex items-center">{{ $displayName }}:</p>
    </div>
    <p class="text-black mt-2 mb-4">{{ $message->content }}</p>
  </div>
</div>
