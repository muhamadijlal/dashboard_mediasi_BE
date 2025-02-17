<?php

namespace App\Models\Services\DB;

use Illuminate\Support\Facades\DB;

class DBOpen
{
    public function getSourceCompare($start_date, $end_date, $schema)
    {
        $query = DB::connection('integrator_pgsql')
                    ->table((string)$schema.'.tbltransaksi_open')
                    ->select('tanggal_siklus as tgl_lap',
                        'idgerbang as gerbang_id',
                        'gardu as gardu_id',
                        'shift',
                        DB::raw('COUNT(*) as jumlah_data')
                    )
                    // ->whereNotNull('ruas_id')
                    ->whereBetween('tanggal_siklus', [(string)$start_date, (string)$end_date])
                    ->whereNotIn('jenis_transaksi', ['91', '92'])
                    ->groupBy('tanggal_siklus', 'idgerbang', 'gardu', 'shift');

        return $query;
    }

    public function getSourceSync($request, $schema)
    {
        $query = DB::connection('integrator_pgsql')
                    ->table((string)$schema.'.tbltransaksi_open')
                    ->select(
                        'tanggal_siklus as tgl_lap',
                        'gardu as gardu_id',
                        'shift',
                        'periode as perioda',
                        'jenis_transaksi as metoda_bayar_sah',
                        'resi as no_resi',
                        'gol as gol_sah',
                        'tarif',
                        'waktu_transaksi as tgl_transaksi',
                        'waktu_transaksi as tgl_entrance',
                        'jenis_dinas',
                        'no_card as etoll_id',
                        'saldo',
                        'etoll_hash',
                        'idgerbang as gerbang_masuk',
                        'idgerbang as gerbang_id',
                        'idkspt as KsptId',
                        'idpultol as PLTId',
                        DB::raw('NULL as jenis_notran')
                    )
                    ->whereNotIn('jenis_transaksi', ['91', '92'])
                    ->whereBetween('tanggal_siklus', [(string)$request->start_date, (string)$request->end_date])
                    ->where('idgerbang', $request->gerbang_id*1)
                    ->where('gardu', $request->gardu_id)
                    ->where('shift', $request->shift);

        return $query;
    }
}
