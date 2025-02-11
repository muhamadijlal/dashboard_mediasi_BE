const path = window.location.pathname;
const segments = path.split('/');
const type = segments[1];


// Ambil nilai ruas_id dan gerbang_id hanya sekali, di luar event listener
const getRuasAndGerbangValues = () => {
    return {
        ruas_id: $("#ruas_id").val(),
        gerbang_id: $("#gerbang_id").val()
    };
};

$("#gerbang_id").on("change", function() {
    const { ruas_id, gerbang_id } = getRuasAndGerbangValues(); // Dapatkan nilai terbaru dari select
    const buttonFilter = $("#btnFilter");

    // Validasi jika nilai ruas_id dan gerbang_id tidak null
    if (ruas_id && gerbang_id) {
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "/ipcheck",
            type: 'POST',
            dataType: 'json',
            data: {
                ruas_id,
                gerbang_id,
                type
            },
            beforeSend: function() {
                Swal.fire({
                    html: `<div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all max-w-full inline-block">
                        <div class="bg-white p-4">
                            <div class="flex items-center gap-2">
                                <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-blue-100">
                                    <i class="text-blue-950 animate-spin fa-solid fa-rotate-right"></i>
                                </div>
                                <h3 class="text-base font-semibold text-blue-950">Checking ip connection...</h3>
                            </div>
                        </div>   
                    </div>`,
                    showConfirmButton: false,
                    showCancelButton: false,
                    allowOutsideClick: false,
                    customClass: {
                        popup: 'hide-bg-swal',
                    }
                });
            },
            success: function(response) {
                const message = response?.message;

                if(response.success){
                    buttonFilter.attr("disabled", false);

                    Swal.fire({
                        html: `<div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all max-w-full inline-block">
                                <div class="bg-white p-4">
                                    <div class="flex items-center gap-2">
                                        <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-green-100 p-4">
                                            <i class="text-green-700 fa-solid fa-tower-broadcast"></i>
                                        </div>
                                        <h3 class="text-base font-semibold text-blue-950">Connected!</h3>
                                    </div>
                                </div>   
                            </div>`,
                        showConfirmButton: false,
                        showCancelButton: false,
                        timer: 1000, 
                        customClass: {
                            popup: 'hide-bg-swal',
                        }
                    });
                } else {
                    Swal.fire({
                        html: `
                        <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all max-w-full inline-block">
                            <div class="bg-white p-4">
                                <div class="flex items-center sm:items-start gap-2">
                                    <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-green-100 p-4">
                                        <div class="mx-auto flex size-12 shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:size-10">
                                            <svg class="size-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                            </svg>
                                        </div>
                                    </div>

                                    <h3 class="text-base ps-2 font-semibold text-blue-950 mt-1">${message}</h3>
                                </div>
                            </div>   
                        </div>
                        `,
                        showConfirmButton: false,
                        showCancelButton: false,
                        timer: 1000, 
                        customClass: {
                            popup: 'hide-bg-swal',
                        }
                    });
                }
            },
            error: function(response) {

                const message = response?.message

                Swal.fire({
                    html: `<div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="flex items-center sm:items-start">
                                <div class="mx-auto flex size-12 shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:size-10">
                                    <svg class="size-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                    <h3 class="text-base text-blue-950 font-semibold ">Error!</h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500">${message ?? 'Something when error!'}</p>
                                    </div>
                                </div>
                            </div>
                        </div>   
                    </div>`,
                    showConfirmButton: false,
                    showCancelButton: false,
                    customClass: {
                        popup: 'hide-bg-swal',
                    }
                });
            }
        });
    }
});