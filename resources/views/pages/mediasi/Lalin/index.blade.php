<x-app-layout>
    <x-slot name="header">
        {{ __("Data Lalin Gerbang Utama") }}
    </x-slot>

    <x-slot name="script">
        <script src="{{asset("assets/js/ipcheck.js")}}"></script>
        <script>
            let tblLalinUtamaEntrance;
            let tblLalinUtamaExit;
            const btnFilter = $("#btnFilter");

            $(document).ready(function() {
                let columns = @json($columns);

                tblLalinUtamaEntrance = $('#tblLalinUtamaEntrance').DataTable({
                    ajax: {
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: "{{ route('mediasi.lalin_gerbang_utama.getDataEntrance') }}",
                        type: 'POST',
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
                        data: function (d) {
                            d.start_date = $('#start_date').val();
                            d.end_date = $('#end_date').val();
                        },
                        error: function (response) {
                            Swal.fire({
                                html: `<x-alert-error
                                        title="Error!"
                                        message="${response.responseJSON.message}!"
                                />`,
                                showConfirmButton: false,
                                showCancelButton: false,
                                customClass: {
                                    popup: 'hide-bg-swal',
                                }
                            });
                        },
                        xhr: function() {
                            // Anda bisa menambahkan tambahan penanganan di sini, jika perlu
                            var xhr = new XMLHttpRequest();
                            xhr.onreadystatechange = function() {
                                if (xhr.readyState === 4 && xhr.status === 200) {
                                    // Proses bisa dilanjutkan jika data sudah selesai
                                    // Swal.close() akan dipanggil setelah request selesai
                                    Swal.close();
                                }
                            };
                            return xhr;
                        }
                    },
                    columns: columns,
                    serverSide: true,
                    processing: true,
                    scrollX: true,
                    scrollCollapse: true,
                    orderCellsTop: true,
                    lengthMenu: [
                        [10, 25, 50, -1],
                        ['10 rows', '25 rows', '50 rows', 'Show all']
                    ],
                    language: {
                        emptyTable: "Empty",
                    },
                    deferLoading: 0,
                });

                tblLalinUtamaExit = $('#tblLalinUtamaExit').DataTable({
                    ajax: {
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: "{{ route('mediasi.lalin_gerbang_utama.getDataExit') }}",
                        type: 'POST',
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
                        data: function (d) {
                            d.start_date = $('#start_date').val();
                            d.end_date = $('#end_date').val();
                        },
                        error: function (response) {
                            Swal.fire({
                                html: `<x-alert-error
                                        title="Error!"
                                        message="${response.responseJSON.message}!"
                                />`,
                                showConfirmButton: false,
                                showCancelButton: false,
                                customClass: {
                                    popup: 'hide-bg-swal',
                                }
                            });
                        },
                        xhr: function() {
                            // Anda bisa menambahkan tambahan penanganan di sini, jika perlu
                            var xhr = new XMLHttpRequest();
                            xhr.onreadystatechange = function() {
                                if (xhr.readyState === 4 && xhr.status === 200) {
                                    // Proses bisa dilanjutkan jika data sudah selesai
                                    // Swal.close() akan dipanggil setelah request selesai
                                    Swal.close();
                                }
                            };
                            return xhr;
                        }
                    },
                    columns: columns,
                    serverSide: true,
                    processing: true,
                    scrollX: true,
                    scrollCollapse: true,
                    orderCellsTop: true,
                    lengthMenu: [
                        [10, 25, 50, -1],
                        ['10 rows', '25 rows', '50 rows', 'Show all']
                    ],
                    language: {
                        emptyTable: "Empty",
                    },
                    deferLoading: 0,
                });
            });

            function handleSubmit(e)
            {
                e.preventDefault();
                tblLalinUtamaEntrance.draw();
                tblLalinUtamaExit.draw();
            }
        </script>
    </x-slot>

    <form class="bg-white rounded-lg shadow-md flex flex-col items-end gap-5 p-5" onsubmit="handleSubmit(event)">
        <!-- Filter Component -->
        <div class="grid grid-cols-2 gap-5 place-content-between w-full">
            <!-- Start Date -->
            <div>
                <label class="mb-2 block font-medium text-sm text-blue-950" for="start_date">{{ __("Start Date") }}</label>
                <input 
                    class="px-3 py-2 border border-gray-300 rounded-lg text-blue-950 w-full focus:ring-2 focus:ring-blue-950 focus:text-blue-950 h-10" 
                    type="date" 
                    name="start_date" 
                    id="start_date"
                    value="{{ date('Y-m-d') }}"
                >
            </div>
        
            <!-- End Date -->
            <div>
                <label class="mb-2 block font-medium text-sm text-blue-950" for="end_date">{{ __("End Date") }}</label>
                <input 
                    class="px-3 py-2 border border-gray-300 rounded-lg text-blue-950 w-full focus:ring-2 focus:ring-blue-950 focus:text-blue-950 h-10" 
                    type="date" 
                    name="end_date" 
                    id="end_date"
                    value="{{ date('Y-m-d') }}"
                >
            </div>
        </div>

        <x-button :disabled="false">
            Submit
        </x-button>
    </form>

    <h4 class="h-10"></h4>

    <div class="bg-white rounded-lg shadow-md gap-5 p-5">
        <h3 class="text-xl font-semibold">Lalin Masuk Jakarta</h3>

        <h4 class="h-10"></h4>

        <table id="tblLalinUtamaEntrance" class="display" style="width:100%">
            <thead>
                <tr>
                    @foreach($columns as $column)
                        <th class="text-center {!! $column['title'] === 'Etoll Hash' ? 'max-w-16' : '' !!}">
                            {!! $column['title'] !!}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div> 

    <h4 class="h-10"></h4>

    <div class="bg-white rounded-lg shadow-md gap-5 p-5">
        <h3 class="text-xl font-semibold">Lalin Keluar Jakarta</h3>

        <h4 class="h-10"></h4>

        <table id="tblLalinUtamaExit" class="display" style="width:100%">
            <thead>
                <tr>
                    @foreach($columns as $column)
                        <th class="text-center {!! $column['title'] === 'Etoll Hash' ? 'max-w-16' : '' !!}">
                            {!! $column['title'] !!}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div> 
</x-app-layout>
