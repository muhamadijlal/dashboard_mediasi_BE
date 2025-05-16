<?php

namespace App\Repositories;

use App\Models\DatabaseConfig;
use App\Models\Integrator;
use App\Models\Services\DB\DBServices;
use App\Models\Utils;
use Illuminate\Support\Facades\DB;

class DBRepository
{
    public function getDataTransakiDetail(string $ruas_id, string $gerbang_id, ?string $start_date = null, ?string $end_date = null, ?string $shift_id = null, ?string $golongan_id = null, ?string $metoda_bayar_id = null)
    {
        try {
            DatabaseConfig::switchConnection($ruas_id, $gerbang_id);

            $query = DB::connection('mediasi')
                ->table("jid_transaksi_deteksi")
                ->select(
                    "gardu_id",
                    "shift",
                    "tgl_lap",
                    "tgl_transaksi",
                    "perioda",
                    "no_resi",
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

    public function getDataRekapAT4(string $ruas_id, string $gerbang_id, ?string $start_date = null, ?string $end_date = null)
    {
        try {
            DatabaseConfig::switchConnection($ruas_id, $gerbang_id);

            $query = DB::connection('mediasi')
                ->table("jid_rekap_at4_db")
                ->select(
                    "Shift",
                    "Tanggal",
                    "Shift",
                    "Tunai",
                    "DinasOpr",
                    "DinasMitra",
                    "DinasKary",
                    "eMandiri",
                    "eBri",
                    "eBni",
                    "eBca",
                    "eFlo",
                    "RpTunai",
                    DB::raw("0 AS RpDinasOpr"),
                    "RpDinasMitra",
                    "RpDinasKary",
                    "RpeMandiri",
                    "RpeBri",
                    "RpeBni",
                    "RpeBca",
                    "RpeFlo"
                )
                ->whereBetween('Tanggal', [$start_date, $end_date]);

            return $query;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function getDataCompare($ruas_id, $gerbang_id, $start_date, $end_date, $isSelisih)
    {
        try {
            DatabaseConfig::switchMultiConnection($ruas_id, $gerbang_id, 'integrator_pgsql');
            $services = Integrator::services($ruas_id, $gerbang_id);
            $database_schema = Integrator::schema($ruas_id, $gerbang_id);

            // Query untuk tabel mediasi
            $query_mediasi = DB::connection('mediasi')
                ->table('jid_transaksi_deteksi')
                ->select(
                    'tgl_lap',
                    'gerbang_id',
                    'metoda_bayar_sah as metoda_bayar',
                    'shift',
                    DB::raw('COUNT(*) as jumlah_data'),
                    DB::raw("SUM(tarif) as jumlah_tarif_mediasi")
                )
                ->whereNotNull("ruas_id")
                ->whereBetween('tgl_lap', [$start_date, $end_date])
                ->where("gerbang_id", $gerbang_id * 1)
                ->groupBy('tgl_lap', 'gerbang_id', 'metoda_bayar_sah', 'shift')
                ->orderBy("metoda_bayar_sah", "ASC");

            // Query untuk tabel integrator
            $query_integrator = $services->getSourceCompare($start_date, $end_date, $database_schema, $gerbang_id);

            // Mendapatkan hasil dari query mediasi dan integrator
            $results_mediasi = $query_mediasi->get();
            $results_integrator = $query_integrator->get();
            
            $final_results = DBServices::mappingDataDB($ruas_id, $results_integrator, $results_mediasi, $isSelisih);

            return $final_results;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function getDataSync($request)
    {
        try {
            DatabaseConfig::switchConnection($request->ruas_id, $request->gerbang_id, 'integrator_pgsql');
            $services = Integrator::services($request->ruas_id, $request->gerbang_id);
            $database_schema = Integrator::schema($request->ruas_id, $request->gerbang_id);
            $query = $services->getSourceSync($request, $database_schema);

            return $query;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function syncData($request)
    {
        DatabaseConfig::switchConnection($request->ruas_id, $request->gerbang_id);
        DB::connection('mediasi')->beginTransaction();

        try {
            $mappedData = [];

            $this->getDataSync($request)->orderBy('tgl_lap', 'ASC')->chunk(1000, function($chunkData) use(&$mappedData) {
                foreach ($chunkData as $item) {
                    list($metoda_bayar, $jenis_notran) =  Utils::transmetod_db_to_jid($item->metoda_bayar_sah);

                    $row = [
                        'gerbang_id'       => $item->gerbang_id,
                        'gardu_id'         => $item->gardu_id,
                        'tgl_lap'          => $item->tgl_lap,
                        'shift'            => $item->shift,
                        'perioda'          => $item->perioda,
                        'no_resi'          => $item->no_resi,
                        'gol_sah'          => $item->gol_sah,
                        'etoll_id'         => $item->etoll_id,
                        'metoda_bayar_sah' => $metoda_bayar,
                        'jenis_notran'     => $jenis_notran,
                        'tgl_transaksi'    => $item->tgl_transaksi,
                        'kspt_id'          => $item->KsptId,
                        'pultol_id'        => $item->PLTId,
                        'tgl_entrance'     => $item->tgl_entrance,
                        'etoll_hash'       => $item->etoll_hash,
                        'tarif'            => $item->tarif,
                        'sisa_saldo'       => $item->saldo
                    ];

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
                        'create_at', 'update_at'
                    ]
                );

                // Reset mappedData for next chunk
                $mappedData = [];
            });

            // Jika semua operasi berhasil, commit transaksi
            DB::connection('mediasi')->commit();

            return response()->json(['message' => "Syncronize data success!"], 201);
        } catch (\Exception $e) {
            DB::connection('mediasi')->rollBack();
            throw new \Exception($e->getMessage());
        }
    }
}
