<x-app-layout>
    <x-slot name="header">
        {{ __("Rekap AT4 Mediasi Dashboard") }}
    </x-slot>

    <x-slot name="script">
        <script src="{{asset("assets/js/ipcheck.js")}}"></script>
        <script>
            let tblRekapAT4;
            const btnFilter = $("#btnFilter");


            $(document).ready(function() {
                const ruas_id = $('#ruas_id');
                const gerbang_id = $('#gerbang_id');
                const shift_id = $('#shift_id');

                let columns = @json($columns);

                const shiftOptions = [
                    { id: '*', text: 'All' },
                    { id: '1', text: 'Shift 1' },
                    { id: '2', text: 'Shift 2' },
                    { id: '3', text: 'Shift 3' },
                ];

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
                        beforeSend: function() {
                           // Disable gerbang_id secara default
                            gerbang_id.val(null).trigger('change');
                            gerbang_id.attr("disabled", true);
                        },
                    },
                    placeholder: "-- Pilih Ruas --",
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

                shift_id.select2({
                    data: shiftOptions,
                    placeholder: "-- Pilih Shift --",
                });

                // When select2 ruas id on change
                // Toggle gerbang_id disabled when ruas_id changes
                ruas_id.on('change', function() {
                    btnFilter.attr('disabled', true);
                    gerbang_id.prop("disabled", !ruas_id.val());
                    gerbang_id.html('');
                });

                gerbang_id.on('change', function() {
                    btnFilter.attr("disabled", true);
                });


                tblRekapAT4 = new DataTable('#tblRekapAT4', {
                    ajax: {
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: "{{ route('mediasi.recap_at4.getData') }}",
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
                            d.shift_id = $('#shift_id').val();
                            d.end_date = $('#end_date').val();
                        },
                        error: function (response) {
                            Swal.fire({
                                html: `<x-alert-error
                                        title="Error!"
                                        message="${response?.responseJSON?.message}!"
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
                    order: [[1, 'desc']],
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
                tblRekapAT4.draw();
            }
        </script>
    </x-slot>

    <form class="bg-white rounded-lg shadow-md flex flex-col items-end gap-5 p-5" onsubmit="handleSubmit(event)">
        <!-- Filter Component -->
        <div class="grid grid-rows-1 sm:grid-rows-2 w-full gap-5">
            <div class='grid grid-cols-1 sm:grid-cols-3 w-full gap-5'>
                <!-- Ruas -->
                <div class=w-full>
                    <label class="mb-2 block font-medium text-sm text-blue-950" for="ruas_id">{{ __("Ruas") }}</label>
                    <select name="ruas_id" id="ruas_id" class="select2-ruas px-3 py-2 border border-gray-300 rounded-lg text-blue-950 w-full focus:ring-2 focus:ring-blue-950 focus:text-blue-950 h-10"></select>
                </div>
            
                <!-- Gerbang -->
                <div class=w-full>
                    <label class="mb-2 block font-medium text-sm text-blue-950" for="gerbang_id">{{ __("Gerbang") }}</label>
                    <select name="gerbang_id" disabled id="gerbang_id" class="px-3 py-2 border border-gray-300 rounded-lg text-blue-950 w-full focus:ring-2 focus:ring-blue-950 focus:text-blue-950 h-10"></select>
                </div>

                <!-- Shift -->
                <div class=w-full>
                    <label class="mb-2 block font-medium text-sm text-blue-950" for="shift_id">{{ __("Shift") }}</label>
                    <select name="shift_id" id="shift_id" class="px-3 py-2 border border-gray-300 rounded-lg text-blue-950 w-full focus:ring-2 focus:ring-blue-950 focus:text-blue-950 h-10"></select>
                </div>
            </div>
        
            <div class='grid grid-cols-1 sm:grid-cols-2 w-full gap-5'>
                <!-- Start Date -->
                <div class=w-full>
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
                <div class=w-full>
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
        </div>

        <x-button :disabled="true">
            Submit
        </x-button>
    </form>

    <h4 class="h-10"></h4>

    <div class="bg-white rounded-lg shadow-md gap-5 p-5">
        <table id="tblRekapAT4" class="display" style="width:100%">
            <thead>
                <tr>
                    @foreach($columns as $column)
                        <th>
                            {!! $column['title'] !!}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div> 
</x-app-layout>
