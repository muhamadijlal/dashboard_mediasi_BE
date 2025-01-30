<?php

namespace App\Models\Services\DB;

use Illuminate\Support\Facades\DB;

class DBExit
{
    public function getSourceCompare($start_date, $end_date, $schema)
    {
        $query = DB::connection('integrator_pgsql')
                    ->table((string)$schema.'.tbltransaksi_exit')
                    ->select('tanggal_siklus as tgl_lap',
                        'gerbang_keluar as gerbang_id',
                        'gardu as gardu_id',
                        'gol as golongan',
                        'shift',
                        DB::raw('COUNT(*) as jumlah_data')
                    )
                    ->whereBetween('tanggal_siklus', [(string)$start_date, (string)$end_date])
                    ->whereNotIn('jenis_transaksi', [(string)'91', (string)'92'])
                    ->groupBy('tanggal_siklus', 'gerbang_keluar', 'gardu', 'shift', 'gol');

        return $query;
    }

    public function getSourceSync($request, $schema)
    {
        $query = DB::connection('integrator_pgsql')
                    ->table((string)$schema.'.tbltransaksi_exit')
                    ->select(
                        'tanggal_siklus as tgl_lap',
                        'gardu as gardu_id',
                        'shift',
                        'periode as perioda',
                        'jenis_transaksi as metoda_bayar_sah',
                        'resi as no_resi',
                        'gol as gol_sah',
                        'tarif',
                        'waktu_trans_exit as tgl_transaksi',
                        'waktu_trans_entry as tgl_entrance',
                        'jenis_dinas',
                        'no_card as etoll_id',
                        'saldo',
                        'etoll_hash',
                        'gerbang_masuk',
                        'gerbang_keluar as gerbang_id',
                        'idkspt as KsptId',
                        'idpultol as PLTId',
                        DB::raw('NULL as jenis_notran')  // Replacing empty string with NULL
                    )
                    ->whereNotIn('jenis_transaksi', [(string)'91', (string)'92'])
                    ->where('tanggal_siklus', [(string)$request->start_date, (string)$request->end_date])
                    ->where('gerbang_keluar', (string)$request->gerbang_id)
                    ->where('gol', (string)$request->golongan)
                    ->where('gardu', (string)$request->gardu_id)
                    ->where('shift', (string)$request->shift);

        return $query;
    }
}
