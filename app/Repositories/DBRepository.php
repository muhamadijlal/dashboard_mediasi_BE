<?php

namespace App\Repositories;

use App\Models\DatabaseConfig;
use App\Models\Integrator;
use Illuminate\Support\Facades\DB;

class DBRepository
{
    public function getDataTransakiDetail(string $ruas_id, string $gerbang_id, ?string $start_date=null, ?string $end_date=null)
    {
        try {
            DatabaseConfig::switchConnection($ruas_id, $gerbang_id);

            $query = DB::connection('mediasi')
                        ->table("jid_transaksi_deteksi")
                        ->select("gardu_id",
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

    public function getDataRekapAT4(string $ruas_id, string $gerbang_id, ?string $start_date=null, ?string $end_date=null)
    {
        try {
            DatabaseConfig::switchConnection($ruas_id, $gerbang_id);

            $query = DB::connection('mediasi')
                    ->table("jid_rekap_at4_db")
                    ->select("Shift",
                        "Tunai",
                        "DinasOpr",
                        "DinasMitra",
                        "DinasKary",
                        "eMandiri",
                        "eBri",
                        "eBni",
                        "eBca",
                        "eFlo",
                        "RpTunai", DB::raw("0 AS RpDinasOpr"),
                        "RpDinasMitra" ,"RpDinasKary",
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

    public function getDataCompare(string $ruas_id, string $gerbang_id, string $start_date=null, string $end_date=null, string $isSelisih)
    {
        try {
            DatabaseConfig::switchMultiConnection($ruas_id, $gerbang_id, 'integrator_pgsql');
            $services = Integrator::services($ruas_id, $gerbang_id);
            $database_schema = Integrator::schema($ruas_id, $gerbang_id);


            // Query untuk tabel mediasi
            $query_mediasi = DB::connection('mediasi')
                                ->table("jid_transaksi_deteksi")
                                ->select("tgl_lap",
                                    "gerbang_id", DB::raw("gol_sah as golongan"),
                                    "gardu_id",
                                    "shift", DB::raw('COUNT(id) as jumlah_data')
                                )
                                ->whereBetween('tgl_lap', [$start_date, $end_date])
                                ->groupBy("tgl_lap", "gerbang_id", "gardu_id", "shift", "gol_sah");

            // Query untuk tabel integrator
            $query_integrator = $services->getSourceCompare($start_date, $end_date, $database_schema);

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
                                $mediasi->shift == $integrator->shift &&
                                $mediasi->golongan == $integrator->golongan;
                        });

                // Hitung jumlah integrator dan selisih
                $jumlah_data = $integrator->jumlah_data;
                $selisih = $jumlah_data - (($index !== false) ? $results_mediasi[$index]->jumlah_data : 0);
        

                // Membuat objek stdClass untuk hasil
                $final_result = new \stdClass();
                $final_result->tanggal = $integrator->tgl_lap;
                $final_result->gerbang_id = $integrator->gerbang_id;
                $final_result->golongan = $integrator->golongan;
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

            foreach ($result as $dataItem) {
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
                    gerbang_id = VALUES(gerbang_id),
                    gardu_id = VALUES(gardu_id),
                    gol_sah = VALUES(gol_sah),
                    tgl_lap = VALUES(tgl_lap),
                    shift = VALUES(shift),
                    no_resi = VALUES(no_resi),
                    tgl_transaksi = VALUES(tgl_transaksi)
                ";

                $result = $this->metoda_bayar_sah($dataItem->metoda_bayar_sah, $dataItem->jenis_dinas);

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
                    $result[0], # metoda bayar sah
                    $result[1], # jenis notran
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

    private function metoda_bayar_sah($metodaBayarSah, $jenisDinas = 0, $jenisNotran = 0) {
        // Define payment map for different payment methods
        $paymentMap = [
            11 => ["21", 1],
            12 => ["21", 1],
            14 => ["22", 1],
            15 => ["22", 1],
            9 => ["23", 1],
            16 => ["23", 1],
            17 => ["23", 1],
            18 => ["24", 1],
            19 => ["24", 1],
            5 => ["25", 1],
            6 => ["25", 1],
            31 => ["28", 1],
            32 => ["28", 1],
            60 => ["28", 1],
            61 => ["28", 1],
            1 => ["40", 1],
            2 => ["40", 1],
            20 => ["11", 1],
            21 => [
                1 => ["11", 1],
                2 => ["12", 1],
                3 => ["13", 1],
                20 => ["11", 1],
                21 => ["12", 1],
                22 => ["13", 1],
                50 => ["11", 1],
                51 => ["12", 1],
                52 => ["13", 1],
            ],
            22 => ["13", 1],
            80 => ["40", 3],
            81 => ["0", 2],
            3 => ["0", 2],
            82 => ["40", 3],
            83 => ["48", 2],
            84 => ["48", 2],
        ];
    
        // Convert input values to integers
        $metodaBayarSah = (int) $metodaBayarSah;
        $jenisDinas = (int) $jenisDinas;
        $jenisNotran = (int) $jenisNotran;
    
        // Handle specific case for metodaBayarSah 20
        if ($metodaBayarSah === 20) {
            // Always returns ["11", 1]
            return $paymentMap[20];
        }
    
        // Handle nested mapping for metodaBayarSah 21
        if ($metodaBayarSah === 21) {
            if (isset($paymentMap[21][$jenisDinas])) {
                return $paymentMap[21][$jenisDinas];
            } else {
                // Throw an exception if jenis_dinas is not found
                throw new \Exception("jenis_dinas {$jenisDinas} not found for metoda_bayar_sah 21.");
            }
        }
    
        // Update payment method and transaction type based on mappings
        if (isset($paymentMap[$metodaBayarSah])) {
            if (is_array($paymentMap[$metodaBayarSah])) {
                list($metodaBayarSah, $jenisNotran) = $paymentMap[$metodaBayarSah];
            } else {
                // Ensure that `jenis_dinas` is valid for the nested dictionary
                if (isset($paymentMap[$metodaBayarSah][$jenisDinas])) {
                    list($metodaBayarSah, $jenisNotran) = $paymentMap[$metodaBayarSah][$jenisDinas];
                } else {
                    throw new \Exception("jenis_dinas {$jenisDinas} not found for metoda_bayar_sah {$metodaBayarSah}.");
                }
            }
        } else {
            throw new \Exception("metoda_bayar_sah {$metodaBayarSah} not found in payment_map.");
        }
    
        // Update transaction type based on specific conditions
        if ($jenisNotran === 81) {
            $jenisNotran = 2;
        } elseif (in_array($jenisNotran, [80, 82])) {
            $jenisNotran = 3;
        }
    
        return [$metodaBayarSah, $jenisNotran];
    }    
}
