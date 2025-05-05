<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Default Title')</title>
    @vite(['resources/js/app.js', 'resources/css/app.css'])
</head>
<body class="bg-gray-100 h-full pt-16">

    @yield('content')
    
</body>
</html>