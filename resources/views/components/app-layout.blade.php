<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ config('app.name') }} | {{ config('app.version') }}</title>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link href="https://cdn.datatables.net/2.2.1/css/dataTables.tailwindcss.css" rel="stylesheet" />

  <!-- Scripts -->
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-blue-950 antialiased">
    <div class="min-h-screen bg-gray-100">
        <!-- header navbar -->
        @include('layouts.navbar')
    
        <!-- sidebar -->
        @include('layouts.sidebar')
    
        <!-- content -->
        <div class="pt-14 md:ml-[300px] p-10">
            @isset($header)
                <header class="py-14">
                    <h1 class="text-lg md:text-2xl uppercase font-bold">{{ $header }}</h1>
                </header>
            @endisset

            {{ $slot }}
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="{{ asset('assets/js/myJs.js') }}"></script>
    <!-- fontawesome -->
    <script src="https://kit.fontawesome.com/aa873de9f0.js" crossorigin="anonymous"></script>
    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Datatables -->
    <script src="https://cdn.datatables.net/2.2.1/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.2.1/js/dataTables.tailwindcss.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    @isset($script)
     {{ $script }}
    @endisset
</body>
</html>