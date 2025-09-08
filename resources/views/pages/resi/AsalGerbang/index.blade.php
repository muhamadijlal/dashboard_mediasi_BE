<x-app-layout>
    <x-slot name="header">
        {{ __("Asal Gerbang") }}
    </x-slot>

    {{-- <x-slot name="script">
        <script src="{{asset("assets/js/validationDates.js")}}"></script>
        <script src="{{asset("assets/js/ipcheck.js")}}"></script>
        <script>
            let tblAsalGerbang;
            const btnFilter = $("#btnFilter");
            

            $(document).ready(function() {
                const ruas_id = $('#ruas_id');
                const $ruasModal  = $('#ruas_id_modal'); // select2 di dalam modal
                const gerbang_id = $('#gerbang_id');
                let columns = @json($columns);

                btnFilter.attr("disabled", ruas_id.val() == null);

                ruas_id.select2({
                    ajax: {
                        url: "{{ route('select2.getKoneksi') }}",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        type: 'POST',
                        dataType: 'json',
                        processResults: function (data) {
                            const {data: ruas} = data;
                            return {
                                results: $.map((ruas), function(item) {
                                    return {
                                        id: item.id_ruas,
                                        text: item.nama_ruas 
                                    };
                                })
                            }
                        },
                    },
                    placeholder: "-- Pilih Ruas --",
                });

                $ruasModal.select2({
                    ajax: {
                        url: "{{ route('select2.getKoneksi') }}",
                        headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        type: 'POST',
                        dataType: 'json',
                        processResults: function (data) {
                        const { data: ruas } = data;
                        return {
                            results: $.map(ruas, function(item) {
                            return {
                                id: item.id_ruas,
                                text: item.nama_ruas
                            };
                            })
                        }
                        },
                    },
                    placeholder: "-- Pilih Ruas --",
                    dropdownParent: $('#modalAsalGerbang'), // penting agar dropdown tampil di atas modal
                    width: '100%'
                });


                ruas_id.on('change', function() {
                    btnFilter.attr("disabled", false);

                });


                tblAsalGerbang = new DataTable('#tblAsalGerbang', {
                    ajax: {
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: "{{ route('resi.asal_gerbang.getData') }}",
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
                            d.ruas_id = $('#ruas_id').val() ;
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

                            localStorage.removeItem('params_resi');
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
                });

                // params && tblAsalGerbang.draw()

                btnFilter.on('click', function() {
                    tblAsalGerbang.draw();
                })

                 // --- Modal helpers ---
                const $modal   = $('#modalAsalGerbang');
                const $form    = $('#formAsalGerbang');
                const $btnOpen = $('#btnTambahAsalGerbang');
                const $btnSave = $('#btnSaveAsalGerbang');

                function syncRuasToModal() {
                    const selectedId   = $ruasFilter.val();
                    const selectedText = ($ruasFilter.select2('data')[0] || {}).text;

                    // reset dulu
                    $ruasModal.val(null).trigger('change');

                    if (selectedId && selectedText) {
                        // buat option sementara supaya select2 bisa menampilkan teks terpilih
                        const opt = new Option(selectedText, selectedId, true, true);
                        $ruasModal.append(opt).trigger('change');
                    }
                }

                function openModal() {                    
                    $modal.removeClass('hidden').addClass('flex');
                    document.body.classList.add('overflow-hidden');
                    $('#id_asal_gerbang').focus();

                }
                function closeModal() {
                    $modal.addClass('hidden').removeClass('flex');
                    document.body.classList.remove('overflow-hidden');
                    $form[0].reset();
                }

                // Open/close
                $btnOpen.on('click', openModal);
                $('#btnCloseModal, #btnCancelModal, #modalBackdrop').on('click', closeModal);
                $(document).on('keydown', function(e){ if(e.key === 'Escape') closeModal(); });

                // Submit form
                $form.on('submit', function(e){
                    e.preventDefault();

                    const ruasId = $ruasModal.val()
                    if (!ruasId) {
                        Swal.fire({
                        html: `<x-alert-error title="Ruas wajib dipilih" message="Silakan pilih Ruas pada form."/>`,
                        showConfirmButton: false,
                        timer: 1400,
                        customClass: { popup: 'hide-bg-swal' }
                        });
                        return;
                    }

                    const payload = {
                        id_asal_gerbang: $('#id_asal_gerbang').val(),
                        nama_asal_gerbang: $('#nama_asal_gerbang').val(),
                        ruas_id: ruasId,
                        nama_ruas: $('#ruas_id_modal option:selected').text(),
                    };

                    $.ajax({
                        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                        url: "{{ route('resi.asal_gerbang.store') }}", // <-- ganti jika perlu
                        type: "POST",
                        data: payload,
                        beforeSend: function() {
                            $btnSave.prop('disabled', true).text('Menyimpan...');
                            Swal.fire({
                                html: `<x-alert-loading />`,
                                showConfirmButton: false,
                                allowOutsideClick: false,
                                customClass: { popup: 'hide-bg-swal' }
                            });
                        },
                        success: function(resp) {
                            closeModal();
                            if (window.tblAsalGerbang) {
                                tblAsalGerbang.draw(false); // refresh datatable, tetap di halaman sekarang
                            }
                            Swal.fire({
                                html: `<x-alert-success title="Tersimpan" message="Data berhasil disimpan."/>`,
                                showConfirmButton: false,
                                timer: 1400,
                                customClass: { popup: 'hide-bg-swal' }
                            });
                        },
                        error: function(xhr) {
                            const msg = xhr?.responseJSON?.message || 'Gagal menyimpan data.';
                            Swal.fire({
                                html: `<x-alert-error title="Error!" message="${msg}"/>`,
                                showConfirmButton: false,
                                customClass: { popup: 'hide-bg-swal' }
                            });
                        },
                        complete: function() {
                            $btnSave.prop('disabled', false).text('Simpan');
                        }
                    });
                });

                tblAsalGerbang.draw();
            });
            function deleteData(id) {
                Swal.fire({
                    title: 'Hapus Data',
                    text: "Anda yakin ingin menghapus data ini?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Hapus!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                            url: "{{ route('resi.asal_gerbang.destroy')}}", // <-- ganti jika perlu
                            type: "DELETE",
                            data: { id },
                            beforeSend: function() {
                                Swal.fire({
                                    html: `<x-alert-loading />`,
                                    showConfirmButton: false,
                                    allowOutsideClick: false,
                                    customClass: { popup: 'hide-bg-swal' }
                                });
                            },
                            success: function(resp) {
                                if (window.tblAsalGerbang) {
                                    tblAsalGerbang.draw(false); // refresh datatable, tetap di halaman sekarang
                                }
                                Swal.fire({
                                    html: `<x-alert-success title="Terhapus" message="Data berhasil dihapus."/>`,
                                    showConfirmButton: false,
                                    timer: 1400,
                                    customClass: { popup: 'hide-bg-swal' }
                                });
                            },
                            error: function(xhr) {
                                const msg = xhr?.responseJSON?.message || 'Gagal menghapus data.';
                                Swal.fire({
                                    html: `<x-alert-error title="Error!" message="${msg}"/>`,
                                    showConfirmButton: false,
                                    customClass: { popup: 'hide-bg-swal' }
                                });
                            }
                        });
                    }
                });
            }

        </script>
    </x-slot> --}}


    <x-slot name="script">
    <script src="{{asset("assets/js/validationDates.js")}}"></script>
    <script src="{{asset("assets/js/ipcheck.js")}}"></script>
    <script>
        let tblAsalGerbang;
        const btnFilter = $("#btnFilter");

        $(document).ready(function() {
            const ruas_id    = $('#ruas_id');
            const $ruasFilter= $('#ruas_id'); // <— dipakai syncRuasToModal()
            const $ruasModal = $('#ruas_id_modal'); // select2 di dalam modal
            const gerbang_id = $('#gerbang_id');
            let columns = @json($columns);

            btnFilter.attr("disabled", ruas_id.val() == null);
            ruas_id.select2({
            ajax: {
                url: "{{ route('select2.getKoneksi') }}",
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                type: 'POST',
                dataType: 'json',
                processResults: function (data) {
                const ruas = (data && data.data) || [];
                return {
                    results: $.map(ruas, function(item){
                    return { id: String(item.id_ruas), text: item.nama_ruas };
                    })
                }
                },
            },
            placeholder: "— Pilih Ruas —",
            allowClear: true
            });


            $ruasModal.select2({
                ajax: {
                    url: "{{ route('select2.getKoneksi') }}",
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    type: 'POST',
                    dataType: 'json',
                    processResults: function (data) {
                        const { data: ruas } = data;
                        return {
                            results: $.map(ruas, function(item) {
                                return { id: item.id_ruas, text: item.nama_ruas };
                            })
                        }
                    },
                },
                placeholder: "-- Pilih Ruas --",
                dropdownParent: $('#modalAsalGerbang'),
                width: '100%'
            });

            ruas_id.on('change', function() {
                btnFilter.attr("disabled", false);
            });

            tblAsalGerbang = new DataTable('#tblAsalGerbang', {
                ajax: {
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    url: "{{ route('resi.asal_gerbang.getData') }}",
                    type: 'POST',
                    beforeSend: function() {
                        Swal.fire({
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            didOpen: () => Swal.showLoading(),
                            customClass: { popup: 'hide-bg-swal' }
                        });
                    },
                    data: function (d) {
                        d.ruas_id = $('#ruas_id').val();
                    },
                    error: function (response) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response?.responseJSON?.message || 'Server error',
                            showConfirmButton: false,
                            customClass: { popup: 'hide-bg-swal' }
                        });
                        localStorage.removeItem('params_resi');
                    },
                    xhr: function() {
                        var xhr = new XMLHttpRequest();
                        xhr.onreadystatechange = function() {
                            if (xhr.readyState === 4 && xhr.status === 200) {
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
                language: { emptyTable: "Empty" },
                deferLoading: 0,
            });

            btnFilter.on('click', function() {
                tblAsalGerbang.draw();
            });

            // --- Modal helpers ---
            const $modal   = $('#modalAsalGerbang');
            const $form    = $('#formAsalGerbang');
            const $btnOpen = $('#btnTambahAsalGerbang');
            const $btnSave = $('#btnSaveAsalGerbang');

            function syncRuasToModal() {
                const selectedId   = $ruasFilter.val();
                const selectedText = ($ruasFilter.select2('data')[0] || {}).text;
                $ruasModal.val(null).trigger('change');
                if (selectedId && selectedText) {
                    const opt = new Option(selectedText, selectedId, true, true);
                    $ruasModal.append(opt).trigger('change');
                }
            }

            function openModal() {
                $modal.removeClass('hidden').addClass('flex');
                document.body.classList.add('overflow-hidden');
                $('#id_asal_gerbang').focus();
            }
            function closeModal() {
                $modal.addClass('hidden').removeClass('flex');
                document.body.classList.remove('overflow-hidden');
                $form[0].reset();
                // Jangan reset select2 di sini biar state tetap kalau diperlukan
            }

            // Open/close
            $btnOpen.on('click', function() {
                // Mode create
                $('#formMode').val('create');
                $('#modalTitle').text('Tambah Asal Gerbang');
                $('#original_id').val('');
                $form[0].reset();
                $ruasModal.val(null).trigger('change');
                syncRuasToModal();
                openModal();
            });
            $('#btnCloseModal, #btnCancelModal, #modalBackdrop').on('click', closeModal);
            $(document).on('keydown', function(e){ if(e.key === 'Escape') closeModal(); });

            // === EDIT HANDLER ===
            // Pastikan tombol Edit di kolom action menggunakan class .btn-edit dan data-id
            // (lihat catatan server-side di bawah)
            // $(document).on('click', '#edit, .btn-edit', function() {
            //     const id = $(this).data('id');
            //     if (!id) return;

            //     $('#formMode').val('edit');
            //     $('#modalTitle').text('Edit Asal Gerbang');
            //     $('#original_id').val(id);
            //     $form[0].reset();
            //     $ruasModal.val(null).trigger('change');

            //     const showUrl = "{{ route('resi.asal_gerbang.show', ':id') }}".replace(':id', id);

            //     $.ajax({
            //         url: showUrl,
            //         type: 'GET',
            //         beforeSend() {
            //             Swal.fire({
            //                 allowOutsideClick: false,
            //                 showConfirmButton: false,
            //                 didOpen: () => Swal.showLoading(),
            //                 customClass: { popup: 'hide-bg-swal' }
            //             });
            //         },
            //         success(resp) {
            //             const row = resp.data;
            //             // Prefill
            //             $('#id_asal_gerbang').val(row.id_asal_gerbang);
            //             $('#nama_asal_gerbang').val(row.nama_asal_gerbang);

            //             // Set Ruas di modal
            //             const ruasId   = row.id_ruas;
            //             const ruasText = row.nama_ruas || row.id_ruas || '';
            //             if (ruasId) {
            //                 const opt = new Option(ruasText, ruasId, true, true);
            //                 $ruasModal.append(opt).trigger('change');
            //             }

            //             Swal.close();
            //             openModal();
            //         },
            //         error(xhr) {
            //             Swal.fire({
            //                 icon: 'error',
            //                 title: 'Error!',
            //                 text: xhr?.responseJSON?.message || 'Gagal memuat data.',
            //                 showConfirmButton: false,
            //                 customClass: { popup: 'hide-bg-swal' }
            //             });
            //         }
            //     });
            // });

            $(document).on('click', '#edit, .btn-edit', function() {
                const id = $(this).data('id');
                if (!id) return;

                $('#formMode').val('edit');
                $('#modalTitle').text('Edit Asal Gerbang');

                $form[0].reset();

                // Prefill Ruas dari atribut data (jika ada)
                const ruasIdAttr   = $(this).data('ruasId')   ?? $(this).attr('data-ruas-id');
                const ruasNamaAttr = $(this).data('ruasNama') ?? $(this).attr('data-ruas-nama');
                $('#original_id').val(id);
                $('#original_ruas_id').val(ruasIdAttr);
                $('#original_ruas_nama').val(ruasNamaAttr);
                // reset select2 dulu
                $ruasModal.val(null).trigger('change');

                if (ruasIdAttr) {
                    // Buat option sementara supaya select2 menampilkan teksnya
                    const opt = new Option(ruasNamaAttr || ruasIdAttr, ruasIdAttr, true, true);
                    $ruasModal.append(opt).trigger('change');
                }

                // const showUrl = "{{ route('resi.asal_gerbang.show', ':id') }}".replace(':id', id);

                $.ajax({
                    url: '/resi/asal_gerbang/show-with-ruas/?id=' + id + '&ruas_id=' + ruasIdAttr + '&ruas_nama=' + ruasNamaAttr,
                    type: 'GET',
                    beforeSend() {
                        Swal.fire({
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            didOpen: () => Swal.showLoading(),
                            customClass: { popup: 'hide-bg-swal' }
                        });
                    },
                    success(resp) {
                        const row = resp.data;

                        // Isi field utama
                        $('#id_asal_gerbang').val(row.id_asal_gerbang);
                        $('#nama_asal_gerbang').val(row.nama_asal_gerbang);

                        // Jika tombol tidak bawa data ruas, isi dari API
                        if (!ruasIdAttr && row.id_ruas) {
                            const text = row.nama_ruas || row.id_ruas;
                            const opt = new Option(text, row.id_ruas, true, true);
                            $ruasModal.append(opt).trigger('change');
                        }

                        Swal.close();
                        openModal();
                    },
                    error(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: xhr?.responseJSON?.message || 'Gagal memuat data.',
                            showConfirmButton: false,
                            customClass: { popup: 'hide-bg-swal' }
                        });
                    }
                });
            });


            // Submit form (Create / Edit)
            $form.on('submit', function(e){
                e.preventDefault();

                const ruasId = $ruasModal.val();
                if (!ruasId) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Ruas wajib dipilih',
                        text: 'Silakan pilih Ruas pada form.',
                        showConfirmButton: false,
                        timer: 1400,
                        customClass: { popup: 'hide-bg-swal' }
                    });
                    return;
                }

                const mode       = $('#formMode').val();
                const originalId = $('#original_id').val();
                const originalRuasId = $('#original_ruas_id').val();
                const originalRuasNama = $('#original_ruas_nama').val();

                const payload = {
                    id_asal_gerbang:   $('#id_asal_gerbang').val(),
                    nama_asal_gerbang: $('#nama_asal_gerbang').val(),
                    ruas_id:           ruasId,
                    nama_ruas:         $('#ruas_id_modal option:selected').text(),
                    original_id:       originalId,
                    original_ruas_id:  originalRuasId,
                    original_ruas_nama: originalRuasNama,
                    ...(mode === 'edit' ? { _method: 'POST' } : {})
                };

                const url = (mode === 'edit')
                    ? "{{ route('resi.asal_gerbang.update' ) }}"
                    : "{{ route('resi.asal_gerbang.store') }}";

                $.ajax({
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    url,
                    type: 'POST', // spoof PUT saat edit
                    data: payload,
                    beforeSend: function() {
                        $('#btnSaveAsalGerbang').prop('disabled', true).text('Menyimpan...');
                        Swal.fire({
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            didOpen: () => Swal.showLoading(),
                            customClass: { popup: 'hide-bg-swal' }
                        });
                    },
                    success: function(resp) {
                        closeModal();
                        if (window.tblAsalGerbang) {
                            tblAsalGerbang.draw(false);
                        }
                        let msg = mode === 'edit' ? 'Data berhasil diperbarui.' : 'Data berhasil disimpan.';
                        Swal.fire({
                            html: `<x-alert-success
                                title="Success!"
                                message="${msg}"
                            />`,
                            showConfirmButton: false,
                            showCancelButton: false,
                                timer: 1500,
                                customClass: {
                                    popup: 'hide-bg-swal',
                            }
                        });
                    

                    },
                    error: function(xhr) {
                        const msg = xhr?.responseJSON?.message || 'Gagal menyimpan data.';
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: msg,
                            showConfirmButton: false,
                            customClass: { popup: 'hide-bg-swal' }
                        });
                    },
                    complete: function() {
                        $('#btnSaveAsalGerbang').prop('disabled', false).text('Simpan');
                    }
                });
            });

            tblAsalGerbang.draw();
        });

        // DELETE
        function deleteData(id,id_ruas,nama_ruas) {
            Swal.fire({
                title: 'Hapus Data',
                text: "Anda yakin ingin menghapus data ini?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const delUrl = "{{ route('resi.asal_gerbang.destroy') }}";

                    $.ajax({
                        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                        url: delUrl,
                        data: { id, id_ruas, nama_ruas },
                        type: "DELETE",
                        beforeSend: function() {
                            Swal.fire({
                                allowOutsideClick: false,
                                showConfirmButton: false,
                                didOpen: () => Swal.showLoading(),
                                customClass: { popup: 'hide-bg-swal' }
                            });
                        },
                        success: function(resp) {
                            if (window.tblAsalGerbang) {
                                tblAsalGerbang.draw(false);
                            }
                            Swal.fire({
                                html: `<x-alert-success
                                    title="Success!"
                                    message="Data berhasil dihapus."
                                />`,
                                showConfirmButton: false,
                                showCancelButton: false,
                                    timer: 1500,
                                    customClass: {
                                        popup: 'hide-bg-swal',
                                }
                            });
                        },
                        error: function(xhr) {
                            const msg = xhr?.responseJSON?.message || 'Gagal menghapus data.';
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: msg,
                                showConfirmButton: false,
                                customClass: { popup: 'hide-bg-swal' }
                            });
                        }
                    });
                }
            });
        }
        // make deleteData global
        window.deleteData = deleteData;
    </script>
