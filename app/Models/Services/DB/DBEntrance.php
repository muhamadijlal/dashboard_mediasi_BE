<?php

namespace App\Models\Services\DB;

use Illuminate\Support\Facades\DB;

class DBEntrance
{
    public function getSourceCompare($start_date, $end_date, $schema)
    {
        $query = DB::connection('integrator_pgsql')
                    ->table($schema.'.tbltransaksi_entry')
                    ->select("tanggal_siklus as tgl_lap", "idgerbang as gerbang_id", "gardu as gardu_id", "gol as golongan", "shift",  DB::raw('COUNT(*) as jumlah_data'))
                    ->whereBetween('tanggal_siklus', [$start_date, $end_date])
                    ->groupBy("tanggal_siklus", "idgerbang", "gardu", "shift", "gol");

        return $query;
    }

    public function getSourceSync($request, $schema)
    {
        $query = DB::connection('integrator_pgsql')
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
                        'periode as perioda', 
                        'jenis_transaksi as metoda_bayar_sah', 
                        DB::raw('NULL as jenis_notran'), 
                        DB::raw('NULL as etoll_hash')
                    )                    
                    ->where('tanggal_siklus', $request->tanggal)
                    ->where('idgerbang', $request->gerbang_id)
                    ->where('gol', $request->golongan)
                    ->where('gardu', $request->gardu_id)
                    ->where('shift', $request->shift);

        return $query;
    }
}
