<?php

namespace App\Repositories;

use App\Models\DatabaseConfig;
use App\Models\Utils;
use Illuminate\Support\Facades\DB;
use App\Models\Services\JMTO\JMTOServices;
use Illuminate\Database\Eloquent\Collection;

class JMTORepository
{
    public function getDataTransakiDetail($ruas_id, $gerbang_id, $start_date, $end_date, $shift_id, $golongan_id, $metoda_bayar_id)
    {
        try {
            DatabaseConfig::switchConnection($ruas_id, $gerbang_id);

            $query = DB::connection('mediasi')
                ->table("jid_transaksi_deteksi")
                ->select("gardu_id",
                    "shift",
                    "perioda",
                    "no_resi",
                    "tgl_lap",
                    "tgl_transaksi",
                    "gol_sah",
                    "metoda_bayar_sah",
                    "validasi_notran",
                    "etoll_hash",
                    "tarif"
                )
                ->whereBetween('tgl_lap', [$start_date, $end_date]);

                if($shift_id != '*') {
                    $query->where("shift", $shift_id);
                }

                if($golongan_id != '*') {
                    $query->where("gol_sah", $golongan_id);
                }

                if($metoda_bayar_id != '*') {
                    $query->where("metoda_bayar_sah", $metoda_bayar_id);
                }

            return $query;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function getDataRekapAT4($ruas_id, $gerbang_id,  $shift_id, $start_date, $end_date)
    {
        try {
            DatabaseConfig::switchConnection($ruas_id, $gerbang_id);

            $query = DB::connection('mediasi')
                ->table("jid_rekap_at4")
                ->select("Shift", "Tunai", "Tanggal", "DinasOpr", "DinasMitra", "DinasKary", "eMandiri", "eBri", "eBni", "eBca", "eFlo", "RpTunai", DB::raw("0 AS RpDinasOpr"), "RpDinasMitra", "RpDinasKary", "RpeMandiri", "RpeBri", "RpeBni", "RpeBca", "RpeFlo")
                ->whereBetween('Tanggal', [$start_date, $end_date]);

            if($shift_id && $shift_id != '*')
            {
                $query = $query->where('shift', $shift_id);
            }

            return $query;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function getDataCompare($ruas_id, $gerbang_id, $shift_id, $metoda_bayar_id, $start_date, $end_date, $isSelisih)
    {
        try {
            DatabaseConfig::switchMultiConnection($ruas_id, $gerbang_id, 'integrator');

            // Query untuk tabel mediasi
            $query_mediasi = DB::connection('mediasi')
                ->table("jid_transaksi_deteksi")
                ->select(
                    "tgl_lap",
                    "gerbang_id",
                    "shift",
                    "metoda_bayar_sah as metoda_bayar",
                    DB::raw("COUNT(*) as jumlah_data"),
                    DB::raw("SUM(tarif) as jumlah_tarif_mediasi")
                )
                ->whereNotNull('ruas_id')
                ->whereBetween('tgl_lap', [$start_date, $end_date])
                ->where("gerbang_id", $gerbang_id * 1)
                ->groupBy(
                    "tgl_lap",
                    "gerbang_id",
                    "metoda_bayar_sah",
                    "shift"
                );

            // Query untuk tabel integrator
            $query_integrator = DB::connection('integrator')
                ->table("tbl_transaksi_deteksi")
                ->select(
                    "tgl_lap",
                    "gerbang_id",
                    "shift",
                    "ktp_jenis_id",
                    "metoda_bayar_id as metoda_bayar",
                    DB::raw("COUNT(id) as jumlah_data"),
                    DB::raw("SUM(tarif) as jumlah_tarif_integrator")
                )
                ->whereNotNull("ruas_id")
                ->whereBetween("tgl_lap", [$start_date, $end_date])
                ->where("gerbang_id", $gerbang_id * 1)
                ->groupBy(
                    "tgl_lap",
                    "gerbang_id",
                    "ktp_jenis_id",
                    "metoda_bayar_id",
                    "shift"
                );

            // Mendapatkan hasil dari query mediasi dan integrator
            $results_mediasi = $query_mediasi->get();
            $results_integrator = $query_integrator->get();

            $final_results = JMTOServices::mappingDataJMTO($ruas_id, $shift_id, $metoda_bayar_id, $results_integrator, $results_mediasi, $isSelisih);

            return $final_results;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function getDataSync($request)
    {
        try {
            DatabaseConfig::switchConnection($request->ruas_id, $request->gerbang_id, 'integrator');

            $whereClause = Utils::metode_bayar_jidJMTO($request->metoda_bayar);

            $query = DB::connection("integrator")
                ->table("tbl_transaksi_deteksi")
                ->select(
                    "ruas_id",
                    "asal_gerbang_id",
                    "gerbang_id",
                    DB::raw("SUBSTRING(gardu_id, 3, 2) as gardu_id"),
                    "tgl_lap",
                    "shift",
                    "perioda",
                    "no_resi",
                    "gol_sah",
                    "ktp_jenis_id",
                    "ktp_sn as etoll_id",
                    "metoda_bayar_id as metoda_bayar_sah",
                    "notran_id_sah as jenis_notran",
                    "tgl_transaksi",
                    "kspt_id",
                    "pultol_id",
                    "tgl_entrance",
                    "etoll_hash",
                    "tarif",
                    "trf1",
                    "trf2",
                    "trf3",
                    "trf4",
                    "trf5",
                    "trf6",
                    "trf7",
                    "trf8",
                    "trf9",
                    "trf10",
                    "datereceived",
                )
                ->whereBetween("tgl_lap", [$request->start_date, $request->end_date])
                ->where("ruas_id", $request->ruas_id)
                ->where("gerbang_id", $request->gerbang_id * 1)
                ->where("shift", $request->shift);

                if ($whereClause) {
                    $query->whereRaw($whereClause);
                }

            return $query;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function syncData($request)
    {
        // Get investors as dynamic columns (inv1, inv2, ..., invN)
        $investors = Utils::jmto_investor($request->ruas_id);
        $invColumns = array_map(fn($idx) => 'inv' . ($idx + 1), array_keys($investors));

        // Switch DB connection
        DatabaseConfig::switchConnection($request->ruas_id, $request->gerbang_id);

        // Mulai transaksi
        DB::connection('mediasi')->beginTransaction();

        try {
            $mappedData = [];

            // Fetch data yang mau di-sync
            $this->getDataSync($request)->orderBy('tgl_lap', 'ASC')->chunk(1000, function ($chunkData) use (&$mappedData, $invColumns, $investors) {
                foreach ($chunkData as $item) {

                    list($metoda_bayar_sah, $jenis_notran) = Utils::transmetod_jmto_to_jid($item->metoda_bayar_sah, $item->ktp_jenis_id);

                    $row = [
                        'asal_gerbang_id'  => $item->asal_gerbang_id,
                        'gerbang_id'       => $item->gerbang_id,
                        'gardu_id'         => $item->gardu_id,
                        'tgl_lap'          => $item->tgl_lap,
                        'shift'            => $item->shift,
                        'perioda'          => $item->perioda,
                        'no_resi'          => $item->no_resi,
                        'gol_sah'          => $item->gol_sah,
                        'etoll_id'         => $item->etoll_id,
                        'metoda_bayar_sah' => $metoda_bayar_sah,
                        'jenis_notran'     => $jenis_notran,
                        'tgl_transaksi'    => $item->tgl_transaksi,
                        'kspt_id'          => $item->kspt_id,
                        'pultol_id'        => $item->pultol_id,
                        'tgl_entrance'     => $item->tgl_entrance,
                        'etoll_hash'       => $item->etoll_hash,
                        'tarif'            => $item->tarif,
                        'trf1'             => $item->trf1,
                        'trf2'             => $item->trf2,
                        'trf3'             => $item->trf3,
                        'trf4'             => $item->trf4,
                        'trf5'             => $item->trf5,
                        'trf6'             => $item->trf6,
                        'trf7'             => $item->trf7,
                        'trf8'             => $item->trf8,
                        'trf9'             => $item->trf9,
                        'trf10'            => $item->trf10,
                        'create_at'        => $item->datereceived,
                        'update_at'        => $item->datereceived,
                    ];

                    // Tambahkan investor ke row
                    foreach ($investors as $idx => $invValue) {
                        $row['inv' . ($idx + 1)] = $invValue;
                    }

                    $mappedData[] = $row;
                }

                // Upsert per chunk (1000 data sekali proses)
                DB::connection('mediasi')->table('jid_transaksi_deteksi')->upsert(
                    $mappedData,
                    ['gerbang_id', 'gardu_id', 'tgl_lap', 'shift', 'perioda', 'no_resi', 'tgl_transaksi'], // unique key
                    [ // columns to update on duplicate
                        'asal_gerbang_id', 'gol_sah', 'etoll_id', 'metoda_bayar_sah', 'jenis_notran',
                        'kspt_id', 'pultol_id', 'tgl_entrance', 'etoll_hash', 'tarif',
                        'trf1', 'trf2', 'trf3', 'trf4', 'trf5', 'trf6', 'trf7', 'trf8', 'trf9', 'trf10',
                        'create_at', 'update_at',
                        ...$invColumns // e.x. inv1, inv2, ..., etc.
                    ]
                );

                // Reset mappedData for next chunk
                $mappedData = [];
            });

            // Commit jika tidak error
            DB::connection('mediasi')->commit();

            return response()->json(['message' => "Syncronize data success!"], 201);
        } catch (\Exception $e) {
            // Rollback jika error
            DB::connection('mediasi')->rollBack();
            throw new \Exception($e->getMessage());
        }
    }
}
