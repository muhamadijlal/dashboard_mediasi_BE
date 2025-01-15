<?php

namespace App\Models;

use App\Repositories\DBRepository;
use App\Repositories\JMTORepository;
use App\Repositories\MIYRepository;
use App\Repositories\MMSRepository;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class Integrator
{
    public static function get($ruas_id, $gerbang_id)
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
            throw new InvalidArgumentException('Integrator not found!');
        }

        // Tentukan service yang akan digunakan berdasarkan integrator
        switch ($integrator->integrator) {
            case 1:
                // Panggil MMSRepository
                $repository = app(MMSRepository::class);
                break;
            
            case 2:
                $repository = app(MIYRepository::class);
                break;

            case 3:
                // Panggil MIYRepository
                $repository = app(DBRepository::class);
                break;

            case 4:
                // Panggil JMTORepository
                $repository = app(JMTORepository::class);
                break;

            default:
                throw new InvalidArgumentException('Invalid integrator');
        }

        return $repository;
    }
}