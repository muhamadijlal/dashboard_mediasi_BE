<?php

namespace App\Models;

use App\Models\Services\DB\DBEntrance;
use App\Models\Services\DB\DBEntranceExit;
use App\Models\Services\DB\DBExit;
use App\Models\Services\DB\DBOpen;
use App\Models\Services\MIY\MIYExit;
use App\Models\Services\MIY\MIYEntranceExit;
use App\Models\Services\MIY\MIYOpen;
use App\Models\Services\MIY\MIYEntrance;
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

    public static function services($ruas_id, $gerbang_id)
    {
        // Ambil nilai integrator dari tbl_ruas
        $integrator = DB::connection('mysql')
                        ->table("tbl_integrator")
                        ->select("tipe_gerbang", "integrator")
                        ->where("ruas_id", $ruas_id)
                        ->where('gerbang_id', $gerbang_id * 1)
                        ->where('status', 1)
                        ->first();

        if (!$integrator) {
            throw new \Exception('Integrator not found');
        }

        $services = null;

        // Define service classes based on integrator type and gate type
        $serviceMap = [
            2 => [
                1 => MIYOpen::class,
                2 => MIYEntrance::class,
                3 => MIYExit::class,
                4 => MIYEntranceExit::class,
            ],
            3 => [
                1 => DBOpen::class,
                2 => DBEntrance::class,
                3 => DBExit::class,
                4 => DBEntranceExit::class,
            ],
        ];

        // Check if the integrator type exists in the service map
        if (isset($serviceMap[$integrator->integrator][$integrator->tipe_gerbang])) {
            $services = app($serviceMap[$integrator->integrator][$integrator->tipe_gerbang]);
        } else {
            throw new \Exception('Invalid Services');
        }

        return $services;
    }

    public static function getCredentialIntegrator($ruas_id, $gerbang_id)
    {
        $credential = DB::table('tbl_integrator')
                        ->where('ruas_id', $ruas_id)
                        ->where('gerbang_id', $gerbang_id * 1)
                        ->where('status', 1)
                        ->first();

        return $credential;
    }

    public static function getCredentialMediasi($ruas_id, $gerbang_id)
    {
        $credential = DB::table('tbl_ruas')
                        ->where('ruas_id', $ruas_id)
                        ->where('gerbang_id', $gerbang_id * 1)
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
                        ->where('gerbang_id', operator: $gerbang_id * 1)
                        ->where('status', 1)
                        ->first();

        // Pastikan integrator ditemukan
        if ($integrator === null) {
            throw new \Exception('Integrator not found!');
        }

        return $integrator->integrator;
    }

    public static function schema($ruas_id, $gerbang_id)
    {
        // Ambil nilai integrator dari tbl_ruas
        $integrator = DB::connection('mysql')
                        ->table("tbl_integrator")
                        ->select("database_schema")
                        ->where("ruas_id", $ruas_id)
                        ->where('gerbang_id', operator: $gerbang_id * 1)
                        ->where('status', 1)
                        ->first();

        // Pastikan integrator ditemukan
        if ($integrator === null) {
            throw new \Exception('Integrator not found!');
        }

        return $integrator->database_schema;
    }
}
