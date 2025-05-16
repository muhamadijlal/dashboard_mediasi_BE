<x-app-layout>
    <x-slot name="header">
        {{ __("Transaksi Detail Mediasi Dashboard") }}
    </x-slot>

    <x-slot name="script">
        <script src="{{asset("assets/js/ipcheck.js")}}"></script>
        <script>
            let tblTransaksiDetail;
            const btnFilter = $("#btnFilter");

            $(document).ready(function() {
                const ruas_id = $('#ruas_id');
                const gerbang_id = $('#gerbang_id');
                // const gardu_id = $('#gardu_id');
                const shift_id = $('#shift_id');
                const golongan_id = $('#golongan_id');
                const metoda_bayar_id = $('#metoda_bayar_id');

                const metodaBayarOptions = [
                    { id: '*', text: 'All' },
                    { id: '40', text: 'Tunai (40)' },
                    { id: '21', text: 'Mandiri (21)' },
                    { id: '24', text: 'BCA (24)' },
                    { id: '22', text: 'BRI (22)' },
                    { id: '23', text: 'BNI (23)' },
                    { id: '25', text: 'DKI (25)' },
                    { id: '28', text: 'FLO (28)' },
                    { id: '11', text: 'JMC OPERASI (11)' },
                    { id: '12', text: 'JMC KARYAWAN (12)' },
                    { id: '13', text: 'JMC MITRA (13)' },
                ];

                // const garduOptions = [
                //     { id: '*', text: 'All' },
                //     { id: '12', text: '12' },
                //     { id: '14', text: '14' },
                //     { id: '16', text: '16' },
                //     { id: '18', text: '18' },
                // ];

                const golonganOptions = [
                    { id: '*', text: 'All' },
                    { id: '1', text: 'Golongan 1' },
                    { id: '2', text: 'Golongan 2' },
                    { id: '3', text: 'Golongan 3' },
                    { id: '4', text: 'Golongan 4' },
                    { id: '5', text: 'Golongan 5' },
                ];

                const shiftOptions = [
                    { id: '*', text: 'All' },
                    { id: '1', text: 'Shift 1' },
                    { id: '2', text: 'Shift 2' },
                    { id: '3', text: 'Shift 3' },
                ];


                let columns = @json($columns);

                btnFilter.attr("disabled", ruas_id.val() == null);

                ruas_id.select2({
                    ajax: {
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: "{{ route('select2.getRuas') }}",
                        type: 'POST',
                        dataType: 'json',
                        processResults: function (data) {
                            const { data: ruas } = data;

                            return {
                                results: $.map(ruas, function(item) {
                                    return {
                                        id: item.value,
                                        text: item.label
                                    };
                                })
                            };
                        },
                        beforeSend: function() {
                            // Disable gerbang_id secara default
                            gerbang_id.attr("disabled", true);
                        },
                    },
                    placeholder: "-- Pilih Ruas --"
                });

                gerbang_id.select2({
                    ajax: {
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: "{{ route('select2.getGerbang') }}",
                        type: 'POST',
                        dataType: 'json',
                        data: function (params) {
                            return {
                                query: params.term,
                                ruas_id: ruas_id.val()
                            };
                        },
                        processResults: function (data) {
                            const {data: ruas} = data;

                            return {
                                results: $.map((ruas), function(item) {
                                    return {
                                        id: item.value,
                                        text: item.label
                                    };
                                })
                            }
                        },
                    },
                    placeholder: "-- Pilih Gerbang --",
                });

                // gardu_id.select2({
                //     ajax: {
                //         headers: {
                //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                //         },
                //         url: "{{ route('select2.getGardu') }}",
                //         type: 'POST',
                //         dataType: 'json',
                //         data: function (params) {
                //             return {
                //                 query: params.term,
                //                 ruas_id: ruas_id.val()
                //             };
                //         },
                //         processResults: function (data) {
                //             const {data: ruas} = data;

                //             return {
                //                 results: $.map((ruas), function(item) {
                //                     return {
                //                         id: item.value,
                //                         text: item.label
                //                     };
                //                 })
                //             }
                //         },
                //     },
                //     placeholder: "-- Pilih Gerbang --",
                // });


                metoda_bayar_id.select2({
                    data: metodaBayarOptions,
                    placeholder: "-- Pilih Metoda Bayar --",
                });
                
                // gardu_id.select2({
                //     data: garduOptions,
                //     placeholder: "-- Pilih Gardu --",
                // });

                golongan_id.select2({
                    data: golonganOptions,
                    placeholder: "-- Pilih Golongan --",
                });

                shift_id.select2({
                    data: shiftOptions,
                    placeholder: "-- Pilih Shift --",
                });

                // When select2 ruas id on change
                // Toggle gerbang_id disabled when ruas_id changes
                ruas_id.on('change', function() {
                    btnFilter.attr("disabled", true);
                    gerbang_id.prop("disabled", !ruas_id.val());
                    gerbang_id.html('');
                });

                gerbang_id.on('change', function() {
                    btnFilter.attr("disabled", true);
                });


                tblTransaksiDetail = new DataTable('#tblTransaksiDetail', {
                    ajax: {
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: "{{ route('mediasi.transaction_detail.getData') }}",
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
                            d.ruas_id = $('#ruas_id').val();
                            d.gerbang_id = $('#gerbang_id').val();
                            d.start_date = $('#start_date').val();
                            d.end_date = $('#end_date').val();
                            // d.gardu_id = $('#gardu_id').val();
                            d.shift_id = $('#shift_id').val();
                            d.golongan_id = $('#golongan_id').val();
                            d.metoda_bayar_id = $('#metoda_bayar_id').val();
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
                    order: [[2, 'desc']],
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
                tblTransaksiDetail.draw();
            }
        </script>
    </x-slot>

    <form class="bg-white rounded-lg shadow-md flex flex-col items-end gap-5 p-5" onsubmit="handleSubmit(event)">
        <!-- Filter Component -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-5 place-content-between w-full">
            <!-- Ruas -->
            <div>
                <label class="mb-2 block font-medium text-sm text-blue-950" for="ruas_id">{{ __("Ruas") }}</label>
                <select name="ruas_id" id="ruas_id" class="select2-ruas px-3 py-2 border border-gray-300 rounded-lg text-blue-950 w-full focus:ring-2 focus:ring-blue-950 focus:text-blue-950 h-10"></select>
            </div>
        
            <!-- Gerbang -->
            <div>
                <label class="mb-2 block font-medium text-sm text-blue-950" for="gerbang_id">{{ __("Gerbang") }}</label>
                <select name="gerbang_id" disabled id="gerbang_id" class="px-3 py-2 border border-gray-300 rounded-lg text-blue-950 w-full focus:ring-2 focus:ring-blue-950 focus:text-blue-950 h-10"></select>
            </div>
        
            <!-- Start Date -->
            <div>
                <label class="mb-2 block font-medium text-sm text-blue-950" for="start_date">{{ __("Start Date") }}</label>
                <input 
                    class="px-3 py-2 border border-gray-300 rounded-lg text-blue-950 w-full max-w-md focus:ring-2 focus:ring-blue-950 focus:text-blue-950 h-10" 
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
                    class="px-3 py-2 border border-gray-300 rounded-lg text-blue-950 w-full max-w-md focus:ring-2 focus:ring-blue-950 focus:text-blue-950 h-10" 
                    type="date" 
                    name="end_date" 
                    id="end_date"
                    value="{{ date('Y-m-d') }}"
                >
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 place-content-between w-full">
            <!-- Gardu -->
            {{-- <div>
                <label class="mb-2 block font-medium text-sm text-blue-950" for="gardu_id">{{ __("Gardu") }}</label>
                <select name="gardu_id" id="gardu_id" class="select2-ruas px-3 py-2 border border-gray-300 rounded-lg text-blue-950 w-full focus:ring-2 focus:ring-blue-950 focus:text-blue-950 h-10"></select>
            </div> --}}
        
            <!-- Shift -->
            <div>
                <label class="mb-2 block font-medium text-sm text-blue-950" for="shift_id">{{ __("Shift") }}</label>
                <select name="shift_id" id="shift_id" class="px-3 py-2 border border-gray-300 rounded-lg text-blue-950 w-full focus:ring-2 focus:ring-blue-950 focus:text-blue-950 h-10"></select>
            </div>
        
            <!-- Golongan -->
            <div>
                <label class="mb-2 block font-medium text-sm text-blue-950" for="golongan_id">{{ __("Golongan") }}</label>
                <select name="golongan_id" id="golongan_id" class="px-3 py-2 border border-gray-300 rounded-lg text-blue-950 w-full focus:ring-2 focus:ring-blue-950 focus:text-blue-950 h-10"></select>
            </div>
        
            <!-- Metoda Bayar -->
            <div>
                <label class="mb-2 block font-medium text-sm text-blue-950" for="metoda_bayar_id">{{ __("Metoda Bayar") }}</label>
                <select name="metoda_bayar_id" id="metoda_bayar_id" class="px-3 py-2 border border-gray-300 rounded-lg text-blue-950 w-full focus:ring-2 focus:ring-blue-950 focus:text-blue-950 h-10"></select>
            </div>
        </div>

        <x-button :disabled="true">
            Submit
        </x-button>
    </form>

    <h4 class="h-10"></h4>

    <div class="bg-white rounded-lg shadow-md gap-5 p-5">
        <table id="tblTransaksiDetail" class="display" style="width:100%">
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
