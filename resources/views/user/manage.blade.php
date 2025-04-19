@extends('layouts.app')
<!-- Extend the layout -->

@section('title', 'Update User')
<!-- Set the page title -->



@section('content')
<x-breadcrumb :breadcrumbs="[
    ['name' => 'Users', 'url' => route('users.index')],
    ['name' => $user->first_name . ' ' . $user->last_name, 'url' => route('users.show', $user->id)],
]" />
<main class="container mx-auto py-8">
	<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
		<div class="mx-auto max-w-2xl">

			<div class="pt-10 grid grid-cols-1 lg:col-span-2 place-items-center">
				<div class="w-full grid place-items-center mb-2">
					<img class="rounded-full bg-gray-50 h-36 w-36 flex-shrink-0 object-cover"
						src="{{ $user->profile_picture ? asset('storage/' . $user->profile_picture) : asset('storage/default_profile_image.png') }}"
						alt="{{ $user->first_name }} {{ $user->last_name }}'s profile picture" />
				</div>
				<h2 class="lg:col-span-2 text-base/7 font-semibold text-gray-900 text-center mb-2">
					{{ $user->first_name }} {{ $user->last_name }}
				</h2>

				<form method="POST" action="{{ route('password.email') }}">
					@csrf
					@method('POST')
					<input type="hidden" name="email" value="{{ $user->email }}">
					<button type="submit" 
						class="rounded-md mb-6 mt-2 bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-indigo-500">
						Send Password Reset Link
					</button>
				</form>
			</div>

		

			<form method="POST" action="{{ route('users.update', $user->id) }}" enctype="multipart/form-data">
				@csrf
				@method('PATCH')

				<div class="space-y-12">
					<div class="border-b border-gray-900/10 pb-12">

						<div class="grid grid-cols-1 lg:grid-cols-2 lg:col-span-2 gap-x-6 gap-y-8">

							<!-- Left Column -->
							<div class="lg:col-span-1 lg:col-start-1">
								<x-form-input class="h-8" type="text" name="first_name" id="firstName" label="First Name" 
									:value="$user->first_name" autocomplete="given-name" />
								@error('first_name')
									<span class="text-red-600 text-sm">{{ $message }}</span>
								@enderror
								<br>
						
								<x-form-input class="h-8" type="text" name="last_name" id="lastName" label="Last Name" 
									:value="$user->last_name" autocomplete="family-name" />
								@error('last_name')
									<span class="text-red-600 text-sm">{{ $message }}</span>
								@enderror
								<br>
						
								<x-form-input class="h-8" type="email" name="email" id="email" label="Email" 
									:value="$user->email" autocomplete="email" />
								@error('email')
									<span class="text-red-600 text-sm">{{ $message }}</span>
								@enderror
								<br>
						
								<x-upload-photo label="Upload / Change Profile Picture" id="profilePicture" name="profile_picture" 
									dragText="or drag and drop your file here" fileNameId="profile-picture-name" 
									filePreviewId="profile-picture-preview">
									Upload a file
								</x-upload-photo>
								@error('profile_picture')
									<span class="text-red-600 text-sm">{{ $message }}</span>
								@enderror
							</div>
						
							<!-- Right Column -->
							<div class="lg:col-span-1 lg:col-start-2">
								<x-form-input class="h-8" type="tel" name="phone" id="phone" label="Phone Number" 
									:value="$user->phone" pattern="[\+]?[\d\s\-]+" />
								@error('phone')
									<span class="text-red-600 text-sm">{{ $message }}</span>
								@enderror
								<br>
						
								<x-select-dropdown class="h-8" name="country" id="country" label="Country" 
									:options="array_combine(config('countries'), config('countries'))" 
									:selected="$user->country" placeholder="Select a country" />
								@error('country')
									<span class="text-red-600 text-sm">{{ $message }}</span>
								@enderror
								<br>
						
								<x-select-dropdown class="h-8" name="gender" id="gender" label="Gender" 
									:options="['male' => 'Male', 'female' => 'Female', 'other' => 'Other']" 
									:selected="$user->gender" placeholder="Select your gender" />
								@error('gender')
									<span class="text-red-600 text-sm">{{ $message }}</span>
								@enderror
								<br>
						
								<x-form-input class="h-8" type="password" name="password" id="password" label="Password" />
								@error('password')
									<span class="text-red-600 text-sm">{{ $message }}</span>
								@enderror
								<br>
						
								<x-form-input class="h-8" type="password" name="password_confirmation" id="repeatPassword" 
									label="Repeat Password" />
								@error('password_confirmation')
									<span class="text-red-600 text-sm">{{ $message }}</span>
								@enderror
						
								<!-- Submit Button -->
								<div class="mt-6 lg:col-span-2">
									<button type="submit" 
										class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
										Update User
									</button>
								</div>
							</div>
						
						</div>

					</div>
				</div>
			</form>

			<div class="flex justify-end items-center gap-x-2 py-3 px-4 bg-gray-50 border-t border-gray-200">
				<button type="button"
					class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-2xs hover:bg-gray-50">
					Cancel
				</button>
				<form method="POST" action="{{ route('users.destroy', $user->id) }}" class="inline-block">
					@csrf
					@method('DELETE')
					<button type="submit"
						class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-[#ff4d4d] text-white hover:bg-red-600">
						Delete Account
					</button>
				</form>
			</div>
		</div>

	</div>
</main>
@endsection