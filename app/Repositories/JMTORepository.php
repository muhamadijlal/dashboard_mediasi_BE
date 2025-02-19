<?php

namespace App\Repositories;

use App\Models\DatabaseConfig;
use App\Models\Utils;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class JMTORepository
{
    public function getDataTransakiDetail(string $ruas_id, string $gerbang_id, ?string $start_date=null, ?string $end_date=null)
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

    public function getDataRekapAT4(string $ruas_id, string $gerbang_id, ?string $start_date=null, ?string $end_date=null)
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

    public function getDataCompare(string $ruas_id, string $gerbang_id, string $start_date=null, string $end_date=null, string $isSelisih)
    {
        try {
            DatabaseConfig::switchMultiConnection($ruas_id, $gerbang_id, 'integrator');

            // Query untuk tabel mediasi
            $query_mediasi = DB::connection('mediasi')
                                ->table("jid_transaksi_deteksi")
                                ->select("tgl_lap", "gerbang_id", "shift", 'metoda_bayar_sah as metoda_bayar', DB::raw('COUNT(id) as jumlah_data'), DB::raw("SUM(tarif) as jumlah_tarif_mediasi"))
                                ->whereNotNull('ruas_id')
                                ->whereBetween('tgl_lap', [$start_date, $end_date])
                                ->where("gerbang_id", $gerbang_id*1)
                                ->groupBy("tgl_lap", "gerbang_id", "metoda_bayar_sah", "shift");

            // Query untuk tabel integrator
            $query_integrator = DB::connection('integrator')
                                ->table("jid_transaksi_deteksi")
                                ->select("tgl_lap", "gerbang_id", "shift", 'metoda_bayar_sah as metoda_bayar', DB::raw('COUNT(id) as jumlah_data'), DB::raw("SUM(tarif) as jumlah_tarif_integrator"))
                                ->whereNotNull('ruas_id')
                                ->whereBetween('tgl_lap', [$start_date, $end_date])
                                ->where("gerbang_id", $gerbang_id*1)
                                ->groupBy("tgl_lap", "gerbang_id", "metoda_bayar_sah", "shift");

            // Mendapatkan hasil dari query mediasi dan integrator
            $results_mediasi = $query_mediasi->get();
            $results_integrator = $query_integrator->get();

            // Gabungkan hasilnya
            $final_results = [];

            foreach($results_integrator as $integrator) {
                $index = $results_mediasi->search(function($mediasi) use($integrator) {
                    return $mediasi->tgl_lap == $integrator->tgl_lap && 
                        $mediasi->gerbang_id == $integrator->gerbang_id &&
                        $mediasi->metoda_bayar == $integrator->metoda_bayar &&
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
                $final_result->metoda_bayar_name = Utils::metode_bayar_jid($integrator->metoda_bayar);
                $final_result->shift = $integrator->shift;
                $final_result->jumlah_data_integrator = $jumlah_data ?? 0;
                $final_result->jumlah_data_mediasi = ($index !== false) ? $results_mediasi[$index]->jumlah_data : 0;
                $final_result->selisih = $selisih;
                $final_result->jumlah_tarif_integrator = $integrator->jumlah_tarif_integrator;
                $final_result->jumlah_tarif_mediasi = $results_mediasi[$index]->jumlah_tarif_mediasi;

                if ($isSelisih === '*') {
                    $final_results[] = $final_result;
                } elseif ($isSelisih === '1' && $selisih > 0) {
                    $final_results[] = $final_result;
                } elseif ($isSelisih === '0' && $selisih == 0) {
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

            $query = DB::connection('integrator')
                        ->table('jid_transaksi_deteksi')
                        ->select('ruas_id',
                            'asal_gerbang_id',
                            'gerbang_id',
                            'gardu_id',
                            'tgl_lap',
                            'shift',
                            'perioda',
                            'no_resi',
                            'gol_sah',
                            'etoll_id',
                            'metoda_bayar_sah',
                            'jenis_notran',
                            'tgl_transaksi',
                            'kspt_id',
                            'pultol_id',
                            'tgl_entrance',
                            'etoll_hash',
                            'tarif',
                            'sisa_saldo',
                            'trf1',
                            'inv1',
                            'trf2',
                            'inv2',
                            'trf3',
                            'inv3',
                            'trf4',
                            'inv4',
                            'trf5',
                            'inv5',
                            'trf6',
                            'inv6',
                            'trf7',
                            'inv7',
                            'trf8',
                            'inv8',
                            'trf9',
                            'inv9',
                            'trf10',
                            'inv10',
                            'KodeIntegrator',
                            'create_at',
                            'update_at'
                        )
                        ->whereBetween('tgl_lap', [$request->start_date, $request->end_date])
                        ->where('ruas_id', $request->ruas_id)
                        ->where("gerbang_id", $request->gerbang_id * 1)
                        ->where("metoda_bayar_sah", $request->metoda_bayar)
                        ->where("shift", $request->shift);

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
                        KodeIntegrator,
                        create_at,
                        update_at
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
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
                    $dataItem->asal_gerbang_id, 
                    $dataItem->gerbang_id, 
                    $dataItem->gardu_id, 
                    $dataItem->tgl_lap, 
                    $dataItem->shift, 
                    $dataItem->perioda, 
                    $dataItem->no_resi, 
                    $dataItem->gol_sah, 
                    $dataItem->etoll_id, 
                    $dataItem->metoda_bayar_sah, 
                    $dataItem->jenis_notran, 
                    $dataItem->tgl_transaksi, 
                    $dataItem->kspt_id, 
                    $dataItem->pultol_id, 
                    $dataItem->tgl_entrance, 
                    $dataItem->etoll_hash, 
                    $dataItem->tarif, 
                    $dataItem->sisa_saldo, 
                    $dataItem->trf1,
                    $dataItem->inv1,
                    $dataItem->trf2,
                    $dataItem->inv2,
                    $dataItem->trf3,
                    $dataItem->inv3,
                    $dataItem->trf4,
                    $dataItem->inv4,
                    $dataItem->trf5,
                    $dataItem->inv5,
                    $dataItem->trf6,
                    $dataItem->inv6,
                    $dataItem->trf7,
                    $dataItem->inv7,
                    $dataItem->trf8,
                    $dataItem->inv8,
                    $dataItem->trf9,
                    $dataItem->inv9,
                    $dataItem->trf10,
                    $dataItem->inv10,
                    $dataItem->KodeIntegrator,
                    $dataItem->create_at,
                    $dataItem->update_at
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
