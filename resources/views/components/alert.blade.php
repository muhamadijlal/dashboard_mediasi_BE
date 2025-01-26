<div id="modal-card" class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
        <div class="sm:flex sm:items-start">
            <div class="mx-auto flex size-12 shrink-0 items-center justify-center rounded-full bg-blue-100 sm:mx-0 sm:size-10">
                <svg class="size-6 text-blue-950" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                </svg>
            </div>
            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                <h3 class="text-base font-semibold text-gray-900" id="modal-title">{{ __($title) }}</h3>
                <div class="mt-2">
                    <p class="text-sm text-gray-500">{{ __(key: $message) }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
        <button onclick="Swal.clickConfirm()" type="button" class="inline-flex w-full justify-center rounded-lg bg-blue-950 px-3 py-2 text-sm font-semibold hover:bg-blue-950 hover:text-yellow-400 hover:ring-yellow-400 hover:ring-2 text-white shadow-xs sm:ml-3 sm:w-auto">
            {{ __($actionText) }}
        </button>
        <button onclick="Swal.close()" type="button" class="inline-flex w-full justify-center rounded-lg bg-white px-3 py-2 text-sm font-semibold text-blue-950 ring-2 ring-blue-950 shadow-xs sm:ml-3 sm:w-auto hover:ring-yellow-400 hover:bg-blue-950 hover:text-yellow-400">
             {{ __(key: "Cancel") }}
        </button>
    </div>
</div>