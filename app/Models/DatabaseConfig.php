<?php

namespace App\Models;

use Illuminate\Support\Facades\Config;

class DatabaseConfig
{
    public static function switchConnection($ruas_id, $gerbang_id, $connectionName='mediasi')
    {
        try {
            if($connectionName === 'mediasi')
            {
                $credential = Integrator::getCredentialMediasi($ruas_id, $gerbang_id);
            }else{
                $credential = Integrator::getCredentialIntegrator($ruas_id, $gerbang_id);
            }

            self::setCredentials(
                $connectionName,
                $credential->host,
                $credential->port,
                $credential->user,
                $credential->pass,
                database: $credential->database
            );

            return response()->json(['message' => "Connection changed"], 200);
        } catch (\Exception $e) {
            // Now return the response with the error message
            throw new \Exception($e->getMessage()); 
        }
    }

    public static function switchMultiConnection($ruas_id, $gerbang_id, $connectionIntegrator)
    {
        try {
            $mediasiCredentials = Integrator::getCredentialMediasi($ruas_id, $gerbang_id);
            $integratorCredentials = Integrator::getCredentialIntegrator($ruas_id, $gerbang_id);

            self::setCredentials(
                'mediasi',
                $mediasiCredentials->host,
                $mediasiCredentials->port,
                $mediasiCredentials->user,
                $mediasiCredentials->pass,
                database: $mediasiCredentials->database
            );

            self::setCredentials(
                $connectionIntegrator,
                $integratorCredentials->host,
                $integratorCredentials->port,
                $integratorCredentials->user,
                $integratorCredentials->pass,
                database: $integratorCredentials->database
            );

            return response()->json(['message' => "Multi Connection changed"], 200);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage()); 
        }
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
