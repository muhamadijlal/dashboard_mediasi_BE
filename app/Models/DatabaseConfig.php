<?php

namespace App\Models;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DatabaseConfig
{

    /**
     * Switch the database connection based on the provided ruas_id and gerbang_id.
     *
     * @param int $ruas_id
     * @param int $gerbang_id
     * @return \Illuminate\Http\JsonResponse
    */
    public static function switchConnection($ruas_id, $gerbang_id)
    {
        try {
            $credentials = self::getCredentialsFromDB($ruas_id, $gerbang_id);

            self::setCredentials(
                'mediasi',
                $credentials->mediasi['host'],
                $credentials->mediasi['port'],
                $credentials->mediasi['username'],
                $credentials->mediasi['password'],
                database: $credentials->mediasi['database']
            );

            return response()->json(['message' => "Connection changed"], 200);
        } catch (\Exception $e) {
            // Now return the response with the error message
            throw new \Exception($e->getMessage()); 
        }
    }

    /**
     * Switch the database connection based on the provided ruas_id and gerbang_id.
     *
     * @param int $ruas_id
     * @param int $gerbang_id
     * @return \Illuminate\Http\JsonResponse
    */
    public static function switchMultiConnection($ruas_id, $gerbang_id)
    {
        try {
            $credentials = self::getCredentialsFromDB($ruas_id, $gerbang_id);
            $sourceConnection = Integrator::sourceConnection($ruas_id, $gerbang_id);

            self::setCredentials(
                'mediasi',
                $credentials->mediasi['host'],
                $credentials->mediasi['port'],
                $credentials->mediasi['username'],
                $credentials->mediasi['password'],
                database: $credentials->mediasi['database']
            );

            self::setCredentials(
                $sourceConnection->connectionName,
                $credentials->source['host'],
                $credentials->source['port'],
                $credentials->source['username'],
                $credentials->source['password'],
                database: $credentials->source['database']
            );

            return response()->json(['message' => "Multi Connection changed"], 200);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage()); 
        }
    }

    /**
     * Mengambil kredensial koneksi database dari tabel tertentu di database.
     *
     * @param int $ruas_id
     * @param int $gerbang_id
     * @return object
    */
    public static function getCredentialsFromDB($ruas_id, $gerbang_id)
    {
        try{
            $mediasi = [];
            $source = [];

            $credentialMediasi = Self::getCredentialMediasi($ruas_id, $gerbang_id);
            $credentialSource = Self::getCredentialSource($ruas_id, $gerbang_id);

            if($credentialMediasi) {
                $mediasi = [
                    'host' => $credentialMediasi->host,
                    'port' => $credentialMediasi->port,
                    'username' => $credentialMediasi->user,
                    'password' => $credentialMediasi->pass,
                    'database' => $credentialMediasi->database,
                ];
            }

            if($credentialSource){
                $source = [
                    'host' => $credentialSource->host,
                    'port' => $credentialSource->port,
                    'username' => $credentialSource->user,
                    'password' => $credentialSource->pass,
                    'database' => $credentialSource->database,
                ];
            }

           return (object) [
            'mediasi' => $mediasi,
            'source' => $source
           ];
        }
        catch (\Exception $e) {
            Log::error('Failed to get database credentials', [
                'ruas_id' => $ruas_id,
                'gerbang_id' => $gerbang_id,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception("Database config error : ".$e->getMessage());
        }
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

    public static function getCredentialSource($ruas_id, $gerbang_id)
    {
        $credential = DB::table('tbl_integrator')
                        ->where('ruas_id', $ruas_id)
                        ->where('gerbang_id', $gerbang_id)
                        ->where('status', 1)
                        ->first();

        return $credential;
    }

    /**
     * Mengubah kredensial koneksi database.
     *
     * @param string $host
     * @param int $port
     * @param string $username
     * @param string $password
     * @param string $database
    */
    public static function setCredentials($connectionName, $host, $port, $username, $password, $database)
    {
        if (empty($host) || empty($port) || empty($username) || empty($password) || empty($database)) {
            throw new \Exception("Semua parameter kredensial harus diisi.");
        }

        Config::set("database.connections.{$connectionName}.host", $host);
        Config::set("database.connections.{$connectionName}.port", $port);
        Config::set("database.connections.{$connectionName}.username", $username);
        Config::set("database.connections.{$connectionName}.password", $password);
        Config::set("database.connections.{$connectionName}.database", $database);
    }
}
