@extends('layouts.app') <!-- Extend the layout -->

@section('title', 'Create User') <!-- Set the page title -->



@section('content')

  <x-navigation.breadcrumb :breadcrumbs="[ ['name' => 'Users', 'url' => route('users.index')], ['name' => 'Create User', 'url' => route('users.index')]]" />
    
    <main class="container mx-auto py-8">
      <div class="bg-gray-100">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <div class="mx-auto max-w-2xl">
    
            <form method="POST" action="{{ route('users.store') }}" enctype="multipart/form-data">
              @csrf
              <div class="space-y-12 pt-10">
    
                <div class="border-b border-gray-900/10 pb-12">
                  
                  <div class="grid grid-cols-1 lg:grid-cols-2 gap-x-6 gap-y-8">
                    <!-- Heading -->
                    <h2 class="lg:col-span-2 text-base/7 font-semibold text-gray-900 text-center">Create New User</h2>
    
                    <!-- Left Column -->
                    <div class="lg:col-span-1 lg:col-start-1">
                      <x-input.form-input class="h-8" type="text" name="first_name" id="firstName" label="First Name" value="{{ old('first_name') }}" autocomplete="given-name" required />
                      @error('first_name')
                          <span class="text-red-600 text-sm">{{ $message }}</span>
                      @enderror
                      <br>

                      <x-input.form-input class="h-8" type="text" name="last_name" id="lastName" label="Last Name" value="{{ old('last_name') }}" autocomplete="family-name" required />
                      @error('last_name')
                          <span class="text-red-600 text-sm">{{ $message }}</span>
                      @enderror
                      <br>

                      <x-input.form-input class="h-8" type="email" name="email" id="email" label="Email" value="{{ old('email') }}" autocomplete="email" required />
                      @error('email')
                          <span class="text-red-600 text-sm">{{ $message }}</span>
                      @enderror
                      <br>

                      <x-input.upload-photo label="Upload Profile Picture" id="profilePicture" name="profile_picture" value="{{ old('profile_picture') }}"
                        dragText="or drag and drop your file here" fileNameId="profile-picture-name" filePreviewId="profile-picture-preview">
                        Upload a file
                      </x-input.upload-photo>
                      @error('profile_picture')
                          <span class="text-red-600 text-sm">{{ $message }}</span>
                      @enderror
                    </div>
    
                    <!-- Right Column -->
                    <div class="lg:col-span-1 lg:col-start-2">
                      <x-input.form-input class="h-8" type="tel" name="phone" id="phone" label="Phone Number" value="{{ old('phone') }}" pattern="[\+]?[\d\s\-]+" required />
                      @error('phone')
                          <span class="text-red-600 text-sm">{{ $message }}</span>
                      @enderror
                      <br>

                      <x-input.select-dropdown class="h-8" name="country" id="country" label="Country" :options="array_combine(config('countries'), config('countries'))" :selected="old('country')"
                        placeholder="Select a country" required />
                      @error('country')
                           <span class="text-red-600 text-sm">{{ $message }}</span>
                       @enderror
                      <br>

                      <x-input.select-dropdown  class="h-8" name="gender" id="gender" label="Gender" value="{{ old('gender') }}" :options="['male' => 'Male', 'female' => 'Female', 'other' => 'Other']" :selected="old('gender')"
                        placeholder="Select your gender" required />
                      @error('gender')
                        <span class="text-red-600 text-sm">{{ $message }}</span>
                      @enderror
                      <br>

                      <x-input.form-input class="h-8" type="password" name="password" id="password" label="Password" autocomplete="new-password" required />
                      @error('password')
                          <span class="text-red-600 text-sm">{{ $message }}</span>
                      @enderror
                      <br>

                      <x-input.form-input class="h-8" type="password" name="password_confirmation" id="repeatPassword" label="Repeat Password" autocomplete="new-password" required />
                      @error('password_confirmation')
                          <span class="text-red-600 text-sm">{{ $message }}</span>
                      @enderror

                      <!-- Submit Button -->
                      <div class="mt-6 lg:col-span-2">
                        <button type="submit"
                          class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm/6 font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                          Create Account
                        </button>
                      </div>
                    </div>
    
                  </div>
                  
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </main>
@endsection