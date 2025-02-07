<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ str_replace("_"," ", config('app.name')) }} | {{ config('app.version') }}</title>
  <link rel="icon" href="{{asset("assets/image/logojm.ico")}}">
  <style>
    @media (prefers-color-scheme: dark) {
        .dark\:bg-gray-800 {
            --tw-bg-opacity: 1 !important;
            background-color: rgb(255, 255, 255) !important;
        }

        .dark\:bg-gray-700\/50 {
            background-color: rgb(244, 245, 246) !important;
        }

       
        .dark\:border-gray-600 {
            --tw-border-opacity: 1 !important;
            border-color: rgb(209 213 219) !important;
        }

        .dark\:border-gray-700 {
            --tw-border-opacity: 1 !important;
            border-color: rgb(209 213 219) !important;
        }

        .dark\:border-gray-700\/50 {
            border-color: rgb(255 255 255) !important;
        }

        .dark\:text-gray-50 {
            --tw-text-opacity: 1 !important;
            color: rgb(17, 24, 39) !important;
        }

        .dark\:even\:bg-gray-900\/50:nth-child(even) {
            background-color: rgb(244 245 246) !important;
        }

        .dark\:bg-gray-700\/25 {
            background-color: rgb(255 255 255) !important;
        }

        /* .dark\:text-gray-600 {
            --tw-text-opacity: 1 !important;
            color: rgb(17, 24, 39) !important;
        }

        .dark\:text-gray-300 {
            --tw-text-opacity: 1 !important;
            opacity: 0.5 !important;
            color: rgb(17, 24, 39) !important;
        }

        .dark\:bg-gray-700\/75 {
            --tw-text-opacity: 1 !important;
            opacity: 0.5 !important;
            background-color: rgb(17, 24, 39) !important;
        } */
    }

  </style>
  <!-- Fonts -->
  <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link href="https://cdn.datatables.net/2.2.2/css/dataTables.tailwindcss.css" rel="stylesheet" />

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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- fontawesome -->
    <script src="https://kit.fontawesome.com/aa873de9f0.js" crossorigin="anonymous"></script>
    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Datatables -->
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.tailwindcss.js"></script>
    <script type="text/javascript" src="https://cdn.tailwindcss.com" defer></script>
    <script>
        const loading = (isActive) => {
            if (isActive) {
                $("#loading").removeClass("hidden"); // Show the spinner
            } else {
                $("#loading").addClass("hidden"); // Hide the spinner
            }
        };
    </script>
    @isset($script)
     {{ $script }}
    @endisset
</body>
</html>