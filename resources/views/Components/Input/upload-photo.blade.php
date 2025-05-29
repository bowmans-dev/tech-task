<div class="upload-component sm:col-span-4 sm:col-start-2">
    <label for="{{ $id ?? 'file-upload' }}" class="block text-sm/6 font-medium text-gray-900">
        {{ $label ?? 'Profile Picture' }}
    </label>
    <div class="mt-2 justify-center rounded-lg border border-dashed border-gray-900/25 px-6 py-10">
        <div class="text-center">
            <svg class="mx-auto size-12 text-gray-300" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" data-slot="icon">
                <path fill-rule="evenodd" d="M1.5 6a2.25 2.25 0 0 1 2.25-2.25h16.5A2.25 2.25 0 0 1 22.5 6v12a2.25 2.25 0 0 1-2.25 2.25H3.75A2.25 2.25 0 0 1 1.5 18V6ZM3 16.06V18c0 .414.336.75.75.75h16.5A.75.75 0 0 0 21 18v-1.94l-2.69-2.689a1.5 1.5 0 0 0-2.12 0l-.88.879.97.97a.75.75 0 1 1-1.06 1.06l-5.16-5.159a1.5 1.5 0 0 0-2.12 0L3 16.061Zm10.125-7.81a1.125 1.125 0 1 1 2.25 0 1.125 1.125 0 0 1-2.25 0Z" clip-rule="evenodd" />
            </svg>
            <div class="mt-4 sm:block text-sm/6 text-gray-600">
                <label for="{{ $id ?? 'file-upload' }}" class="mr-1 relative cursor-pointer rounded-md bg-white font-semibold text-indigo-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-indigo-600 focus-within:ring-offset-2 hover:text-indigo-500">
                    <span class="px-2">{{ $slot }}</span>
                    <input id="{{ $id ?? 'file-upload' }}" name="{{ $name ?? 'file-upload' }}" type="file" class="sr-only" {{ $attributes->merge(['accept' => 'image/*']) }}>
                </label>
                <p class="pl-1">{{ $dragText ?? 'or drag and drop' }}</p>
            </div>
            <p id="{{ $fileNameId ?? 'file-name' }}" class="file-name mt-2 text-sm text-gray-500"></p>
            <img id="{{ $filePreviewId ?? 'file-preview' }}" 
     src="{{ $currentImageUrl ?? asset('storage/default_profile_image.webp') }}" 
     alt="Profile Picture" 
     class="file-preview mt-4 {{ $currentImageUrl ? '' : 'hidden' }} rounded-md">
        </div>
    </div>
</div> 