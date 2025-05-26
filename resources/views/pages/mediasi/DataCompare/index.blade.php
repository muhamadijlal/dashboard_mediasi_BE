<x-app-layout>
    <x-slot name="header">
        {{ __("Data Compare Mediasi Dashboard") }}
    </x-slot>

    <x-slot name="link">
        <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.2.2/css/buttons.dataTables.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.dataTables.css">
    </x-slot>

    <x-slot name="script">
        <script src="{{asset("assets/js/ipcheck.js")}}"></script>
        <script src="https://cdn.datatables.net/buttons/3.2.2/js/dataTables.buttons.js"></script>
        <script src="https://cdn.datatables.net/buttons/3.2.2/js/buttons.dataTables.js"></script>
        <script src="https://cdn.datatables.net/buttons/3.2.2/js/buttons.print.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/3.2.2/js/buttons.html5.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
        <script>
            let tblCompare;
            const btnFilter = $("#btnFilter");

            $(document).ready(function() {
                const ruas_id = $('#ruas_id');
                const gerbang_id = $('#gerbang_id');
                const shift_id = $('#shift_id');
                const metoda_bayar_id = $('#metoda_bayar_id');

                let columns = @json($columns);
                let params = JSON.parse(localStorage.getItem("params"));

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

                const shiftOptions = [
                    { id: '*', text: 'All' },
                    { id: '1', text: 'Shift 1' },
                    { id: '2', text: 'Shift 2' },
                    { id: '3', text: 'Shift 3' },
                ];


                btnFilter.attr("disabled", ruas_id.val() == null);

                stateParams = {
                    ruas_id: params?.ruas_id ?? null,
                    gerbang_id: params?.gerbang_id ?? null,
                    start_date: params?.start_date ?? null,
                    end_date: params?.end_date ?? null,
                    shift_id: params?.shift_id ?? null,
                    metoda_bayar_id: params?.metoda_bayar_id ?? null,
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
                    gerbang_id.html('');
                });

                gerbang_id.on('change', function() {
                    btnFilter.attr("disabled", true);
                });

                metoda_bayar_id.select2({
                    data: metodaBayarOptions,
                    placeholder: "-- Pilih Metoda Bayar --",
                });

                shift_id.select2({
                    data: shiftOptions,
                    placeholder: "-- Pilih Shift --",
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
                            d.ruas_id = $('#ruas_id').val() ?? stateParams.ruas_id;
                            d.gerbang_id = $('#gerbang_id').val() ?? stateParams.gerbang_id;
                            d.start_date = $('#start_date').val() ?? stateParams.start_date;
                            d.end_date = $('#end_date').val() ?? stateParams.end_date;
                            d.shift_id = $('#shift_id').val() ?? stateParams.shift_id;
                            d.metoda_bayar_id = $('#metoda_bayar_id').val() ?? stateParams.metoda_bayar_id;

                            d.selisih = $('#selisih').val();
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
                    order: [[5, 'asc']],
                    columns: columns,
                    layout: {
                        topEnd: {
                            buttons: ['csv', 'excel'],
                            search: true,
                        }
                    },
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
                            i.replace(/[\Rp.\s,]/g, '') * 1 :
                            typeof i === 'number' ?
                                i : 0;
                        };

                        function getValueFromLink(html) {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            return doc.querySelector('a') ? doc.querySelector('a').textContent : null;
                        }

                        let totalTarifIntegrator = api.column(6).data().reduce(function (a, b) {
                            return intVal(a) + intVal(b)
                        }, 0);
                        let totalTarifMediasi = api.column(7).data().reduce(function (a, b) {
                            return intVal(a) + intVal(b)
                        }, 0);
                        let totalDataIntegrator = api.column(8).data().reduce(function (a, b) {
                            return intVal(a) + intVal(b)
                        }, 0);
                        let totalDataMediasi = api.column(9).data().reduce(function (a, b) {
                            return intVal(a) + intVal(b)
                        }, 0);
                        let totalSelisihData = api.column(10).data().reduce(function (a, b) {
                            return intVal(a) + intVal(getValueFromLink(b))
                        }, 0);

                        $(api.column(0).footer()).html("Total");
                        $(api.column(6).footer()).html("Rp. "+number_format(totalTarifIntegrator,0,'.','.'))
                        $(api.column(7).footer()).html("Rp. "+number_format(totalTarifMediasi,0,'.','.'))
                        $(api.column(8).footer()).html(totalDataIntegrator)
                        $(api.column(9).footer()).html(totalDataMediasi)
                        $(api.column(10).footer()).html(totalSelisihData)
                    }
                });

                params && tblCompare.draw()
            });

            function handleSubmit(e)
            {
                e.preventDefault();
                tblCompare.draw();
            }

            function number_format(number, decimals, dec_point, thousands_sep) {
                var n = number,
                    prec = isNaN(decimals = Math.abs(decimals)) ? 0 : decimals,
                    sep = (typeof thousands_sep === 'undefined') ? '.' : thousands_sep,
                    dec = (typeof dec_point === 'undefined') ? ',' : dec_point,
                    s = n < 0 ? '-' : '',
                    i = parseInt(n = Math.abs(+n || 0).toFixed(prec)) + '',
                    j = (i.length) > 3 ? i.length % 3 : 0;

                return s + (j ? i.substr(0, j) + sep : '') + i.substr(j).replace(/(\d{3})(?=\d)/g, '$1' + sep) +
                    (prec ? dec + Math.abs(n - i).toFixed(prec).slice(2) : '');
            }
        </script>
    </x-slot>

    <form class="bg-white rounded-lg shadow-md flex flex-col items-end gap-5 p-5" onsubmit="handleSubmit(event)">
        <!-- Filter Component -->
        <div class="grid grid-rows-1 sm:grid-rows-2 gap-5 w-full">
            <div class="grid grid-cols-1 sm:grid-cols-4 w-full gap-5">
                <!-- Ruas -->
                <div class="w-full">
                    <label class="mb-2 block font-medium text-sm text-blue-950" for="ruas_id">{{ __("Ruas") }}</label>
                    <select name="ruas_id" id="ruas_id" class="select2-ruas px-3 py-2 border border-gray-300 rounded-lg text-blue-950 w-full focus:ring-2 focus:ring-blue-950 focus:text-blue-950 h-10"></select>
                </div>
            
                <!-- Gerbang -->
                <div class="w-full">
                    <label class="mb-2 block font-medium text-sm text-blue-950" for="gerbang_id">{{ __("Gerbang") }}</label>
                    <select name="gerbang_id" disabled id="gerbang_id" class="px-3 py-2 border border-gray-300 rounded-lg text-blue-950 w-full focus:ring-2 focus:ring-blue-950 focus:text-blue-950 h-10"></select>
                </div>
            
                <!-- Start Date -->
                <div class="w-full">
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
                <div class="w-full">
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

            <div class="grid grid-cols-1 sm:grid-cols-3 w-full gap-5">
                <!-- Selisih -->
                <div class="w-full">
                    <label class="mb-2 block font-medium text-sm text-blue-950" for="selisih">{{ __("Selisih") }}</label>
                    <select name="selisih" id="selisih" class="px-3 py-2 border border-gray-300 rounded-lg text-blue-950 w-full focus:ring-2 focus:ring-blue-950 focus:text-blue-950 h-10">
                        <option value="*" selected>{{ __("All") }}</option>
                        <option value="1">{{ __("True") }}</option>
                        <option value="0">{{ __("False") }}</option>
                    </select>
                </div>

                <!-- Shift -->
                <div class="w-full">
                    <label class="mb-2 block font-medium text-sm text-blue-950" for="shift_id">{{ __("Shift") }}</label>
                    <select name="shift_id" id="shift_id" class="select2-ruas px-3 py-2 border border-gray-300 rounded-lg text-blue-950 w-full focus:ring-2 focus:ring-blue-950 focus:text-blue-950 h-10"></select>
                </div>

                <!-- Metoda Bayar -->
                <div class="w-full">
                    <label class="mb-2 block font-medium text-sm text-blue-950" for="metoda_bayar_id">{{ __("Metoda Bayar") }}</label>
                    <select name="metoda_bayar_id" id="metoda_bayar_id" class="select2-ruas px-3 py-2 border border-gray-300 rounded-lg text-blue-950 w-full focus:ring-2 focus:ring-blue-950 focus:text-blue-950 h-10"></select>
                </div>
            </div>  
        </div>

        <x-button :disabled="true">
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
                <th colspan="6" class="bg-gray-50"></th>
                <th class="bg-gray-50"></th>
                <th class="bg-gray-50"></th>
                <th class="bg-gray-50"></th>
                <th class="bg-gray-50"></th>
                <th class="bg-gray-50"></th>
            </tfoot>
        </table>
    </div> 
</x-app-layout>