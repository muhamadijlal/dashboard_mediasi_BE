<?php

namespace App\Repositories;

use App\Traits\ResponseAPI;
use App\Interfaces\RekapDataInterface;
use App\Models\DatabaseConfig;
use Illuminate\Support\Facades\DB;

class MMSRepository implements RekapDataInterface
{
    use ResponseAPI;

    public function getDataTransakiDetail(string $ruas_id, string $gerbang_id, ?string $start_date=null, ?string $end_date=null, ?int $limit = null)
    {
        try {
            // Switch database connection based on the passed parameters
            DatabaseConfig::switchConnection($ruas_id, $gerbang_id);

            // Perform the database query
            $query = DB::connection('mediasi')
                ->table("tbl_transaksi_deteksi")
                ->select("*")
                ->whereBetween('tgl_lap', [$start_date, $end_date]);


            $data = $limit === null 
                ? $query->get() 
                : $query->paginate($limit);

            return $this->success("Get data success!", $data);
        } catch (\Exception $e) {
            return $this->error("Error: " . $e->getMessage(), 500); // You can customize the error code if needed
        }
    }

    public function getDataRekapAT4(string $ruas_id, string $gerbang_id, ?string $start_date=null, ?string $end_date=null, ?int $limit = null)
    {
        try {
            DatabaseConfig::switchConnection($ruas_id, $gerbang_id);

            $query = DB::table("jid_rekap_at4")
                    ->select("*")
                    ->whereBetween('tgl_lap', [$start_date, $end_date]);

            $data = $limit === null 
                ? $query->get() 
                : $query->paginate($limit);

            return $this->success("Get data success!", $data);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    public function getDataRekapPendapatan(string $ruas_id, string $gerbang_id, ?string $start_date=null, ?string $end_date=null, ?int $limit = null)
    {
        try {
            DatabaseConfig::switchConnection($ruas_id, $gerbang_id);

            $query = DB::table("jid_rekap_pendapatan")
                    ->select("*")
                    ->whereBetween('tgl_lap', [$start_date, $end_date]);

            $data = $limit === null 
                ? $query->get() 
                : $query->paginate($limit);

            return $this->success("Get data success!", $data);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }
}