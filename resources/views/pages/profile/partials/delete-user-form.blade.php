<x-slot name="script">
    <script>
        function handleSubmit(e)
        {
            e.preventDefault();
            Swal.fire({
                html: `<x-alert-delete-user
                            title="Attention!"
                            message="Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account."
                            actionText="Delete this user"
                        />`,
                showConfirmButton: false,
                showCancelButton: false,
                customClass: {
                    popup: 'hide-bg-swal',
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route("profile.delete_account") }}',
                        method: "DELETE",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        beforeSend: function() {
                            Swal.fire({
                                html: `<x-alert-loading />`,
                                showConfirmButton: false,
                                showCancelButton: false,
                                allowOutsideClick: false,
                                customClass: {
                                    popup: 'hide-bg-swal',
                                }
                            })
                        },
                        success: function(response) {
                            Swal.fire({
                                html: `<x-alert-success
                                        title="Success!"
                                        message="Account deleted success!"
                                    />`,
                                showConfirmButton: false,
                                showCancelButton: false,
                                timer: 1500,
                                customClass: {
                                    popup: 'hide-bg-swal',
                                }
                            });
                            window.location.href = "/";
                        },
                        error: function(xhr, status, error) {
                            Swal.fire({
                                html: `<x-alert-error
                                    title="${status.toUpperCase()}!"
                                    message="${error}!"
                                />`,
                                showConfirmButton: false,
                                showCancelButton: false,
                                customClass: {
                                    popup: 'hide-bg-swal',
                                }
                            });
                        }
                    })
                }
            });
        }
    </script>
</x-slot>

<section>
    <div class="max-w-xl">
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                {{ __('Delete Account') }}
            </h2>
    
            <p class="mt-1 text-sm text-gray-600">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
            </p>
        </header>

        <form action="#" class="mt-6 space-y-6" onsubmit="handleSubmit(event)">
            <x-button class="bg-red-500 text-white">
                Delete user
            </x-button>
        </form>
    </div>
</section>