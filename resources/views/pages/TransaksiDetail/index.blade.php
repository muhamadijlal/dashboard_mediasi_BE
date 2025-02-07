<x-app-layout>
    <x-slot name="header">
        {{ __("Transaction Detail Dashboard") }}
    </x-slot>

    <x-slot name="script">
        <script src="{{asset("assets/js/validationDates.js")}}"></script>
        <script src="{{asset("assets/js/ipcheck.js")}}"></script>
        <script>
            let tblTransaksiDetail;
            $('#ruas_id').val('').trigger('change');
            $('#gerbang_id').val('').trigger('change');

            $(document).ready(function() {
                const ruas_id = $('#ruas_id');
                const gerbang_id = $('#gerbang_id');
                let columns = @json($columns);

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

                // When select2 ruas id on change
                // Toggle gerbang_id disabled when ruas_id changes
                ruas_id.on('change', function() {
                    gerbang_id.prop("disabled", !ruas_id.val());
                });


                tblTransaksiDetail = new DataTable('#tblTransaksiDetail', {
                    ajax: {
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: "{{ route('transaction_detail.getData') }}",
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
                <select name="ruas_id[]" multp id="ruas_id" class="select2-ruas px-3 py-2 border border-gray-300 rounded-lg text-blue-950 w-full focus:ring-2 focus:ring-blue-950 focus:text-blue-950 h-10"></select>
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

        <x-button>
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
