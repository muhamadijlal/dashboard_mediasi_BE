<?php

namespace App\Models\Services\DB;

use App\Models\Utils;
use Illuminate\Support\Facades\DB;

class DBOpen
{
    public function getSourceCompare($start_date, $end_date, $schema, $gerbang_id)
    {
        $query = DB::connection('integrator_pgsql')
            ->table((string)$schema . '.tbltransaksi_open')
            ->select(
                'tanggal_siklus as tgl_lap',
                'idgerbang as gerbang_id',
                'jenis_transaksi as metoda_bayar',
                'jenis_dinas',
                'shift',
                DB::raw('COUNT(id) as jumlah_data'),
                DB::raw('SUM(tarif) as jumlah_tarif_integrator')
            )
            // ->whereNotNull('ruas_id')
            ->where("idgerbang", $gerbang_id * 1)
            ->whereBetween('tanggal_siklus', [(string)$start_date, (string)$end_date])
            ->whereNotIn('jenis_transaksi', ['91', '92'])
            ->groupBy('tanggal_siklus', 'jenis_dinas',  'idgerbang', 'jenis_transaksi', 'shift');

        return $query;
    }

    public function getSourceSync($request, $schema)
    {
        $whereClause = Utils::metode_bayar_jidDB($request->metoda_bayar, $request->jenis_notran, $request->jenis_dinas);

        $query = DB::connection('integrator_pgsql')
            ->table((string)$schema . '.tbltransaksi_open')
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
            ->whereBetween('tanggal_siklus', [(string)$request->start_date, (string)$request->end_date])
            ->where('idgerbang', $request->gerbang_id * 1)
            ->where('shift', $request->shift);

        if ($whereClause) {
            $query->whereRaw($whereClause);
        }

        return $query;
    }
}
