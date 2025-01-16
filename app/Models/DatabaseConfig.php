<?php

namespace App\Models;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

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
                $credentials['host'],
                $credentials['port'],
                $credentials['username'],
                $credentials['password'],
                database: $credentials['database']
            );

            return response()->json(['message' => "Connection changed"], 200);
        } catch (InvalidArgumentException $e) {
            // Now return the response with the error message
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 400);
        }
    }

    /**
     * Mengambil kredensial koneksi database dari tabel tertentu di database.
     *
     * @param int $ruas_id
     * @param int $gerbang_id
     * @return array
    */
    public static function getCredentialsFromDB($ruas_id, $gerbang_id)
    {
        try{
            $credential = DB::table('tbl_ruas')
                            ->where('ruas_id', $ruas_id)
                            ->where('gerbang_id', $gerbang_id)
                            ->where('status', 1)
                            ->first();

            if (!$credential) {
                throw new InvalidArgumentException("Database confign : Credential not found!");
            }

            return [
                'host' => $credential->host,
                'port' => $credential->port,
                'username' => $credential->user,
                'password' => $credential->pass,
                'database' => $credential->database,
            ];
        }
            catch (InvalidArgumentException $e) {
                throw new InvalidArgumentException("Database config error : ".$e->getMessage());
        }
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
    public static function setCredentials($host, $port, $username, $password, $database)
    {
        if (empty($host) || empty($port) || empty($username) || empty($password) || empty($database)) {
            throw new InvalidArgumentException("Semua parameter kredensial harus diisi.");
        }

        Config::set('database.connections.mediasi.host', $host);
        Config::set('database.connections.mediasi.port', $port);
        Config::set('database.connections.mediasi.username', $username);
        Config::set('database.connections.mediasi.password', $password);
        Config::set('database.connections.mediasi.database', $database);
    }
}
