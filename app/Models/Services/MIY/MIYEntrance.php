<?php

namespace App\Models\Services\MIY;

use Illuminate\Support\Facades\DB;

class MIYEntrance
{
    public function getSourceCompare($start_date, $end_date, $gerbang_id)
    {
        $query = DB::connection('integrator')
            ->table('lalin_entrance')
            ->select(
                "TanggalLaporan as tgl_lap",
                "GerbangId as gerbang_id",
                "MetodeTransaksi as metoda_bayar",
                "Shift as shift",
                "JenisNotran as jenis_notran",
                "ValidasiNotran as validasi_notran",
                DB::raw('COUNT(id) as jumlah_data'),
                DB::raw("SUM(Tarif) as jumlah_tarif_integrator")
            )
            ->whereBetween('TanggalLaporan', [$start_date, $end_date])
            ->where("GerbangId", $gerbang_id * 1)
            ->groupBy("TanggalLaporan", "JenisNotran", "ValidasiNotran", "GerbangId", "MetodeTransaksi", "Shift");

        return $query;
    }

    public function getSourceSync($request)
    {
        $query = DB::connection('integrator')
            ->table("lalin_entrance")
            ->select(
                'TanggalLaporan as tgl_lap',
                'GerbangId as gerbang_id',
                'GarduId as gardu_id',
                'Golongan as gol_sah',
                'NomorKartu',
                'Shift as shift',
                'NoResi as no_resi',
                'WaktuTransaksiEntrance as tgl_transaksi',
                'WaktuTransaksiEntrance as tgl_entrance',
                DB::raw('0 as tarif'),
                'Perioda as perioda',
                'KsptId',
                'Saldo',
                'PLTId',
                'MetodeTransaksi as metoda_bayar_sah',
                'JenisNotran as jenis_notran',
                DB::raw('"" as etoll_hash')
            )
            ->whereBetween('TanggalLaporan', [$request['start_date'], $request['end_date']])
            ->where('GerbangId', $request['gerbang_id'] * 1)
            ->where('MetodeTransaksi', $request['metoda_bayar'])
            ->where('Shift', $request['shift']);

        return $query;
    }
}
