<?php

namespace App\Repositories;

use App\Models\DatabaseConfig;
use App\Models\Utils;
use Illuminate\Support\Facades\DB;
use App\Models\Services\JMTO\JMTOServices;

class JMTORepository
{
    public function getDataTransakiDetail($ruas_id, $gerbang_id, $start_date, $end_date)
    {
        try {
            DatabaseConfig::switchConnection($ruas_id, $gerbang_id);

            $query = DB::connection('mediasi')
                        ->table("jid_transaksi_deteksi")
                        ->select("gardu_id", "shift", "perioda", "no_resi", "gol_sah", "metoda_bayar_sah", "validasi_notran", "etoll_hash", "tarif")
                        ->whereBetween('tgl_lap', [$start_date, $end_date]);

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
                    ->table("jid_rekap_at4")
                    ->select("Shift", "Tunai", "DinasOpr", "DinasMitra", "DinasKary", "eMandiri", "eBri", "eBni", "eBca", "eFlo", "RpTunai", DB::raw("0 AS RpDinasOpr"), "RpDinasMitra" ,"RpDinasKary", "RpeMandiri", "RpeBri", "RpeBni", "RpeBca", "RpeFlo")
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

            // Query untuk tabel mediasi
            $query_mediasi = DB::connection('mediasi')
                                ->table("jid_transaksi_deteksi")
                                ->select("tgl_lap",
                                    "gerbang_id",
                                    "jenis_notran",
                                    "shift",
                                    "metoda_bayar_sah as metoda_bayar",
                                    DB::raw("COUNT(id) as jumlah_data"),
                                    DB::raw("SUM(tarif) as jumlah_tarif_mediasi")
                                )
                                ->whereNotNull('ruas_id')
                                ->whereBetween('tgl_lap', [$start_date, $end_date])
                                ->where("gerbang_id", $gerbang_id*1)
                                ->groupBy("tgl_lap", "gerbang_id", "jenis_notran", "metoda_bayar_sah", "shift");

            // Query untuk tabel integrator
            $query_integrator = DB::connection('integrator')
                                ->table("tbl_transaksi_deteksi")
                                ->select("tgl_lap", 
                                    "ktp_jenis_id as jenis_ktp",
                                    "gerbang_id",
                                    "shift",
                                    "metoda_bayar_id as metoda_bayar",
                                    "notran_id_sah as jenis_notran",
                                    DB::raw("COUNT(id) as jumlah_data"),
                                    DB::raw("SUM(tarif) as jumlah_tarif_integrator")
                                )
                                ->whereNotNull("ruas_id")
                                ->whereBetween("tgl_lap", [$start_date, $end_date])
                                ->where("gerbang_id", $gerbang_id*1)
                                ->groupBy("tgl_lap",
                                    "notran_id_sah",
                                    "ktp_jenis_id",
                                    "gerbang_id",
                                    "metoda_bayar_id",
                                    "shift"
                                );

            // Mendapatkan hasil dari query mediasi dan integrator
            $results_mediasi = $query_mediasi->get();
            $results_integrator = $query_integrator->get();

            $mergeResults = JMTOServices::mergeMandiriPayMethod($results_integrator);

            $final_results = [];

            foreach($mergeResults as $integrator) {
                list($metodaBayar, $jenisNotran) = Utils::metoda_bayar_sah($integrator->metoda_bayar, $integrator->jenis_notran, $integrator->jenis_ktp);

                $index = $results_mediasi->search(function($mediasi) use($integrator, $metodaBayar, $jenisNotran) {
                    return $mediasi->tgl_lap == $integrator->tgl_lap &&
                        $mediasi->gerbang_id == $integrator->gerbang_id &&
                        $mediasi->jenis_notran == $jenisNotran &&
                        $mediasi->metoda_bayar == $metodaBayar &&
                        $mediasi->shift == $integrator->shift;
                });

                // Hitung jumlah integrator dan selisih
                $jumlah_data = $integrator->jumlah_data;
                $selisih = $jumlah_data - (($index !== false) ? $results_mediasi[$index]->jumlah_data : 0);

                // Membuat objek stdClass untuk hasil
                $final_result = new \stdClass();
                $final_result->tanggal = $integrator->tgl_lap;
                $final_result->gerbang_id = $integrator->gerbang_id;
                $final_result->metoda_bayar = $integrator->metoda_bayar;
                $final_result->metoda_bayar_name = Utils::metode_bayar_jid($metodaBayar, $jenisNotran);
                $final_result->shift = $integrator->shift;
                $final_result->jumlah_data_integrator = $jumlah_data ?? 0;
                $final_result->jumlah_data_mediasi = ($index !== false) ? $results_mediasi[$index]->jumlah_data : 0;
                $final_result->selisih = $selisih;
                $final_result->jumlah_tarif_integrator = ($index !== false) ? $integrator->jumlah_tarif_integrator : 0;
                $final_result->jumlah_tarif_mediasi = ($index !== false) ? $results_mediasi[$index]->jumlah_tarif_mediasi : 0;

                if ($isSelisih === "*") {
                    $final_results[] = $final_result;
                } elseif ($isSelisih === "1" && $selisih > 0) {
                    $final_results[] = $final_result;
                } elseif ($isSelisih === "0" && $selisih == 0) {
                    $final_results[] = $final_result;
                }
            }

            return $final_results;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function getDataSync($request)
    {
        try {
            DatabaseConfig::switchConnection($request->ruas_id, $request->gerbang_id, 'integrator');

            $query = DB::connection("integrator")
                        ->table("tbl_transaksi_deteksi")
                        ->select("ruas_id",
                            "asal_gerbang_id",
                            "gerbang_id",
                            DB::raw("SUBSTRING(gardu_id, 3, 2) as gardu_id"),
                            "tgl_lap",
                            "shift",
                            "perioda",
                            "no_resi",
                            "gol_sah",
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
                    
                    // special case for metoda_bayar Mandiri (3, 13)
                    if((int)$request->metoda_bayar == 21) {
                        $query->whereIn("metoda_bayar_id", [13, 3]);
                    } else {
                        $query->where("metoda_bayar_id", $request->metoda_bayar);
                    }
            
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

            $cols = "";
            $variables = "";
            $investors = Utils::miy_investor($request->ruas_id);

            foreach ($investors as $idx => $_) {
                $cols .= "inv" . ($idx + 1) . ",";
                $variables .= " ?, ";
            }

            $variables = rtrim($variables, ", ");
            $cols = rtrim($cols, ", ");

            foreach ($result as $dataItem) {
                // Define the SQL query with placeholders for parameterized queries
                $query = "INSERT INTO jid_transaksi_deteksi (
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
                        trf1,
                        trf2,
                        trf3,
                        trf4,
                        trf5,
                        trf6,
                        trf7,
                        trf8,
                        trf9,
                        trf10,
                        create_at,
                        update_at,
                        $cols
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, $variables)
                    ON DUPLICATE KEY UPDATE 
                        ruas_id = VALUES(ruas_id),
                        gerbang_id = VALUES(gerbang_id),
                        gardu_id = VALUES(gardu_id),
                        tgl_lap = VALUES(tgl_lap),
                        shift = VALUES(shift),
                        no_resi = VALUES(no_resi),
                        tgl_transaksi = VALUES(tgl_transaksi),
                        inv1,
                        inv2,
                        inv3,
                        inv4,
                        inv5,
                        inv6,
                        inv7,
                        inv8,
                        inv9,
                        inv10
                    ";

                $metoda_bayar_sah = in_array($dataItem->metoda_bayar_sah, [13, 3]) ? 21 : $dataItem->metoda_bayar_sah;
                $jenis_notran = in_array($dataItem->metoda_bayar_sah, [13, 3]) ? 1 : $dataItem->jenis_notran;

                // Bind the data for the prepared statement
                $params = [
                    $dataItem->asal_gerbang_id, 
                    $dataItem->gerbang_id, 
                    $dataItem->gardu_id, 
                    $dataItem->tgl_lap, 
                    $dataItem->shift, 
                    $dataItem->perioda, 
                    $dataItem->no_resi,
                    $dataItem->gol_sah,
                    $dataItem->etoll_id, 
                    $metoda_bayar_sah, 
                    $jenis_notran, 
                    $dataItem->tgl_transaksi, 
                    $dataItem->kspt_id, 
                    $dataItem->pultol_id, 
                    $dataItem->tgl_entrance, 
                    $dataItem->etoll_hash, 
                    $dataItem->tarif,
                    $dataItem->trf1,
                    $dataItem->trf2,
                    $dataItem->trf3,
                    $dataItem->trf4,
                    $dataItem->trf5,
                    $dataItem->trf6,
                    $dataItem->trf7,
                    $dataItem->trf8,
                    $dataItem->trf9,
                    $dataItem->trf10,
                    $dataItem->datereceived,
                    $dataItem->datereceived,
                    ...$investors,  // spread the value
                ];

                // Execute the statement on the "mediasi" connection
                DB::connection('mediasi')->statement($query, $params);              
            }

            // If all operations were successful, commit the transaction
            DB::connection('mediasi')->commit();

            // Return success message
            return response()->json(['message' => "Syncronize data success!"], 201);
        } catch (\Exception $e) {
            // If an error occurs, roll back the transaction
            DB::connection('mediasi')->rollBack();
            // Throw the exception to be handled elsewhere
            throw new \Exception($e->getMessage());
        }
    }
}