</x-slot>


    <div class="bg-white rounded-lg shadow-md flex flex-col items-end gap-5 p-5" >
        <!-- Filter Component -->
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-5 place-content-between w-full">
            <!-- Ruas -->
            <div>
                <label class="mb-2 block font-medium text-sm text-blue-950" for="ruas_id">{{ __("Ruas") }}</label>
                <select name="ruas_id" id="ruas_id" class="select2-ruas px-3 py-2 border border-gray-300 rounded-lg text-blue-950 w-full focus:ring-2 focus:ring-blue-950 focus:text-blue-950 h-10">
                    <option value="">— Pilih Ruas —</option>
                </select>
            </div>
        </div>

        <x-button :disabled="true">
            Submit
        </x-button>
    </div>

    <h4 class="h-10"></h4>

    <div class="bg-white rounded-lg shadow-md gap-5 p-5">
        <button id="btnTambahAsalGerbang" class="px-6 py-1 bg-yellow-400 border-2 border-blue-950 rounded-lg font-bold mb-5" >Tambah Data</button>
        <table id="tblAsalGerbang" class="display" style="width:100%">
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



    <!-- Modal Tambah Asal Gerbang -->
<div id="modalAsalGerbang" class="fixed inset-0 z-50 hidden items-center justify-center">
  <div id="modalBackdrop" class="absolute inset-0 bg-black/50"></div>

  <div class="relative bg-white rounded-xl w-full max-w-lg shadow-xl">
    <div class="flex items-center justify-between p-4 border-b">
        <h3 id="modalTitle" class="font-semibold text-blue-950">Tambah Asal Gerbang</h3>
        <button id="btnCloseModal" class="p-2 hover:bg-gray-100 rounded-lg" aria-label="Tutup">✕</button>
    </div>

    <form id="formAsalGerbang" class="p-5 space-y-4">
        <input type="hidden" id="formMode" value="create">
        <input type="hidden" id="original_id" value="">
        <input type="hidden" id="original_ruas_id" value="">
        <input type="hidden" id="original_ruas_nama" value="">


    <div>
        <label for="ruas_id_modal" class="mb-1 block font-medium text-sm text-blue-950">
            Ruas
        </label>
        <select
            id="ruas_id_modal"
            name="ruas_id_modal"
            class="w-full"
            style="width: 100%;"
        ></select>
    </div>

      <div>
        <label for="id_asal_gerbang" class="mb-1 block font-medium text-sm text-blue-950">
          ID Asal Gerbang
        </label>
        <input
          type="text"
          id="id_asal_gerbang"
          name="id_asal_gerbang"
          class="px-3 py-2 border border-gray-300 rounded-lg text-blue-950 w-full focus:ring-2 focus:ring-blue-950"
          required
        />
      </div>

      <div>
        <label for="nama_asal_gerbang" class="mb-1 block font-medium text-sm text-blue-950">
          Nama Asal Gerbang
        </label>
        <input
          type="text"
          id="nama_asal_gerbang"
          name="nama_asal_gerbang"
          class="px-3 py-2 border border-gray-300 rounded-lg text-blue-950 w-full focus:ring-2 focus:ring-blue-950"
          required
        />
      </div>

      <div class="flex justify-end gap-3 pt-2">
        <button type="button" id="btnCancelModal"
                class="px-4 py-2 border rounded-lg text-blue-950 hover:bg-gray-100">
          Batal
        </button>
        <button type="submit" id="btnSaveAsalGerbang"
                class="px-4 py-2 bg-blue-950 text-white rounded-lg hover:opacity-90">
          Simpan
        </button>
      </div>
    </form>
  </div>
</div>





</x-app-layout>