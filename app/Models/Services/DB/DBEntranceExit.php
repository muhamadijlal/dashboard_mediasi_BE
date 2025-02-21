<?php

namespace App\Models\Services\DB;

use Illuminate\Support\Facades\DB;

class DBEntranceExit
{
    public function getSourceCompare($start_date, $end_date, $schema, $gerbang_id)
    {
        $tbltransaksi_entrance = DB::connection('integrator_pgsql')
                                    ->table((string)$schema.'.tbltransaksi_entry')
                                    ->select('tanggal_siklus as tgl_lap',
                                        'idgerbang as gerbang_id',
                                        'jenis_transkasi as metoda_bayar',
                                        'shift',
                                        'jenis_dinas',
                                        DB::raw("'' as jenis_notran"),
                                        DB::raw('COUNT(id) as jumlah_data'),
                                        DB::raw('0 as jumlah_tarif_integrator')
                                    )
                                    // ->whereNotNull('ruas_id')
                                    ->where("idgerbang", $gerbang_id*1)
                                    ->whereBetween('tanggal_siklus', [(string)$start_date, (string)$end_date])
                                    ->whereNotIn('jenis_transaksi', ['91', '92'])
                                    ->groupBy('tanggal_siklus', 'jenis_dinas', 'jenis_notran', 'idgerbang', 'jenis_transkasi', 'shift');

        $tbltransaksi_exit = DB::connection('integrator_pgsql')
                                    ->table((string)$schema.'.tbltransaksi_exit')
                                    ->select('tanggal_siklus as tgl_lap',
                                        'gerbang_keluar as gerbang_id',
                                        'jenis_transkasi as metoda_bayar',
                                        'shift',
                                        'jenis_dinas',
                                        DB::raw("'' as jenis_notran"),
                                        DB::raw('COUNT(id) as jumlah_data'),
                                        DB::raw('SUM(tarif) as jumlah_tarif_integrator')
                                    )
                                    // ->whereNotNull('ruas_id')
                                    ->where("idgerbang", $gerbang_id*1)
                                    ->whereBetween('tanggal_siklus', [(string)$start_date, (string)$end_date])
                                    ->whereNotIn('jenis_transaksi', ['91', '92'])
                                    ->groupBy('tanggal_siklus', 'jenis_dinas', 'jenis_notran', 'gerbang_keluar', 'jenis_transkasi', 'shift');

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
                                        DB::raw('NULL as etoll_hash'),
                                        DB::raw('idgerbang as gerbang_masuk'),
                                        'idgerbang as gerbang_id',
                                        'idkspt as KsptId',
                                        'idpultol as PLTId',
                                        DB::raw('NULL as jenis_notran'),
                                    )
                                    ->whereNotIn('jenis_transaksi', ['91', '92'])
                                    ->where('tanggal_siklus', [(string)$request->start_date, (string)$request->end_date])
                                    ->where('idgerbang', $request->gerbang_id*1)
                                    ->where('jenis_transaksi', $request->metoda_bayar)
                                    ->where('shift', $request->shift);

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
                                    DB::raw('NULL as jenis_notran')
                                )
                                ->whereNotIn('jenis_transaksi', ['91', '92'])
                                ->where('tanggal_siklus', [(string)$request->start_date, (string)$request->end_date])
                                ->where('gerbang_keluar', $request->gerbang_id*1)
                                ->where('jenis_transaksi', $request->metoda_bayar)
                                ->where('shift', $request->shift);

        $query = $tbltransaksi_exit->unionAll($tbltransaksi_entrance);


        return $query;
    }
}
