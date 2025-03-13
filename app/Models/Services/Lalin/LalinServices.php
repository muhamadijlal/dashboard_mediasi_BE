<?php

namespace App\Models\Services\Lalin;

use App\Models\DatabaseConfig;
use Exception;
use Illuminate\Support\Facades\Config;
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
        try{
            $credentials = self::getAllConnection();

            if (empty($credentials)) {
                throw new Exception("No database credentials found.");
            }    

            $dataEntrance = [];
            $dataExit = [];

            foreach($credentials as $credential)
            {
                DatabaseConfig::setConnectionMediasi($credential->host, $credential->port, $credential->user, $credential->pass, $credential->database);

                DB::purge('mediasi');

                $data = DB::connection('mediasi')
                            ->table('jid_transaksi_deteksi')
                            ->select('shift', 'tgl_lap', DB::raw('COUNT(id) as jumlah_data'))
                            ->whereIn('metoda_bayar_sah', [21, 22, 23, 24, 28, 40])
                            ->whereBetween('tgl_lap', [$start_date, $end_date])
                            ->groupBy('shift','tgl_lap')
                            ->get();

                foreach ($data as $item) {
                    // Prepare the common data
                    $items = [
                        "tgl_lap" => $item->tgl_lap,
                        "ruas_nama" => $credential->ruas_nama,
                        "gerbang_nama" => $credential->gerbang_nama,
                        "shift" => $item->shift,
                        "jumlah_data" => $item->jumlah_data,
                    ];
            
                    if ($credential->status_gerbang_utama == 2) { // Entrance
                        $dataEntrance[] = $items; // Append to the entrance array
                    } else { // Exit
                        $dataExit[] = $items; // Append to the exit array
                    }
                }
            }

            if(strtolower($type) === 'exit')
            {
                return $dataExit;
            } 
            else if(strtolower($type) === 'entrance')
            {
                return $dataEntrance;
            }
        } catch(\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}