<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ str_replace("_"," ", config('app.name')) }} | {{ config('app.version') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <!-- component -->
    <div class="min-h-screen bg-gray-100 flex flex-col gap-5 items-center justify-center p-4">
        @if($errors->has('email'))
            <div class="bg-red-100 border-l-2 border-red-500 p-4 text-red-400 w-1/2">
                <h4 class="text-lg font-bold">Error!</h4>
                <p>{{ $errors->first('email') }}</p>
            </div>
        @endif

        <div class="max-w-md w-full bg-white rounded-xl shadow-lg p-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">{{ __("Login") }}</h2>
            
            <form class="space-y-4" method="POST" action="{{ route('auth.login') }}">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __("Email") }}</label>
                    <input 
                        type="email" 
                        name="email"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-950 focus:border-blue-950 outline-none transition-all"
                        placeholder={{__("your@email.com")}}
                    />
                </div>
        
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __("Password") }}</label>
                    <input 
                        type="password" 
                        name="password"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-950 focus:border-blue-950 outline-none transition-all"
                        placeholder="••••••••"
                    />
                </div>
        
                <button class="w-full bg-blue-950 hover:ring-2 hover:ring-yellow-400 text-yellow-400 font-medium py-2.5 rounded-lg transition-colors">
                    {{__("Login")}}
                </button>
            </form>
        </div>
    </div>
</body>
</html>