<?php

namespace App\Repositories;

use App\Traits\ResponseAPI;
use App\Interfaces\RekapDataInterface;
use App\Models\DatabaseConfig;
use App\Models\Integrator;
use Illuminate\Support\Facades\DB;

class DBRepository implements RekapDataInterface
{
    use ResponseAPI;

    public function getDataTransakiDetail(string $ruas_id, string $gerbang_id, ?string $start_date=null, ?string $end_date=null, ?int $limit = 10)
    {
        try {
                DatabaseConfig::switchConnection($ruas_id, $gerbang_id);

                // buat handling error connection sql like base table or view not found etc.
                $query = DB::connection('mediasi')
                            ->table("jid_transaksi_deteksi_db")
                            ->select("gardu_id", "shift", "perioda", "no_resi", "gol", "metoda_bayar_id", "notran_id_sah", "etoll_hash", "tarif")
                            ->whereBetween('tgl_lap', [$start_date, $end_date]);

                $data = $query->paginate($limit);

           return $this->success("Get data success!", $data);
        } catch (\Exception $e) {
            return $this->error("Error: " . $e->getMessage()); // You can customize the error code if needed
        }
    }

    public function getDataRekapAT4(string $ruas_id, string $gerbang_id, ?string $start_date=null, ?string $end_date=null, ?int $limit = 10)
    {
        try {
            DatabaseConfig::switchConnection($ruas_id, $gerbang_id);

            $query = DB::connection('mediasi')
                    ->table("jid_rekap_at4_db")
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
                    ->table("jid_rekap_pendapatan_db")
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
                $sourceName = Integrator::sourceConnection($ruas_id, $gerbang_id);

                dd($sourceName);

                // buat handling error connection sql like base table or view not found etc.
                $query = DB::connection('mediasi')
                            ->table("jid_transaksi_deteksi_db")
                            ->select("gardu_id", "shift", "perioda", "no_resi", "gol", "metoda_bayar_id", "notran_id_sah", "etoll_hash", "tarif")
                            ->whereBetween('tgl_lap', [$start_date, $end_date]);

                $query = DB::connection($sourceName)
                        ->table("jid_transaksi_deteksi_db")
                        ->select("gardu_id", "shift", "perioda", "no_resi", "gol", "metoda_bayar_id", "notran_id_sah", "etoll_hash", "tarif")
                        ->whereBetween('tgl_lap', [$start_date, $end_date]);

                $data = $query->paginate($limit);

           return $this->success("Get data success!", $data);
        } catch (\Exception $e) {
            return $this->error("Error: " . $e->getMessage()); // You can customize the error code if needed
        }
    }
}