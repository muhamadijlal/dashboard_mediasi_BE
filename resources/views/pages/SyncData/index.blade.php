<x-app-layout>
    <x-slot name="header">
        {{ __("Sync Data Dashboard") }}
    </x-slot>

    <x-slot name="script">
        <script>
            let tblSync;
            let columns = @json($columns);
            let params = @json($params);

            $(document).ready(function() {
                tblSync = new DataTable('#tblSync', {
                    ajax: {
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: "{{ route('sync.getData') }}",
                        type: 'POST',
                        data: function (d) {
                            // Add parameters from URL
                            d.ruas_id = params.ruas_id;
                            d.tanggal = params.tanggal;
                            d.gerbang_id = params.gerbang_id;
                            d.golongan = params.golongan;
                            d.gardu_id = params.gardu_id;
                            d.shift = params.shift;
                        },
                        error: function (xhr, error, code) {
                            console.log(xhr, error, code)
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
                    }
                });
            });

            function handleSync(e)
            {
                e.preventDefault();
                const isConfirmed = confirm("Apakah anda yakin akan melakukan sync ?");

                if(isConfirmed) {
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: '{{ route("sync.syncData") }}',
                        type: 'POST',
                        data: {
                            ruas_id: params.ruas_id,
                            tanggal: params.tanggal,
                            gerbang_id: params.gerbang_id,
                            golongan: params.golongan,
                            gardu_id: params.gardu_id,
                            shift: params.shift
                        },
                        success: function(response) {
                            localStorage.setItem('params', JSON.stringify(params));
                            location.href = "{{ route('data_compare.transaction_detail.dashboard') }}";
                        },
                        error: function(xhr, status, error) {
                            // Handle errors in AJAX response
                            console.error('Request failed:', status, error);
                        }
                    });
                }
            }
        </script>
    </x-slot>

    <form onsubmit="handleSync(event)">
        <button class="px-6 py-1 bg-yellow-400 border-2 border-blue-950 rounded-lg font-bold">
            <i class="fa-solid fa-play"></i>
            Sync
        </button>
    </form>

    <h4 class="h-10"></h4>

    <div class="bg-white rounded-lg shadow-md gap-5 p-5">
        <table id="tblSync" class="display" style="width:100%">
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
