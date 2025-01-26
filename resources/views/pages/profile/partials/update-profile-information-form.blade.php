<section>
    <div class="max-w-xl">
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                {{ __('Profile Information') }}
            </h2>
    
            <p class="mt-1 text-sm text-gray-600">
                {{ __("Update your account's profile information and email address.") }}
            </p>
        </header>

        <form method="post" action="{{ route("profile.update") }}" class="mt-6 space-y-6">
            @csrf
            @method('patch')
            <div>
                <label class="mb-2 block font-medium text-sm text-blue-950" for="name">{{ __("Name") }}</label>
                <input 
                    class="px-3 py-2 border border-gray-300 rounded-lg text-blue-950 w-full max-w-md focus:ring-2 focus:ring-blue-950 focus:text-blue-950 h-10" 
                    type="text" 
                    name="name" 
                    value="{{ $user->name }}"
                    placeholder="{{__('Please enter your name here')}}"
                >
            </div>
            <div>
                <label class="mb-2 block font-medium text-sm text-blue-950" for="email">{{ __("Email") }}</label>
                <input 
                    class="px-3 py-2 border border-gray-300 rounded-lg text-blue-950 w-full max-w-md focus:ring-2 focus:ring-blue-950 focus:text-blue-950 h-10" 
                    type="text" 
                    name="email" 
                    value="{{ $user->email }}"
                    placeholder="{{__('example@email.com')}}"
                >
            </div>

            <x-button>
                Update Profile
            </x-button>
        </form>
    </div>
</section>