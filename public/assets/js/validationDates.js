function checkDateRange(startDate, endDate) {
    // Mendapatkan tanggal sekarang
    var currentDate = new Date();

    // Mengonversi start_date dan end_date menjadi objek Date
    var startDateObj = new Date(startDate);
    var endDateObj = new Date(endDate);

    // Cek apakah startDateObj atau endDateObj menghasilkan NaN
    if (isNaN(startDateObj) || isNaN(endDateObj)) {
        console.error("Invalid date format.");
        return false;
    }

    // Hitung selisih hari antara tanggal sekarang dan start_date
    var diffStart = Math.abs(currentDate - startDateObj) / (1000 * 3600 * 24); // Menghitung selisih hari
    var diffEnd = Math.abs(currentDate - endDateObj) / (1000 * 3600 * 24); // Menghitung selisih hari

    // Memeriksa apakah selisihnya lebih dari 30 hari
    if (diffStart > 30 || diffEnd > 30) {
        return true;
    } else {
        return false;
    }
}


const handleValidationDate = () => {
    const startDate = $("#start_date");
    const endDate = $("#end_date");
    const currentDate = new Date().toISOString().split('T')[0];

    const isMaxOfRange = checkDateRange(startDate.val(), endDate.val());

    if(isMaxOfRange){
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
                                    <p class="text-sm text-gray-500">The date must not be outside the 30-day range from today</p>
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

        startDate.val(currentDate);
        endDate.val(currentDate);
    }
}

// Handle change
$('#start_date, #end_date').on('change', handleValidationDate);