<section>
    <div class="max-w-xl">
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                {{ __('Update Password') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                {{ __('Ensure your account is using a long, random password to stay secure.') }}
            </p>
        </header>

        <form method="post" action="{{ route('profile.reset_password') }}" class="mt-6 space-y-6">
            @csrf
            @method('patch')

            <div>
                <label class="mb-2 block font-medium text-sm text-blue-950" for="current_password">{{ __("Current password") }}</label>
                <input 
                    class="px-3 py-2 border border-gray-300 rounded-lg text-blue-950 w-full max-w-md focus:ring-2 focus:ring-blue-950 focus:text-blue-950 h-10" 
                    type="password" 
                    name="current_password" 
                    placeholder="••••••••"
                >
                @if ($errors->has('current_password'))
                    <ul class='text-sm text-red-600 space-y-1'>
                        @foreach ($errors->get('current_password') as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div>
                <label class="mb-2 block font-medium text-sm text-blue-950" for="password">{{ __("New password") }}</label>
                <input 
                    class="px-3 py-2 border border-gray-300 rounded-lg text-blue-950 w-full max-w-md focus:ring-2 focus:ring-blue-950 focus:text-blue-950 h-10" 
                    type="password" 
                    name="password" 
                    placeholder="••••••••"
                >
                @if ($errors->has('password'))
                    <ul class='text-sm text-red-600 space-y-1'>
                        @foreach ($errors->get('password') as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div>
                <label class="mb-2 block font-medium text-sm text-blue-950" for="password_confirmation">{{ __("Confirm password") }}</label>
                <input 
                    class="px-3 py-2 border border-gray-300 rounded-lg text-blue-950 w-full max-w-md focus:ring-2 focus:ring-blue-950 focus:text-blue-950 h-10" 
                    type="password" 
                    name="password_confirmation" 
                    placeholder="••••••••"
                >
                @if ($errors->has('password_confirmation'))
                    <ul class='text-sm text-red-600 space-y-1'>
                        @foreach ($errors->get('password_confirmation') as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <x-button>
                Update password
            </x-button>
        </form>
    </div>
</section>
