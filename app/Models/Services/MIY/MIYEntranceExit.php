<?php

namespace App\Models\Services\MIY;

use App\Models\Utils;
use Illuminate\Support\Facades\DB;

class MIYEntranceExit
{
    public function getSourceCompare($start_date, $end_date, $gerbang_id)
    {
        // Query untuk lalin_settlement
        $lalin_settlement = DB::connection('integrator')
            ->table('lalin_settlement')
            ->select(
                "TanggalLaporan as tgl_lap",
                "GerbangId as gerbang_id",
                "MetodeTransaksi as metoda_bayar",
                "Shift as shift",
                DB::raw('COUNT(*) as jumlah_data'),
                DB::raw("SUM(Tarif) as jumlah_tarif_integrator")
            )
            ->whereBetween('TanggalLaporan', [$start_date, $end_date])
            ->where("GerbangId", $gerbang_id * 1)
            ->groupBy("TanggalLaporan", "GerbangId", "MetodeTransaksi", "Shift");

        // Query untuk lalin_entrance
        $lalin_entrance = DB::connection('integrator')
            ->table('lalin_entrance')
            ->select(
                "TanggalLaporan as tgl_lap",
                "GerbangId as gerbang_id",
                "MetodeTransaksi as metoda_bayar",
                "Shift as shift",
                DB::raw('COUNT(*) as jumlah_data'),
                DB::raw("0 as jumlah_tarif_integrator")
            )
            ->whereBetween('TanggalLaporan', [$start_date, $end_date])
            ->where("GerbangId", $gerbang_id * 1)
            ->groupBy("TanggalLaporan", "GerbangId", "MetodeTransaksi", "Shift");

        // Menggabungkan keduanya dengan unionAll
        $query = $lalin_settlement->unionAll($lalin_entrance);

        // Mengembalikan hasil query
        return $query;
    }

    public function getSourceSync($request)
    {
        $whereClause = Utils::metode_bayar_jidMIY($request->metoda_bayar);

        $lalin_settlement = DB::connection('integrator')
            ->table("lalin_settlement")
            ->select(
                'TanggalLaporan as tgl_lap',
                'AsalGerbangId as asal_gerbang_id',
                'GerbangId as gerbang_id',
                'GarduId as gardu_id',
                'Golongan as gol_sah',
                'NomorKartu',
                'Shift as shift',
                'NoResi as no_resi',
                'WaktuTransaksiEntrance as tgl_transaksi',
                'WaktuTransaksiEntrance as tgl_entrance',
                'Tarif as tarif',
                'Perioda as perioda',
                'KsptId',
                'Saldo',
                'PLTId',
                'MetodeTransaksi as metoda_bayar_sah',
                'JenisNotran as jenis_notran',
                'ValidasiNotran as validasi_notran',
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
                DB::raw('NULL as KodeIntegrator')
            )
            ->whereBetween('TanggalLaporan', [$request['start_date'], $request['end_date']])
            ->where('GerbangId', $request['gerbang_id'] * 1)
            ->where('Shift', $request['shift']);

        $lalin_entrance = DB::connection('integrator')
            ->table("lalin_entrance")
            ->select(
                'TanggalLaporan as tgl_lap',
                DB::raw('NULL as asal_gerbang_id'),
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
                DB::raw('NULL as etoll_hash'),
                DB::raw('NULL as KodeInvestor1'),
                DB::raw('NULL as TarifInvestor1'),
                DB::raw('NULL as KodeInvestor2'),
                DB::raw('NULL as TarifInvestor2'),
                DB::raw('NULL as KodeInvestor3'),
                DB::raw('NULL as TarifInvestor3'),
                DB::raw('NULL as KodeInvestor4'),
                DB::raw('NULL as TarifInvestor4'),
                DB::raw('NULL as KodeInvestor5'),
                DB::raw('NULL as TarifInvestor5'),
                DB::raw('NULL as KodeInvestor6'),
                DB::raw('NULL as TarifInvestor6'),
                DB::raw('NULL as KodeInvestor7'),
                DB::raw('NULL as TarifInvestor7'),
                DB::raw('NULL as KodeInvestor8'),
                DB::raw('NULL as TarifInvestor8'),
                DB::raw('NULL as KodeInvestor9'),
                DB::raw('NULL as TarifInvestor9'),
                DB::raw('NULL as KodeInvestor10'),
                DB::raw('NULL as TarifInvestor10'),
                DB::raw('NULL as KodeIntegrator')
            )
            ->whereBetween('TanggalLaporan', [$request['start_date'], $request['end_date']])
            ->where('GerbangId', $request['gerbang_id'] * 1)
            ->where('Shift', $request['shift']);

        if ($whereClause) {
            $lalin_settlement->whereRaw($whereClause);
            $lalin_entrance->whereRaw($whereClause);
        }

        // Menggabungkan keduanya dengan unionAll
        $query = $lalin_settlement->unionAll($lalin_entrance);

        return $query;
    }
}
