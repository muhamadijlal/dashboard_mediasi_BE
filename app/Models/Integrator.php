<?php

namespace App\Models;

use App\Repositories\DBRepository;
use App\Repositories\JMTORepository;
use App\Repositories\MIYRepository;
use Illuminate\Support\Facades\DB;

class Integrator
{
    public static function get($ruas_id, $gerbang_id)
    {
        $integrator = Self::connection($ruas_id, $gerbang_id);

        // Tentukan service yang akan digunakan berdasarkan integrator
        switch ($integrator->integrator) {
            // case 1:
            //     $repository = app(DEVRepository::class);
            //     break;
            
            case 2:
                $repository = app(MIYRepository::class);
                break;

            case 3:
                $repository = app(DBRepository::class);
                break;

            case 4:
                $repository = app(JMTORepository::class);
                break;

            default:
                throw new \Exception(message: 'Invalid integrator');
        }

        return $repository;
    }

    public static function connection($ruas_id, $gerbang_id)
    {
        // Ambil nilai integrator dari tbl_ruas
        $integrator = DB::connection(config('database.default'))
                        ->table("tbl_ruas")
                        ->select("integrator")
                        ->where("ruas_id", $ruas_id)
                        ->where('gerbang_id', operator: $gerbang_id)
                        ->where('status', 1)
                        ->first();

        // Pastikan integrator ditemukan
        if (!$integrator) {
            throw new \Exception('Integrator not found!');
        }

        return $integrator;
    }

    public static function sourceConnection($ruas_id, $gerbang_id)
    {
        $integrator = Self::connection($ruas_id, $gerbang_id);

        // Tentukan service yang akan digunakan berdasarkan integrator
        switch ($integrator->integrator) {
            // case 1:
            //     // MMS
            //     $connectionName = '';
            //     break;
            
            case 2:
                // MIY
                $connectionName = 'source' ;
                break;

            case 3:
                // DB
                $connectionName = 'source_pgsql';
                break;

            case 4:
                // JMTO
                $connectionName = 'source';
                break;

            default:
                throw new \Exception('Invalid integrator');
        }

        return (object) [
            'connectionName' => $connectionName,
        ];
    }
}