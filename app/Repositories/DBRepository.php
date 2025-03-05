<?php

namespace App\Repositories;

use App\Models\DatabaseConfig;
use App\Models\Integrator;
use App\Models\Services\DB\DBServices;
use App\Models\Utils;
use Illuminate\Support\Facades\DB;

class DBRepository
{
    public function getDataTransakiDetail(string $ruas_id, string $gerbang_id, ?string $start_date = null, ?string $end_date = null)
    {
        try {
            DatabaseConfig::switchConnection($ruas_id, $gerbang_id);

            $query = DB::connection('mediasi')
                ->table("jid_transaksi_deteksi")
                ->select(
                    "gardu_id",
                    "shift",
                    "perioda",
                    "no_resi",
                    "gol_sah",
                    "metoda_bayar_sah",
                    "validasi_notran",
                    "etoll_hash",
                    "tarif"
                )
                ->whereBetween('tgl_lap', [$start_date, $end_date]);

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
                    DB::raw('COUNT(id) as jumlah_data'),
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
            $data = $this->getDataSync($request);
            $result = $data->get();

            if (count($result) === 0) {
                throw new \Exception("Data empty cannot sync");
            }

            foreach ($result as $dataItem) {
                list($metoda_bayar, $jenis_notran) =  Utils::transmetod_db_to_jid($dataItem->metoda_bayar_sah);

                $query = "INSERT INTO jid_transaksi_deteksi(
                    gerbang_id,
                    gardu_id,
                    tgl_lap,
                    shift,
                    perioda,
                    no_resi,
                    gol_sah,
                    etoll_id,
                    metoda_bayar_sah,
                    jenis_notran,
                    tgl_transaksi,
                    kspt_id,
                    pultol_id,
                    tgl_entrance,
                    etoll_hash,
                    tarif,
                    sisa_saldo
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    ruas_id = VALUES(ruas_id),
                    gardu_id = VALUES(gardu_id),
                    shift = VALUES(shift),
                    no_resi = VALUES(no_resi),
                    metoda_bayar_sah = VALUES(metoda_bayar_sah),
                    gerbang_id = VALUES(gerbang_id),
                    tgl_lap = VALUES(tgl_lap),
                    tgl_transaksi = VALUES(tgl_transaksi)
                ";

                // Bind the data for the prepared statement
                $params = [
                    $dataItem->gerbang_id,
                    $dataItem->gardu_id,
                    $dataItem->tgl_lap,
                    $dataItem->shift,
                    $dataItem->perioda,
                    $dataItem->no_resi,
                    $dataItem->gol_sah,
                    $dataItem->etoll_id,
                    $metoda_bayar,
                    $jenis_notran,
                    $dataItem->tgl_transaksi,
                    $dataItem->KsptId,
                    $dataItem->PLTId,
                    $dataItem->tgl_entrance,
                    $dataItem->etoll_hash,
                    $dataItem->tarif,
                    $dataItem->saldo
                ];

                // Execute the statement
                DB::connection("mediasi")->statement($query, $params);
            }

            // Jika semua operasi berhasil, commit transaksi
            DB::connection('mediasi')->commit();

            return response()->json(['message' => "Syncronize data success!"], 201);
        } catch (\Exception $e) {
            DB::connection('mediasi')->rollBack();
            throw new \Exception($e->getMessage());
        }
    }
}
