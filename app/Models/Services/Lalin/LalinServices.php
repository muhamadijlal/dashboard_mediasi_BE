<?php

namespace App\Models\Services\Lalin;

use App\Models\DatabaseConfig;
use Illuminate\Support\Facades\DB;

class LalinServices
{
    protected static function getAllConnection()
    {
        $credentials = DB::connection('mysql')
            ->table('tbl_ruas') 
            ->select('ruas_nama','gerbang_nama','host', 'port', 'user', 'pass', 'database', 'status_gerbang_utama')
            ->where('status_gerbang_utama', '!=', 0)
            ->orderBy('ruas_id')
            ->get();

        return $credentials;
    }

    public static function mapping($start_date, $end_date, $type)
    {
        $credentials = self::getAllConnection();

        $dataEntrance = [];
        $dataExit = [];

        $prevCredential = '';
        foreach($credentials as $credential)
        {
            if($credential->host != $prevCredential){
                DatabaseConfig::setCredentials('mediasi', $credential->host, $credential->port, $credential->user, $credential->pass, $credential->database);
            }

            $data = DB::connection('mediasi')
                ->table('jid_transaksi_deteksi')
                ->select('shift', 'tgl_lap', DB::raw('COUNT(id) as jumlah_data'))
                ->whereIn('metoda_bayar_sah', [21, 22, 23, 24])
                ->whereBetween('tgl_lap', [$start_date, $end_date])
                ->groupBy('shift','tgl_lap')
                ->get();

            foreach ($data as $item) {
                // Prepare the common data
                $data = [
                    "tgl_lap" => $item->tgl_lap,
                    "ruas_nama" => $credential->ruas_nama,
                    "gerbang_nama" => $credential->gerbang_nama,
                    "shift" => $item->shift,
                    "jumlah_data" => $item->jumlah_data,
                ];
        
                if ($credential->status_gerbang_utama == 2) { // Entrance
                    $dataEntrance[] = $data; // Append to the entrance array
                } else { // Exit
                    $dataExit[] = $data; // Append to the exit array
                }
            }

            $prevCredential = $credential->host;
        }

        if(strtolower($type) === 'exit')
        {
            return $dataExit;
        } 
        else if(strtolower($type) === 'entrance')
        {
            return $dataEntrance;
        }
    }
}