<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RuasIntegratorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tbl_ruas = [
            [
                'ruas_id' => '111',
                'ruas_nama' => "JAGORAWI-DEV",
                'gerbang_id' => '77',
                'gerbang_nama' => "CIAWI 1-DEV",
                'host' => '175.10.1.101',
                'port' => 3306,
                'user' => 'dbdev',
                'pass' => 'jmto2024',
                'database' => 'jago_lattol_77_source',
                'integrator' => 4,
            ],
            [
                'ruas_id' => '888',
                'ruas_nama' => "DALKOT-DEV",
                'gerbang_id' => '88',
                'gerbang_nama' => "CILILITAN-DEV",
                'host' => '175.10.1.101',
                'port' => 3306,
                'user' => 'dbdev',
                'pass' => 'jmto2024',
                'database' => 'miy_source',
                'integrator' => 2,
            ],
            [
                'ruas_id' => '999',
                'ruas_nama' => "JANGER-DEV",
                'gerbang_id' => '99',
                'gerbang_nama' => "TANGERANG1-DEV",
                'host' => '10.10.100.20',
                'port' => 3306,
                'user' => 'jmto',
                'pass' => 'password',
                'database' => 'dbsahrejmto',
                'integrator' => 3,
            ],
        ];

        $tbl_integrator = [
            [
                'ruas_id' => '111',
                'ruas_nama' => "JAGORAWI-DEV",
                'gerbang_id' => '77',
                'gerbang_nama' => "CIAWI 1-DEV",
                'host' => '175.10.1.101',
                'port' => 3306,
                'user' => 'dbdev',
                'pass' => 'jmto2024',
                'database' => 'jago_lattol_77_source',
                'database_schema' => '',
                'tipe_gerbang' => 1,
                'integrator' => 4,
            ],
            [
                'ruas_id' => '888',
                'ruas_nama' => "DALKOT-DEV",
                'gerbang_id' => '88',
                'gerbang_nama' => "CILILITAN-DEV",
                'host' => '175.10.1.101',
                'port' => 3306,
                'user' => 'dbdev',
                'pass' => 'jmto2024',
                'database' => 'miy_source',
                'database_schema' => '',
                'tipe_gerbang' => 1,
                'integrator' => 2,
            ],
            [
                'ruas_id' => '999',
                'ruas_nama' => "JANGER-DEV",
                'gerbang_id' => '99',
                'gerbang_nama' => "TANGERANG1-DEV",
                'host' => '10.10.100.20',
                'port' => 3306,
                'user' => 'jmto',
                'pass' => 'password',
                'database' => 'dbsahrejmto',
                'database_schema' => 'karawaci1',
                'tipe_gerbang' => 1,
                'integrator' => 3,
            ],
        ];

        DB::connection("mysql")
        ->table("tbl_integrator")
        ->insert($tbl_integrator);

        DB::connection("mysql")
        ->table("tbl_ruas")
        ->insert($tbl_ruas);
    }
}
