<?php

namespace App\Repositories;

use App\Traits\ResponseAPI;
use App\Interfaces\RekapDataInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use App\Models\DatabaseConfig;
use Illuminate\Pagination\Paginator;

class MIYRepository implements RekapDataInterface
{
    use ResponseAPI;

    public function getDataTransakiDetail(string $ruas_id, string $gerbang_id, ?string $start_date=null, ?string $end_date=null, ?int $limit = 10)
    {
        try {
            DatabaseConfig::switchConnection($ruas_id, $gerbang_id);

            $query = DB::connection('mediasi')
                        ->table("jid_transaksi_deteksi")
                        ->select("gardu_id", "shift", "perioda", "no_resi", "gol_sah as gol", "metoda_bayar_sah as metoda_bayar", "jenis_notran as notran", "etoll_hash", "tarif")
                        ->whereBetween('tgl_lap', [$start_date, $end_date]);
            
            $data = $query->paginate($limit);
            
            return $this->success("Get data success!", $data);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function getDataRekapAT4(string $ruas_id, string $gerbang_id, ?string $start_date=null, ?string $end_date=null, ?int $limit = 10)
    {
        try {
            DatabaseConfig::switchConnection($ruas_id, $gerbang_id);

            $query = DB::connection('mediasi')
                    ->table("jid_rekap_at4_miy")
                    ->select("Shift", "Tunai", "DinasOpr", "DinasMitra", "DinasKary", "eMandiri", "eBri", "eBni", "eBca", "eFlo", "RpTunai", "0 AS RpDinasOpr", "RpDinasMitra" ,"RpDinasKary", "RpMandiri", "RpBri", "RpBni", "RpBca", "RpFlo")
                    ->whereBetween('Tanggal', [$start_date, $end_date]);
            
            $data = $query->paginate($limit);

            return $this->success("Get data success!", $data);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function getDataRekapPendapatan(string $ruas_id, string $gerbang_id, ?string $start_date=null, ?string $end_date=null, ?int $limit = 10)
    {
        try {
            DatabaseConfig::switchConnection($ruas_id, $gerbang_id);

            $query = DB::connection('mediasi')
                    ->table("jid_rekap_pendapatan_miy")
                    ->select("*")
                    ->whereBetween('tgl_lap', [$start_date, $end_date]);

            $data = $query->paginate($limit);

            return $this->success("Get data success!", $data);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function getDataCompare(string $ruas_id, string $gerbang_id, ?string $start_date=null, ?string $end_date=null, ?int $limit = 10)
    {
        try {
                DatabaseConfig::switchMultiConnection($ruas_id, $gerbang_id);

                // Query untuk tabel mediasi
                $query_mediasi = DB::connection('mediasi')
                                    ->table("jid_transaksi_deteksi")
                                    ->select("tgl_lap", "gerbang_id", "gardu_id", "shift", DB::raw('COUNT(id) as jumlah_data_mediasi'))
                                    ->whereBetween('tgl_lap', [$start_date, $end_date])
                                    ->groupBy("tgl_lap", "gerbang_id", "gardu_id", "shift");

                // Query untuk tabel source
                $query_source = DB::connection('source')
                                    ->table("jid_transaksi_deteksi")
                                    ->select("tgl_lap", "gerbang_id", "gardu_id", "shift", DB::raw('COUNT(id) as jumlah_data_source'))
                                    ->whereBetween('tgl_lap', [$start_date, $end_date])
                                    ->groupBy("tgl_lap", "gerbang_id", "gardu_id", "shift");

                // Mendapatkan hasil dari query mediasi
                $results_mediasi = $query_mediasi->get();

                // Mendapatkan hasil dari query source
                $results_source = $query_source->get();

                // Gabungkan hasilnya dalam aplikasi
                $final_results = [];

                foreach ($results_mediasi as $mediasi) {
                        // Cari data yang sesuai di source berdasarkan kolom yang sama
                    $source_data = $results_source->first(function ($source) use ($mediasi) {
                        return $source->tgl_lap == $mediasi->tgl_lap && 
                                $source->gerbang_id == $mediasi->gerbang_id && 
                                $source->gardu_id == $mediasi->gardu_id && 
                                $source->shift == $mediasi->shift;
                    });

                    // Jika data source ditemukan, hitung selisih, jika tidak, anggap source = 0
                    $jumlah_data_source = $source_data ? $source_data->jumlah_data_source : 0;
                    $selisih = $jumlah_data_source - $mediasi->jumlah_data_mediasi;

                    // Simpan hasil dalam final_results
                    $final_results[] = [
                        'tanggal' => $mediasi->tgl_lap,
                        'gerbang_id' => $mediasi->gerbang_id,
                        'gardu_id' => $mediasi->gardu_id,
                        'shift' => $mediasi->shift,
                        'jumlah_data_mediasi' => $mediasi->jumlah_data_mediasi,
                        'jumlah_data_source' => $jumlah_data_source,
                        'selisih' => $selisih
                    ];
                }

               // Menambahkan paginasi secara manual
                $page = Paginator::resolveCurrentPage(); // Mendapatkan halaman saat ini
                $perPage = $limit; // Jumlah data per halaman
                $total = count($final_results); // Total data

                // Slice array sesuai dengan halaman yang diinginkan
                $items = array_slice($final_results, ($page - 1) * $perPage, $perPage);

                // Buat instance LengthAwarePaginator
                $data = new LengthAwarePaginator(
                    $items, 
                    $total, 
                    $perPage, 
                    $page, 
                    ['path' => Paginator::resolveCurrentPath()]
                );

                return $this->success("Get data success!", $data);
        } catch (\Exception $e) {
            return $this->error("Error: " . $e->getMessage());
        }
    }
}