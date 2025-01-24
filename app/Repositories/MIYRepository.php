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
                                ->select("tgl_lap", "gerbang_id", "gol_sah as golongan", "gardu_id", "shift", DB::raw('COUNT(id) as jumlah_data'))
                                ->whereBetween('tgl_lap', [$start_date, $end_date])
                                ->groupBy("tgl_lap", "gerbang_id", "gardu_id", "shift", "gol_sah");

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
        DatabaseConfig::switchConnection($request->ruas_id, $request->gerbang_id);
        DB::connection('mediasi')->beginTransaction();

        try {
            $data = $this->getDataSync($request);
            $result = $data->get();

            foreach ($result as $data) {
                $query = "INSERT INTO jid_transaksi_deteksi (ruas_id, gerbang_id, gardu_id, gol_sah, tgl_lap, shift, no_resi, tgl_transaksi) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                        ruas_id = VALUES(ruas_id), 
                        gerbang_id = VALUES(gerbang_id),
                        gardu_id = VALUES(gardu_id),
                        gol_sah = VALUES(gol_sah),
                        tgl_lap = VALUES(tgl_lap),
                        shift = VALUES(shift),
                        no_resi = VALUES(no_resi),
                        tgl_transaksi = VALUES(tgl_transaksi)";

                // Execute the statement
                DB::connection("mediasi")->statement($query, [$request->ruas_id, $data->gerbang_id, $data->gardu_id, $data->gol_sah, $data->tgl_lap, $data->shift, $data->no_resi, $data->tgl_transaksi]);              
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
