<?php

namespace App\Models\Services\DB;

use Illuminate\Support\Facades\DB;

class DBEntrance
{
    public function getSourceCompare($start_date, $end_date, $schema)
    {
        $query = DB::connection('integrator_pgsql')
                    ->table((string)$schema.'.tbltransaksi_entry')
                    ->select(
                        'tanggal_siklus as tgl_lap',
                        'idgerbang as gerbang_id',
                        'gardu as gardu_id',
                        'gol as golongan',
                        'shift',
                        DB::raw('COUNT(*) as jumlah_data')
                    )
                    ->whereBetween('tanggal_siklus', [$start_date, $end_date])
                    ->whereNotIn('jenis_transaksi', ['91', '92'])
                    ->groupBy('tanggal_siklus', 'idgerbang', 'gardu', 'shift', 'gol');

        return $query;
    }

    public function getSourceSync($request, $schema)
    {
        $query = DB::connection('integrator_pgsql')
                    ->table((string)$schema.'.tbltransaksi_entry')
                    ->select(
                        'tanggal_siklus as tgl_lap', 
                        'gardu as gardu_id',
                        'shift',
                        'periode as perioda',
                        'jenis_transaksi as metoda_bayar_sah',
                        'resi as no_resi',
                        'gol as gol_sah',
                        DB::raw('0 as tarif'),
                        'waktu_transaksi as tgl_transaksi',
                        'waktu_transaksi as tgl_entrance',
                        'jenis_dinas',
                        'no_card as etoll_id',
                        DB::raw('0 as saldo'), 
                        DB::raw('NULL as etoll_hash'),  // Replacing empty string with NULL
                        DB::raw('idgerbang as gerbang_masuk'),
                        'idgerbang as gerbang_id',
                        'idkspt as KsptId',
                        'idpultol as PLTId',
                        DB::raw('NULL as jenis_notran'),  // Replacing empty string with NULL
                    )
                    ->whereNotIn('jenis_transaksi', ['91', '92'])
                    ->where('tanggal_siklus', [$request->start_date, $request->end_date])
                    ->where('idgerbang', $request->gerbang_id)
                    ->where('gol', $request->golongan)
                    ->where('gardu', $request->gardu_id)
                    ->where('shift', $request->shift);

        return $query;
    }
}
