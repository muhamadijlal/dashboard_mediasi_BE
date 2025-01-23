<?php

namespace App\Repositories;

use App\Models\DatabaseConfig;
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
                                ->select("tgl_lap", "gerbang_id", DB::raw("gol_sah as golongan"), "gardu_id", "shift", DB::raw('COUNT(id) as jumlah_data'))
                                ->whereBetween('tgl_lap', [$start_date, $end_date])
                                ->groupBy("tgl_lap", "gerbang_id", "gardu_id", "shift", "gol_sah");

            // Query untuk tabel integrator
            $query_integrator = DB::connection('integrator')
                                ->table("jid_transaksi_deteksi")
                                ->select("tgl_lap", "gerbang_id", "gardu_id", DB::raw("gol_sah as golongan"), "shift", DB::raw('COUNT(id) as jumlah_data'))
                                ->whereBetween('tgl_lap', [$start_date, $end_date])
                                ->groupBy("tgl_lap", "gerbang_id", "gardu_id", "shift", "gol_sah");

            // Mendapatkan hasil dari query mediasi dan integrator
            $results_mediasi = $query_mediasi->get();
            $results_integrator = $query_integrator->get();

            // Menghitung panjang masing-masing koleksi
            $length_mediasi = $results_mediasi->count();
            $length_integrator = $results_integrator->count();

            // Memilih koleksi dengan panjang terbesar
            if ($length_mediasi > $length_integrator) {
                $maxResults = $results_mediasi;
                $minResults = $results_integrator;
            } else {
                $maxResults = $results_integrator;
                $minResults = $results_mediasi;
            }

            // Gabungkan hasilnya
            $final_results = [];

            foreach($maxResults as $max) {
                $index = $minResults->search(function($data) use($max) {
                            return $data->tgl_lap == $max->tgl_lap && 
                                    $data->gerbang_id == $max->gerbang_id &&
                                    $data->gardu_id == $max->gardu_id &&
                                    $data->shift == $max->shift &&
                                    $data->golongan == $max->golongan;
                        });

                // Hitung jumlah integrator dan selisih
                $jumlah_data = $max->jumlah_data;
                $selisih = $jumlah_data - (($index !== false) ? $minResults[$index]->jumlah_data : 0);

                // Membuat objek stdClass untuk hasil
                $final_result = new \stdClass();
                $final_result->tanggal = $max->tgl_lap ?? 0;
                $final_result->gerbang_id = $max->gerbang_id ?? 0;
                $final_result->golongan = $max->golongan ?? 0;
                $final_result->gardu_id = $max->gardu_id ?? 0;
                $final_result->shift = $max->shift ?? 0;
                $final_result->jumlah_data_integrator = $jumlah_data ?? 0;
                $final_result->jumlah_data_mediasi = ($index !== false) ? $minResults[$index]->jumlah_data : 0;
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

            $query = DB::connection('integrator')
                        ->table('jid_transaksi_deteksi')
                        ->select("ruas_id", "gerbang_id", "tgl_lap", "tgl_transaksi", "gardu_id", "shift", "perioda", "no_resi", "gol_sah", "metoda_bayar_sah", "jenis_notran", "etoll_hash", "tarif")
                        ->where('tgl_lap', $request->tanggal)
                        ->where('ruas_id', $request->ruas_id)
                        ->where("gerbang_id", $request->gerbang_id)
                        ->where("gol_sah", $request->golongan)
                        ->where("gardu_id", $request->gardu_id)
                        ->where("shift", $request->shift);

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
                DB::connection("mediasi")->statement($query, [$data->ruas_id, $data->gerbang_id, $data->gardu_id, $data->gol_sah, $data->tgl_lap, $data->shift, $data->no_resi, $data->tgl_transaksi]);              
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
