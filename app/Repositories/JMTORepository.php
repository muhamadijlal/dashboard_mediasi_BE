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
                                ->select("tgl_lap", "gerbang_id", DB::raw("gol_sah as golongan"), "gardu_id", "shift", DB::raw('COUNT(id) as jumlah_data_mediasi'))
                                ->whereBetween('tgl_lap', [$start_date, $end_date])
                                ->groupBy("tgl_lap", "gerbang_id", "gardu_id", "shift", "gol_sah");

            // Query untuk tabel integrator
            $query_integrator = DB::connection('integrator')
                                ->table("tbl_transaksi_deteksi")
                                ->select("tgl_lap", "gerbang_id", "gardu_id", DB::raw("gol as golongan"), "shift", DB::raw('COUNT(id) as jumlah_data_integrator'))
                                ->whereBetween('tgl_lap', [$start_date, $end_date])
                                ->groupBy("tgl_lap", "gerbang_id", "gardu_id", "shift", "gol");

            // Mendapatkan hasil dari query mediasi dan integrator
            $results_mediasi = $query_mediasi->get();
            $results_integrator = $query_integrator->get();

            // Gabungkan hasilnya
            $final_results = [];

            foreach ($results_mediasi as $index => $mediasi) {
                // Cari data yang sesuai di integrator
                // $integrator_data = $results_integrator->first(function ($integrator) use ($mediasi) {
                //     return $integrator->tgl_lap == $mediasi->tgl_lap && 
                //         $integrator->gerbang_id == $mediasi->gerbang_id && 
                //         $integrator->shift == $mediasi->shift;
                // });

                $gardu_id = strlen($results_integrator[$index]->gardu_id) > 2 ? substr($results_integrator[$index]->gardu_id, 2, 4) : $results_integrator[$index]->gardu_id;

                $integrator_data = $results_integrator[$index]->tgl_lap == $mediasi->tgl_lap && 
                                    $results_integrator[$index]->gerbang_id == $mediasi->gerbang_id && 
                                    $gardu_id == $mediasi->gardu_id && 
                                    $results_integrator[$index]->shift == $mediasi->shift;

                // Hitung jumlah integrator dan selisih
                $jumlah_data_integrator = $integrator_data ? $results_integrator[$index]->jumlah_data_integrator : 0;
                $selisih = $jumlah_data_integrator - $mediasi->jumlah_data_mediasi;

                // Membuat objek stdClass untuk hasil
                $final_result = new \stdClass();
                $final_result->tanggal = $mediasi->tgl_lap;
                $final_result->gerbang_id = $mediasi->gerbang_id;
                $final_result->golongan = $mediasi->golongan;
                $final_result->gardu_id = $mediasi->gardu_id;
                $final_result->shift = $mediasi->shift;
                $final_result->jumlah_data_mediasi = $mediasi->jumlah_data_mediasi;
                $final_result->jumlah_data_integrator = $jumlah_data_integrator;
                $final_result->selisih = $selisih;

                // Tambahkan filter berdasarkan nilai selisih
                if ($isSelisih === '*') {
                    // Tampilkan semua data, tidak ada filter
                    $final_results[] = $final_result;
                } elseif ($isSelisih === '1' && $selisih > 0) {
                    // Tampilkan data dengan selisih > 0
                    $final_results[] = $final_result;
                } elseif ($isSelisih === '0' && $selisih == 0) {
                    // Tampilkan data dengan selisih == 0
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
                        ->table('tbl_transaksi_deteksi')
                        ->select("gardu_id", "shift", "perioda", "no_resi", "gol", "metoda_bayar_sah", "notran_id_sah", "etoll_hash", "tarif")
                        ->where('tgl_lap', $request->tanggal)
                        ->where('ruas_id', $request->ruas_id)
                        ->where("gerbang_id", $request->gerbang_id)
                        ->where("gol", $request->golongan)
                        ->where("gardu_id", $request->gerbang_id.$request->gardu_id)
                        ->where("shift", $request->shift);

            return $query;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage()); 
        }
    }
}
