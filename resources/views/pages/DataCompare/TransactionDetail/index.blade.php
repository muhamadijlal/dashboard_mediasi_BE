<x-app-layout>
    <x-slot name="header">
        {{ __("Data Compare Transaction Detail Dashboard") }}
    </x-slot>

    <x-slot name="script">
        <script src="{{asset("assets/js/validationDates.js")}}"></script>
        <script src="{{asset("assets/js/ipcheck.js")}}"></script>
        <script>
            let tblCompare;
            const btnFilter = $("#btnFilter");

            $(document).ready(function() {
                const ruas_id = $('#ruas_id');
                const gerbang_id = $('#gerbang_id');
                let columns = @json($columns);
                let params = JSON.parse(localStorage.getItem("params"));

                btnFilter.attr("disabled", ruas_id.val() == '');

                stateParams = {
                    ruas_id: params?.ruas_id ?? null,
                    gerbang_id: params?.gerbang_id ?? null,
                    start_date: params?.start_date ?? null,
                    end_date: params?.end_date ?? null,
                    selisih: "*",
                };

                let defaultRuas = params ? [{ id: params.ruas_id, text: params.ruas_nama }] : [{ id: '', text: '' }];
                let defaultGerbang = params ? [{ id: params.gerbang_id, text: params.gerbang_nama }] : [{ id: '', text: '' }];

                params?.gerbang_id ? gerbang_id.attr("disabled", false) : gerbang_id.attr("disabled", true);
                params?.start_date && $('#start_date').val(params.start_date);
                params?.end_date && $('#end_date').val(params.end_date);

                ruas_id.select2({
                    data: defaultRuas,
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
                    data: defaultGerbang
                });

                // When select2 ruas id on change
                // Toggle gerbang_id disabled when ruas_id changes
                ruas_id.on('change', function() {
                    btnFilter.attr("disabled", true);
                    gerbang_id.prop("disabled", !ruas_id.val());
                });

                tblCompare = new DataTable('#tblCompare', {
                    ajax: {
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: "{{ route('mediasi.data_compare.getData') }}",
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
                            d.ruas_id = stateParams.ruas_id ?? $('#ruas_id').val();
                            d.gerbang_id = stateParams.gerbang_id ?? $('#gerbang_id').val();
                            d.start_date = stateParams.start_date ?? $('#start_date').val();
                            d.end_date = stateParams.end_date ?? $('#end_date').val();
                            d.selisih = stateParams.selisih ?? $('#selisih').val();
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

                            localStorage.removeItem("params")
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
                    footerCallback: function(row, data, start, end, display) {
                        let api = this.api();

                        let intVal = function(i) {
                            return typeof i === 'string' ?
                            i.replace('/[\$,]/g', '')*1 :
                            typeof i === 'number' ?
                                i : 0;
                        };

                        function getValueFromLink(html) {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            return doc.querySelector('a') ? doc.querySelector('a').textContent : null;
                        }

                        let totalDataIntegrator = api.column(5).data().reduce(function (a, b) {
                            return intVal(a) + intVal(b)
                        }, 0);
                        let totalDataMediasi = api.column(6).data().reduce(function (a, b) {
                            return intVal(a) + intVal(b)
                        }, 0);
                        let totalSelisihData = api.column(7).data().reduce(function (a, b) {
                            return intVal(a) + intVal(getValueFromLink(b))
                        }, 0);

                        $(api.column(0).footer()).html("Total");
                        $(api.column(5).footer()).html(totalDataIntegrator)
                        $(api.column(6).footer()).html(totalDataMediasi)
                        $(api.column(7).footer()).html(totalSelisihData)
                    }
                });

                params && tblCompare.draw()
            });

            function handleSubmit(e)
            {
                e.preventDefault();
                tblCompare.draw();
            }
        </script>
    </x-slot>

    <form class="bg-white rounded-lg shadow-md flex flex-col items-end gap-5 p-5" onsubmit="handleSubmit(event)">
        <!-- Filter Component -->
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-5 place-content-between w-full">
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

            <!-- Selisih -->
            <div class="col-span-2 lg:col-span-1">
                <label class="mb-2 block font-medium text-sm text-blue-950" for="selisih">{{ __("Selisih") }}</label>
                <select name="selisih" id="selisih" class="px-3 py-2 border border-gray-300 rounded-lg text-blue-950 w-full focus:ring-2 focus:ring-blue-950 focus:text-blue-950 h-10">
                    <option value="*" selected>{{ __("All") }}</option>
                    <option value="1">{{ __("True") }}</option>
                    <option value="0">{{ __("False") }}</option>
                </select>
            </div>
        </div>

        <x-button>
            Submit
        </x-button>
    </form>

    <h4 class="h-10"></h4>

    <div class="bg-white rounded-lg shadow-md gap-5 p-5">
        <table id="tblCompare" class="display" style="width:100%">
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
            <tfoot>
                <th colspan="5" class="bg-gray-50"></th>
                <th class="bg-gray-50"></th>
                <th class="bg-gray-50"></th>
                <th class="bg-gray-50"></th>
            </tfoot>
        </table>
    </div> 
</x-app-layout>