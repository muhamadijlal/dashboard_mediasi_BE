<?php

namespace App\Repositories;

use App\Models\DatabaseConfig;
use App\Models\Integrator;
use App\Models\Services\MIY\MIYServices;
use App\Models\Utils;
use Illuminate\Support\Facades\DB;

class MIYRepository
{
    public function getDataTransakiDetail($ruas_id, $gerbang_id, $start_date, $end_date)
    {
        try {
            DatabaseConfig::switchConnection($ruas_id, $gerbang_id);

            $query = DB::connection('mediasi')
                ->table("jid_transaksi_deteksi")
                ->select("gardu_id", "shift", "perioda", "no_resi", "gol_sah", "metoda_bayar_sah", "jenis_notran as validasi_notran", "etoll_hash", "tarif")
                ->whereBetween('tgl_lap', values: [$start_date, $end_date]);

            return $query;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function getDataRekapAT4($ruas_id, $gerbang_id, $start_date, $end_date)
    {
        try {
            DatabaseConfig::switchConnection($ruas_id, $gerbang_id);

            $query = DB::connection('mediasi')
                ->table("jid_rekap_at4_miy")
                ->select("Shift", "Tunai", "DinasOpr", "DinasMitra", "DinasKary", "eMandiri", "eBri", "eBni", "eBca", "eFlo", "RpTunai", DB::raw("0 AS RpDinasOpr"), "RpDinasMitra", "RpDinasKary", "RpeMandiri", "RpeBri", "RpeBni", "RpeBca", "RpeFlo")
                ->whereBetween('Tanggal', [$start_date, $end_date]);

            return $query;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function getDataCompare($ruas_id, $gerbang_id, $start_date, $end_date, $isSelisih)
    {
        try {
            DatabaseConfig::switchMultiConnection($ruas_id, $gerbang_id, 'integrator');
            $services = Integrator::services($ruas_id, $gerbang_id);

            // Query untuk tabel mediasi
            $query_mediasi = DB::connection('mediasi')
                ->table("jid_transaksi_deteksi")
                ->select(
                    "tgl_lap",
                    "gerbang_id",
                    "metoda_bayar_sah as metoda_bayar",
                    "shift",
                    DB::raw('COUNT(id) as jumlah_data'),
                    DB::raw('SUM(tarif) as jumlah_tarif_mediasi')
                )
                ->whereNotNull("ruas_id")
                ->whereBetween('tgl_lap', [$start_date, $end_date])
                ->where("gerbang_id", $gerbang_id * 1)
                ->groupBy("tgl_lap", "gerbang_id", "metoda_bayar_sah", "shift");

            $query_integrator = $services->getSourceCompare($start_date, $end_date, $gerbang_id);

            // Mendapatkan hasil dari query mediasi dan integrator
            $results_mediasi = $query_mediasi->get();
            $results_integrator = $query_integrator->get();

            $final_results = MIYServices::mappingDataMIY($results_integrator, $results_mediasi, $isSelisih);

            return $final_results;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function getDataSync($request)
    {
        try {
            DatabaseConfig::switchConnection($request->ruas_id, $request->gerbang_id, 'integrator');
            $services = Integrator::services($request->ruas_id, $request->gerbang_id);
            $query = $services->getSourceSync($request);

            return $query;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function syncData($request)
    {
        // Switch to the correct database connection based on the request parameters
        DatabaseConfig::switchConnection($request->ruas_id, $request->gerbang_id);

        // Begin a transaction on the "mediasi" connection
        DB::connection('mediasi')->beginTransaction();

        try {
            // Fetch the data to be synced
            $data = $this->getDataSync($request);
            $result = $data->get();

            if (count($result) === 0) {
                throw new \Exception("Data empty cannot sync");
            }

            foreach ($result as $dataItem) {
                list($metoda_bayar, $jenis_notran) = Utils::transmetod_miy_to_jid($dataItem->metoda_bayar_sah);

                $query = "INSERT INTO jid_transaksi_deteksi(
                            asal_gerbang_id,
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
                            sisa_saldo,
                            trf1,
                            inv1,
                            trf2,
                            inv2,
                            trf3,
                            inv3,
                            trf4,
                            inv4,
                            trf5,
                            inv5,
                            trf6,
                            inv6,
                            trf7,
                            inv7,
                            trf8,
                            inv8,
                            trf9,
                            inv9,
                            trf10,
                            inv10,
                            KodeIntegrator
                        )
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
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
                    $this->asalGerbang($dataItem->asal_gerbang_id ?? NULL),
                    $dataItem->gerbang_id,
                    $dataItem->gardu_id,
                    $dataItem->tgl_lap,
                    $dataItem->shift,
                    $dataItem->perioda,
                    $dataItem->no_resi,
                    $dataItem->gol_sah,
                    $this->add_zero_cardnum($dataItem->NomorKartu),
                    $metoda_bayar,
                    $jenis_notran, # jenis notran
                    $dataItem->tgl_transaksi,
                    $dataItem->KsptId,
                    $dataItem->PLTId,
                    $dataItem->tgl_entrance,
                    $dataItem->etoll_hash,
                    $dataItem->tarif,
                    $dataItem->Saldo,
                    $dataItem->TarifInvestor1 ?? NULL,
                    $dataItem->KodeInvestor1 ?? NULL,
                    $dataItem->TarifInvestor2 ?? NULL,
                    $dataItem->KodeInvestor2 ?? NULL,
                    $dataItem->TarifInvestor3 ?? NULL,
                    $dataItem->KodeInvestor3 ?? NULL,
                    $dataItem->TarifInvestor4 ?? NULL,
                    $dataItem->KodeInvestor4 ?? NULL,
                    $dataItem->TarifInvestor5 ?? NULL,
                    $dataItem->KodeInvestor5 ?? NULL,
                    $dataItem->TarifInvestor6 ?? NULL,
                    $dataItem->KodeInvestor6 ?? NULL,
                    $dataItem->TarifInvestor7 ?? NULL,
                    $dataItem->KodeInvestor7 ?? NULL,
                    $dataItem->TarifInvestor8 ?? NULL,
                    $dataItem->KodeInvestor8 ?? NULL,
                    $dataItem->TarifInvestor9 ?? NULL,
                    $dataItem->KodeInvestor9 ?? NULL,
                    $dataItem->TarifInvestor10 ?? NULL,
                    $dataItem->KodeInvestor10 ?? NULL,
                    $dataItem->KodeIntegrator ?? NULL
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

    private function asalGerbang($asalGerbangId)
    {
        return in_array($asalGerbangId, [NULL, "Null", "0", "", 0]) ? 0 : $asalGerbangId;
    }

    private function add_zero_cardnum($cardNumber)
    {
        if ($cardNumber == '' ||  $cardNumber == NULL) {
            return '';
        }

        $cardNumber = (string)$cardNumber;
        if (substr($cardNumber, 0, 2) == '14') {
            return "0" . (string)$cardNumber;
        }

        return $cardNumber;
    }
}
