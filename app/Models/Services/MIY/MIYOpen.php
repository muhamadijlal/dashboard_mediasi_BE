<?php

namespace App\Models\Services\MIY;

use Illuminate\Support\Facades\DB;

class MIYOpen
{
    public function getSourceCompare($start_date, $end_date)
    {
        $query = DB::connection('integrator')
                                ->table('lalin_settlement')
                                ->select("TanggalLaporan as tgl_lap",
                                    "GerbangId as gerbang_id",
                                    "GarduId as gardu_id",
                                    "Golongan as golongan",
                                    "Shift as shift",
                                    DB::raw('COUNT(*) as jumlah_data')
                                )
                                ->whereBetween('TanggalLaporan', [$start_date, $end_date])
                                ->groupBy("TanggalLaporan", "GerbangId", "GarduId", "Shift", "Golongan");

        return $query;
    }

    public function getSourceSync($request)
    {
        $query = DB::connection('integrator')
                    ->table("lalin_settlement")
                    ->select('TanggalLaporan as tgl_lap',
                        'AsalGerbangId as asal_gerbang_id',
                        'GerbangId as gerbang_id',
                        'GarduId as gardu_id',
                        'Golongan as gol_sah',
                        'NomorKartu',
                        'Shift as shift',
                        'NoResi as no_resi',
                        'WaktuTransaksiExit as tgl_transaksi',
                        'Tarif as tarif',
                        'Perioda as perioda',
                        'KsptId',
                        'Saldo',
                        'PLTId',
                        'WaktuTransaksiEntrance as tgl_entrance',
                        'MetodeTransaksi as metoda_bayar_sah',
                        'JenisNotran as jenis_notran',
                        'EtollHash as etoll_hash',
                        'KodeInvestor1',
                        'TarifInvestor1',
                        'KodeInvestor2',
                        'TarifInvestor2',
                        'KodeInvestor3',
                        'TarifInvestor3',
                        'KodeInvestor4',
                        'TarifInvestor4',
                        'KodeInvestor5',
                        'TarifInvestor5',
                        'KodeInvestor6',
                        'TarifInvestor6',
                        'KodeInvestor7',
                        'TarifInvestor7',
                        'KodeInvestor8',
                        'TarifInvestor8',
                        'KodeInvestor9',
                        'TarifInvestor9',
                        'KodeInvestor10',
                        'TarifInvestor10',
                        DB::raw('" " as KodeIntegrator')
                    )
                    ->whereBetween('TanggalLaporan', [$request['start_date'], $request['end_date']])
                    ->where('GerbangId', $request['gerbang_id'])
                    ->where('Golongan', $request['golongan'])
                    ->where('GarduId', $request['gardu_id'])
                    ->where('Shift', $request['shift']);

        return $query;
    }
}
