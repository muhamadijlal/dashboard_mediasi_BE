<?php

namespace App\Models\Services\MIY;

use Illuminate\Support\Facades\DB;

class MIYEntrance
{
    public function getSourceCompare($start_date, $end_date)
    {
        $query = DB::connection('integrator')
                                ->table('lalin_entrance')
                                ->select("TanggalLaporan as tgl_lap",
                                    "GerbangId as gerbang_id",
                                    "GarduId as gardu_id",
                                    "Golongan as golongan",
                                    "Shift as shift",
                                    DB::raw('COUNT(*) as jumlah_data')
                                )
                                ->whereNotNull('ruas_id')
                                ->whereBetween('TanggalLaporan', [$start_date, $end_date])
                                ->groupBy("TanggalLaporan", "GerbangId", "GarduId", "Shift", "Golongan");

        return $query;
    }

    public function getSourceSync($request)
    {
        $query = DB::connection('integrator')
                    ->table("lalin_entrance")
                    ->select('TanggalLaporan as tgl_lap',
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
                    ->where('GerbangId', $request['gerbang_id']*1)
                    ->where('Golongan', $request['golongan'])
                    ->where('GarduId', $request['gardu_id'])
                    ->where('Shift', $request['shift']);

        return $query;
    }
}
