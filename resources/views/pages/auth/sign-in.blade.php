<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full">

<div class="flex min-h-full flex-col justify-center px-6 py-12 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-sm">
      <img class="mx-auto h-10 w-auto" src="https://tailwindcss.com/plus-assets/img/logos/mark.svg?color=indigo&shade=600" alt="Your Company">
      <h2 class="mt-10 text-center text-2xl/9 font-bold tracking-tight text-gray-900">Sign in to your account</h2>
    </div>
  
    <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
      <form class="space-y-6" action="{{ route('login') }}" method="POST">
        @csrf
        <div>
          <label for="email" class="block text-sm/6 font-medium text-gray-900">Email address</label>
          <div class="mt-2">
            <input type="email" name="email" id="email" autocomplete="email" required class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
          </div>
        </div>
  
        <div>
          <div class="flex items-center justify-between">
            <label for="password" class="block text-sm/6 font-medium text-gray-900">Password</label>
            <div class="text-sm">
              <a href="javascript:void(0);" onclick="sendPasswordResetLink()" class="font-semibold text-indigo-600 hover:text-indigo-500">Forgot password?</a>
            </div>
          </div>
          <div class="mt-2">
            <input type="password" name="password" id="password" autocomplete="current-password" required class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
            @error('password')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
            <!-- Check for authentication errors -->
              @if(session('auth.failed'))
                <span class="text-red-600 text-sm">{{ session('auth.failed') }}</span>
            @endif

          </div>
        </div>
  
        <div class="mt-2">
          <button type="submit" class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm/6 font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Sign in</button>
        </div>
      </form>
      <form id="password-reset-form" method="POST" action="{{ route('password.email') }}" style="display: none;">
        @csrf
        <input type="hidden" name="email" id="reset-email">
      </form>
  
      <p class="mt-10 text-center text-sm/6 text-gray-500">
        Not a member?
        <a href="/register" class="font-semibold text-indigo-600 hover:text-indigo-500">Sign Up</a>
      </p>
    </div>
  </div>
  <script>
    // document.addEventListener('DOMContentLoaded', () => {
      function sendPasswordResetLink() {
          // Prompt the user for their email
          const email = prompt("Please enter your email address to reset your password:");

          if (email) {
              // Populate the hidden form's email field
              document.getElementById('reset-email').value = email;

              // Submit the form
              document.getElementById('password-reset-form').submit();

              // Display an alert to confirm the action
              alert("If the email exists in our system, a password reset link has been sent.");
          } else {
              alert("Password reset process was canceled.");
          }
      }
    // });
  </script>
</body>
</html>
