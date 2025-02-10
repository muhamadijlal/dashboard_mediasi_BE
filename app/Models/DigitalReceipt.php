<?php

namespace App\Models;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class DigitalReceipt
{
    public static function switchDB($ruas_id, $gerbang_id)
    {
        try {
            $credentials = self::getDB($ruas_id, $gerbang_id);

            $mediasiCredentials = $credentials[0];
            $integratorCredentials = $credentials[1];

            self::setCredentials(
                'mediasi',
                $mediasiCredentials->host,
                $mediasiCredentials->port,
                $mediasiCredentials->user,
                $mediasiCredentials->pass,
                database: $mediasiCredentials->database
            );

            self::setCredentials(
                'integrator',
                $integratorCredentials->host,
                $integratorCredentials->port,
                $integratorCredentials->user,
                $integratorCredentials->pass,
                database: $integratorCredentials->database
            );

            return response()->json(['message' => "Switch DB succed"], 200);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage()); 
        }
    }

    public static function getDB($ruas_id, $gerbang_id)
    {
        $mediasi = DB::connection('mysql')
                        ->table('tbl_resi_digital')
                        ->where('ruas_id', $ruas_id)
                        ->where('gerbang_id', $gerbang_id*1)
                        ->where('status', 1)
                        ->first();

        $integrator = DB::connection('mysql')
                        ->table('tbl_resi_mediasi')
                        ->where('ruas_id', $ruas_id)
                        ->where('gerbang_id', $gerbang_id*1)
                        ->where('status', 1)
                        ->first();

        return [$mediasi, $integrator];
    }

    public static function getIPIntegrator($ruas_id, $gerbang_id)
    {
        $integrator = DB::table('tbl_resi_mediasi')
                ->select("host")
                ->where('ruas_id', $ruas_id)
                ->where('gerbang_id', $gerbang_id*1)
                ->where('status', 1)
                ->first();

        return $integrator;
    }

    public static function getIPMediasi($ruas_id, $gerbang_id) 
    {
        $mediasi = DB::table('tbl_resi_digital')
                ->select("host")
                ->where('ruas_id', $ruas_id)
                ->where('gerbang_id', $gerbang_id*1)
                ->where('status', 1)
                ->first();

        return $mediasi;
    }
    

    public static function setCredentials($connectionName, $host, $port, $username, $password, $database)
    {
        if (empty($host) || empty($port) || empty($username) || empty($password) || empty($database)) {
            throw new \Exception("All credentials must be filled.");
        }

        Config::set("database.connections.{$connectionName}.host", $host);
        Config::set("database.connections.{$connectionName}.port", $port);
        Config::set("database.connections.{$connectionName}.username", $username);
        Config::set("database.connections.{$connectionName}.password", $password);
        Config::set("database.connections.{$connectionName}.database", $database);
    }
}
