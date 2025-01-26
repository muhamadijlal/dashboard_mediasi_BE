<x-app-layout>
    <x-slot name="header">
        {{ __("Profile Dashboard") }}
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                @include("pages.profile.partials.update-profile-information-form")
            </div>
            
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                @include("pages.profile.partials.update-password-form")
            </div>
            
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                @include("pages.profile.partials.delete-user-form")
            </div>
        </div>
    </div>

</x-app-layout>