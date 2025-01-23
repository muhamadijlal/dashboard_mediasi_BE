<?php

namespace App\Models\Services\MIY;

use Illuminate\Support\Facades\DB;

class MIYEntrance
{
    public function getSourceCompare($start_date, $end_date)
    {
        $query = DB::connection('integrator')
                                ->table('lalin_entrance')
                                ->select("TanggalLaporan as tgl_lap", "GerbangId as gerbang_id", "GarduId as gardu_id", "Golongan as golongan", "Shift as shift",  DB::raw('COUNT(*) as jumlah_data'))
                                ->whereBetween('TanggalLaporan', [$start_date, $end_date])
                                ->groupBy("TanggalLaporan", "GerbangId", "GarduId", "Shift", "Golongan");

        return $query;
    }

    public function getSourceSync($request)
    {
        $query = DB::connection('integrator')
                    ->table("lalin_entrance")
                    ->select('TanggalLaporan as tgl_lap', 'GerbangId as gerbang_id', 'GarduId as gardu_id', 'Golongan as gol_sah', 'Shift as shift', 'NoResi as no_resi', 'WaktuTransaksiEntrance as tgl_transaksi', DB::raw('0 as tarif'), 'Perioda as perioda', 'MetodeTransaksi as metoda_bayar_sah','JenisNotran as jenis_notran',DB::raw('"-" as etoll_hash'))
                    ->where('TanggalLaporan', $request->tanggal)
                    ->where('GerbangId', $request->gerbang_id)
                    ->where('Golongan', $request->golongan)
                    ->where('GarduId', $request->gardu_id)
                    ->where('Shift', $request->shift);

        return $query;
    }
}
