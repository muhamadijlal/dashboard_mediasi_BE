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
        $integrator = Self::integrator($ruas_id, $gerbang_id);

        switch ($integrator) {
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

    public static function type($ruas_id, $gerbang_id)
    {
        // Ambil nilai integrator dari tbl_ruas
        $integrator = DB::connection('mysql')
                        ->table("tbl_integrator")
                        ->select("tipe_gerbang")
                        ->where("ruas_id", operator: $ruas_id)
                        ->where('gerbang_id', operator: $gerbang_id)
                        ->where('status', 1)
                        ->first();

        switch($integrator->tipe_gerbang) {
            case 1:
            case 3:
                $tableName = "lalin_settlement";
                break;
            case 2:
                $tableName =  "lalin_entrance";
                break;
            default:
                throw new \Exception(message: 'Invalid Type');
        }

        return $tableName;
    }

    public static function getCredentialIntegrator($ruas_id, $gerbang_id)
    {
        $credential = DB::table('tbl_integrator')
                        ->where('ruas_id', $ruas_id)
                        ->where('gerbang_id', $gerbang_id)
                        ->where('status', 1)
                        ->first();

        return $credential;
    }

    public static function getCredentialMediasi($ruas_id, $gerbang_id)
    {
        $credential = DB::table('tbl_ruas')
                        ->where('ruas_id', $ruas_id)
                        ->where('gerbang_id', $gerbang_id)
                        ->where('status', 1)
                        ->first();

        return $credential;
    }

    public static function integrator($ruas_id, $gerbang_id)
    {
        // Ambil nilai integrator dari tbl_ruas
        $integrator = DB::connection('mysql')
                        ->table("tbl_ruas")
                        ->select("integrator")
                        ->where("ruas_id", $ruas_id)
                        ->where('gerbang_id', operator: $gerbang_id)
                        ->where('status', 1)
                        ->first();

        // Pastikan integrator ditemukan
        if ($integrator === null) {
            throw new \Exception('Integrator not found!');
        }

        return $integrator->integrator;
    }
}
