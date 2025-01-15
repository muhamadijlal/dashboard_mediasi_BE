<?php

namespace App\Repositories;

use App\Traits\ResponseAPI;
use App\Interfaces\RekapDataInterface;
use Illuminate\Support\Facades\DB;
use App\Models\DatabaseConfig;

class MIYRepository implements RekapDataInterface
{
    use ResponseAPI;

    public function getDataTransakiDetail(string $ruas_id, string $gerbang_id, ?string $start_date=null, ?string $end_date=null, ?int $limit = 10)
    {
        try {
            DatabaseConfig::switchConnection($ruas_id, $gerbang_id);

            $query = DB::table("jid_transaksi_deteksi_miy")
                        ->select(columns: "*")
                        ->whereBetween('tgl_lap', [$start_date, $end_date]);
            
            $data = $limit === null 
                ? $query->get() 
                : $query->paginate($limit);
            
            return $this->success("Get data success!", $data);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    public function getDataRekapAT4(string $ruas_id, string $gerbang_id, ?string $start_date=null, ?string $end_date=null, ?int $limit = 10)
    {
        try {
            DatabaseConfig::switchConnection($ruas_id, $gerbang_id);

            $query = DB::table("jid_rekap_at4_miy")
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

    public function getDataRekapPendapatan(string $ruas_id, string $gerbang_id, ?string $start_date=null, ?string $end_date=null, ?int $limit = 10)
    {
        try {
            DatabaseConfig::switchConnection($ruas_id, $gerbang_id);

            $query = DB::table("jid_rekap_pendapatan_miy")
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