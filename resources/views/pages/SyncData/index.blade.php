<x-app-layout>
    <x-slot name="header">
        {{ __("Sync Data Page") }}
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
        </script>
    </x-slot>

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
