<?php

namespace App\Repositories;

use App\Models\DatabaseConfig;
use App\Models\Integrator;
use Illuminate\Support\Facades\DB;

class MIYRepository
{
    public function getDataTransakiDetail(string $ruas_id, string $gerbang_id, ?string $start_date=null, ?string $end_date=null)
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

    public function getDataRekapAT4(string $ruas_id, string $gerbang_id, ?string $start_date=null, ?string $end_date=null)
    {
        try {
            DatabaseConfig::switchConnection($ruas_id, $gerbang_id);

            $query = DB::connection('mediasi')
                    ->table("jid_rekap_at4_miy")
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
            $services = Integrator::services($ruas_id, $gerbang_id);

            // Query untuk tabel mediasi
            $query_mediasi = DB::connection('mediasi')
                                ->table("jid_transaksi_deteksi")
                                ->select("tgl_lap", "gerbang_id", "gardu_id", "shift", DB::raw('COUNT(id) as jumlah_data'))
                                ->whereBetween('tgl_lap', [$start_date, $end_date])
                                ->groupBy("tgl_lap", "gerbang_id", "gardu_id", "shift");

            $query_integrator = $services->getSourceCompare($start_date, $end_date);

            // Mendapatkan hasil dari query mediasi dan integrator
            $results_mediasi = $query_mediasi->get();
            $results_integrator = $query_integrator->get();

            // Gabungkan hasilnya
            $final_results = [];

            foreach($results_integrator as $integrator)
            {
                $index = $results_mediasi->search(function($mediasi) use($integrator) {
                    return $mediasi->tgl_lap == $integrator->tgl_lap && 
                        $mediasi->gerbang_id == $integrator->gerbang_id &&
                        $mediasi->gardu_id == $integrator->gardu_id &&
                        $mediasi->shift == $integrator->shift;
                });

                // Hitung jumlah integrator dan selisih
                $jumlah_data = $integrator->jumlah_data;
                $selisih = $jumlah_data - (($index !== false) ? $results_mediasi[$index]->jumlah_data : 0);

                // Membuat objek stdClass untuk hasil
                $final_result = new \stdClass();
                $final_result->tanggal = $integrator->tgl_lap;
                $final_result->gerbang_id = $integrator->gerbang_id;
                $final_result->gardu_id = $integrator->gardu_id;
                $final_result->shift = $integrator->shift;
                $final_result->jumlah_data_integrator = $jumlah_data ?? 0;
                $final_result->jumlah_data_mediasi = ($index !== false) ? $results_mediasi[$index]->jumlah_data : 0;
                $final_result->selisih = $selisih;

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
                            gerbang_id = VALUES(gerbang_id),
                            gardu_id = VALUES(gardu_id),
                            tgl_lap = VALUES(tgl_lap),
                            shift = VALUES(shift),
                            no_resi = VALUES(no_resi),
                            tgl_transaksi = VALUES(tgl_transaksi)
                        ";

                $result = $this->metoda_bayar_sah( $dataItem->metoda_bayar_sah, $dataItem->jenis_notran);
                
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
                    $result[0], # metoda bayar sah
                    $result[1], # jenis notran
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
        if($cardNumber == '' ||  $cardNumber == NULL) {
            return '';
        }

        $cardNumber = (string)$cardNumber;
        if(substr($cardNumber, 0, 2) == '14') {
            return "0".(string)$cardNumber;
        }

        return $cardNumber;
    }

    private function metoda_bayar_sah($metoda_bayar_sah, $jenis_notran) {
        $metode_transaksi = (int)$metoda_bayar_sah;
    
        $payment_map = [
            0 => ["48", "2"],
            1 => ["11", "1"],
            2 => ["40", "1"],
            3 => ["21", "1"],
            7 => ["40", "3"],
            8 => ["48", "2"],
            12 => ["12", "1"],
            13 => ["13", "1"],
            17 => ["11", "1"],
            18 => ["12", "1"],
            19 => ["13", "1"],
            20 => ["22", "1"],
            21 => ["23", "1"],
            23 => ["24", "1"],
            25 => ["25", "1"],
            29 => ["28", "1"],
        ];
    
        if ($metode_transaksi == 2 && $jenis_notran == "NAK") {
            return ["40", "3"];
        } elseif ($metode_transaksi == 2 && $jenis_notran == "NTK") {
            return ["48", "2"];
        } elseif (array_key_exists($metode_transaksi, $payment_map)) {
            return $payment_map[$metode_transaksi];
        }
    
        return null; // Optional: in case no match is found
    }
}
