<?php

namespace App\Repositories;

use App\Models\DatabaseConfig;
use Illuminate\Support\Facades\DB;

class DBRepository
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
                    ->table("jid_rekap_at4_db")
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
            $query_integrator = DB::connection('integrator_pgsql')
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
                $jumlah_data = $index !== false ? $max->jumlah_data : 0;
                $selisih = ($index !== false) ? $jumlah_data - $minResults[$index]->jumlah_data : 0;

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
}
