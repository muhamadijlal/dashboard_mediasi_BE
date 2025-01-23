<?php

namespace App\Models\Services\DB;

use Illuminate\Support\Facades\DB;

class DBEntranceExit
{
    public function getSourceCompare($start_date, $end_date, $schema)
    {
        $tbltransaksi_entrance = DB::connection('integrator_pgsql')
                                    ->table($schema.'.tbltransaksi_entry')
                                    ->select("tanggal_siklus as tgl_lap", "idgerbang as gerbang_id", "gardu as gardu_id", "gol as golongan", "shift",  DB::raw('COUNT(*) as jumlah_data'))
                                    ->whereBetween('tanggal_siklus', [$start_date, $end_date])
                                    ->groupBy("tanggal_siklus", "idgerbang", "gardu", "shift", "gol");

        $tbltransaksi_exit = DB::connection('integrator_pgsql')
                                    ->table($schema.'.tbltransaksi_exit')
                                    ->select("tanggal_siklus as tgl_lap", "gerbang_keluar as gerbang_id", "gardu as gardu_id", "gol as golongan", "shift",  DB::raw('COUNT(*) as jumlah_data'))
                                    ->whereBetween('tanggal_siklus', [$start_date, $end_date])
                                    ->groupBy("tanggal_siklus", "gerbang_keluar", "gardu", "shift", "gol");

        $query = $tbltransaksi_exit->unionAll($tbltransaksi_entrance);

        return $query;
    }

    public function getSourceSync($request, $schema)
    {
        $tbltransaksi_entrance = DB::connection('integrator_pgsql')
                                    ->table($schema.".tbltransaksi_entry")
                                    ->select(
                                        'tanggal_siklus as tgl_lap', 
                                        'idgerbang as gerbang_id', 
                                        'gardu as gardu_id', 
                                        'gol as gol_sah', 
                                        'shift', 
                                        'resi as no_resi', 
                                        'waktu_transaksi as tgl_transaksi', 
                                        DB::raw('0 as tarif'), 
                                        'periode', 
                                        'jenis_transaksi as metoda_bayar_sah', 
                                        DB::raw('NULL as jenis_notran'), 
                                        DB::raw('NULL as etoll_hash')
                                    )
                                    ->where('tanggal_siklus', $request->tanggal)
                                    ->where('idgerbang', $request->gerbang_id)
                                    ->where('gol', $request->golongan)
                                    ->where('gardu', $request->gardu_id)
                                    ->where('shift', $request->shift);

        $tbltransaksi_exit = DB::connection('integrator_pgsql')
                                ->table($schema.".tbltransaksi_exit")
                                ->select(
                                    'tanggal_siklus as tgl_lap', 
                                    'gerbang_keluar as gerbang_id', 
                                    'gardu as gardu_id', 
                                    'gol as gol_sah', 
                                    'shift', 
                                    'resi as no_resi', 
                                    'waktu_trans_exit as tgl_transaksi', 
                                    'tarif', 
                                    'periode as perioda', 
                                    'jenis_transaksi as metoda_bayar_sah', 
                                    DB::raw('NULL as jenis_notran'), 
                                    'etoll_hash'
                                )                       
                                ->where('tanggal_siklus', $request->tanggal)
                                ->where('gerbang_keluar', $request->gerbang_id)
                                ->where('gol', $request->golongan)
                                ->where('gardu', $request->gardu_id)
                                ->where('shift', $request->shift);

        $query = $tbltransaksi_exit->unionAll($tbltransaksi_entrance);
        
        return $query;
    }
}
