<x-app-layout>
    <x-slot name="header">
        {{ __("Sync Data Digital Receipt Dashboard") }}
    </x-slot>

    <x-slot name="script">
        <script>
            function showConfirmModal() {
                Swal.fire({
                    html: `<x-alert
                                title="Attention!"
                                message="Are you sure want to sync data ?"
                                actionText="Sync data"
                            />`,
                    showConfirmButton: false,
                    showCancelButton: false,
                    customClass: {
                        popup: 'hide-bg-swal',
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            url: '{{ route("sync.digital_receipt.data_compare.syncData") }}',
                            type: 'POST',
                            data: {
                                ruas_id: params.ruas_id,
                                start_date: params.start_date,
                                end_date: params.end_date,
                                gerbang_id: params.gerbang_id,
                                golongan: params.golongan,
                                gardu_id: params.gardu_id,
                                shift: params.shift
                            },
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
                            success: function(response) {
                                Swal.fire({
                                    html: `<x-alert-success
                                            title="Success!"
                                            message="Sync data success!"
                                        />`,
                                    showConfirmButton: false,
                                    showCancelButton: false,
                                    timer: 1500,
                                    customClass: {
                                        popup: 'hide-bg-swal',
                                    }
                                });
                                localStorage.setItem('params_resi', JSON.stringify(params));
                                location.href = "{{ route('digital_receipt.data_compare.dashboard') }}";
                            },
                            error: function(xhr, status, error) {
                                Swal.fire({
                                    html: `<x-alert-error
                                            title="${status.toUpperCase()}!"
                                            message="${error}!"
                                        />`,
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
            }
        </script>
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
                        url: "{{ route('sync.digital_receipt.data_compare.getData') }}",
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
                            // Add parameters from URL
                            d.ruas_id = params.ruas_id;
                            d.start_date = params.start_date;
                            d.end_date = params.end_date;
                            d.gerbang_id = params.gerbang_id;
                            d.golongan = params.golongan;
                            d.gardu_id = params.gardu_id;
                            d.shift = params.shift;
                        },
                        error: function (xhr, error, code) {
                            Swal.fire({
                                html: `<x-alert-error
                                        title="${status.toUpperCase()}!"
                                        message="${error}!"
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
                    }
                });
            });

            function handleSync(e) {
                e.preventDefault();
                showConfirmModal();
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
