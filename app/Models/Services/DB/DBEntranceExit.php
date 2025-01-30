<?php

namespace App\Models\Services\DB;

use Illuminate\Support\Facades\DB;

class DBEntranceExit
{
    public function getSourceCompare($start_date, $end_date, $schema)
    {
        $tbltransaksi_entrance = DB::connection('integrator_pgsql')
                                    ->table((string)$schema.'.tbltransaksi_entry')
                                    ->select('tanggal_siklus as tgl_lap',
                                        'idgerbang as gerbang_id',
                                        'gardu as gardu_id',
                                        'gol as golongan',
                                        'shift',
                                        DB::raw('COUNT(*) as jumlah_data')
                                    )
                                    ->whereBetween('tanggal_siklus', [(string)$start_date, (string)$end_date])
                                    ->whereNotIn('jenis_transaksi', [(string)'91', (string)'92'])
                                    ->groupBy('tanggal_siklus', 'idgerbang', 'gardu', 'shift', 'gol');

        $tbltransaksi_exit = DB::connection('integrator_pgsql')
                                    ->table((string)$schema.'.tbltransaksi_exit')
                                    ->select('tanggal_siklus as tgl_lap',
                                        'gerbang_keluar as gerbang_id',
                                        'gardu as gardu_id',
                                        'gol as golongan',
                                        'shift',  DB::raw('COUNT(*) as jumlah_data')
                                    )
                                    ->whereBetween('tanggal_siklus', [(string)$start_date, (string)$end_date])
                                    ->whereNotIn('jenis_transaksi', [(string)'91', (string)'92'])
                                    ->groupBy('tanggal_siklus', 'gerbang_keluar', 'gardu', 'shift', 'gol');

        $query = $tbltransaksi_exit->unionAll($tbltransaksi_entrance);

        return $query;
    }

    public function getSourceSync($request, $schema)
    {
        $tbltransaksi_entrance = DB::connection('integrator_pgsql')
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
                                    ->whereNotIn('jenis_transaksi', [(string)'91', (string)'92'])
                                    ->where('tanggal_siklus', [(string)$request->start_date, (string)$request->end_date])
                                    ->where('idgerbang', (string)$request->gerbang_id)
                                    ->where('gol', (string)$request->golongan)
                                    ->where('gardu', (string)$request->gardu_id)
                                    ->where('shift', (string)$request->shift);

        $tbltransaksi_exit = DB::connection('integrator_pgsql')
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

        $query = $tbltransaksi_exit->unionAll($tbltransaksi_entrance);


        return $query;
    }
}
