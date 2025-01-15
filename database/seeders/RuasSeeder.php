<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RuasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $data = [
        //     ['gerbang_id' => '01', 'ruas_id' => '11', 'gerbang_nama' => 'CIAWI 1', 'host' => '172.20.1.234', 'port' => 3306, 'user' => 'terminal', 'pass' => '@jago01#', 'database' => 'jago_lattol_01', 'status' => 1, 'integrator' => 1],
        //     ['gerbang_id' => '02', 'ruas_id' => '11', 'gerbang_nama' => 'BOGOR 1', 'host' => '172.20.2.234', 'port' => 3306, 'user' => 'terminal', 'pass' => '@jago02#', 'database' => 'jago_lattol_02', 'status' => 1, 'integrator' => 1],
        //     ['gerbang_id' => '03', 'ruas_id' => '11', 'gerbang_nama' => 'SENTUL SELATAN 1', 'host' => '172.20.3.234', 'port' => 3306, 'user' => 'terminal03', 'pass' => '@jago03#', 'database' => 'jago_lattol_03', 'status' => 1, 'integrator' => 1],
        //     ['gerbang_id' => '04', 'ruas_id' => '11', 'gerbang_nama' => 'SENTUL 1', 'host' => '172.20.4.234', 'port' => 3306, 'user' => 'terminal04', 'pass' => '@jago04#', 'database' => 'jago_lattol_04', 'status' => 1, 'integrator' => 1],
        //     ['gerbang_id' => '05', 'ruas_id' => '11', 'gerbang_nama' => 'CITEUREUP 1', 'host' => '172.20.5.234', 'port' => 3306, 'user' => 'terminal05', 'pass' => '@jago05#', 'database' => 'jago_lattol_05', 'status' => 1, 'integrator' => 1],
        //     ['gerbang_id' => '06', 'ruas_id' => '11', 'gerbang_nama' => 'GUNUNG PUTRI', 'host' => '172.20.6.234', 'port' => 3306, 'user' => 'terminal06', 'pass' => '@jago06#', 'database' => 'jago_lattol_06', 'status' => 1, 'integrator' => 1],
        //     ['gerbang_id' => '07', 'ruas_id' => '11', 'gerbang_nama' => 'KARANGGAN', 'host' => '172.20.7.234', 'port' => 3306, 'user' => 'terminal07', 'pass' => '@jago07#', 'database' => 'jago_lattol_07', 'status' => 1, 'integrator' => 1],
        //     ['gerbang_id' => '08', 'ruas_id' => '11', 'gerbang_nama' => 'CIMANGGIS 1', 'host' => '172.20.8.234', 'port' => 3306, 'user' => 'terminal', 'pass' => '@jago08#', 'database' => 'jago_lattol_08', 'status' => 1, 'integrator' => 1],
        //     ['gerbang_id' => '09', 'ruas_id' => '11', 'gerbang_nama' => 'CIBUBUR 1', 'host' => '172.20.9.234', 'port' => 3306, 'user' => 'terminal09', 'pass' => '@jago09#', 'database' => 'jago_lattol_09', 'status' => 1, 'integrator' => 1],
        //     ['gerbang_id' => '10', 'ruas_id' => '11', 'gerbang_nama' => 'DUKUH 2', 'host' => '172.20.13.234', 'port' => 3306, 'user' => 'terminal', 'pass' => '@jago13#', 'database' => 'jago_lattol_13', 'status' => 1, 'integrator' => 1],
        //     ['gerbang_id' => '11', 'ruas_id' => '11', 'gerbang_nama' => 'RAMP TAMAN MINI 1', 'host' => '172.20.14.234', 'port' => 3306, 'user' => 'terminal14', 'pass' => '@jago14#', 'database' => 'jago_lattol_14', 'status' => 1, 'integrator' => 1],
        //     ['gerbang_id' => '12', 'ruas_id' => '11', 'gerbang_nama' => 'BOGOR 2', 'host' => '172.20.17.234', 'port' => 3306, 'user' => 'terminal', 'pass' => '@jago15#', 'database' => 'jago_lattol_15', 'status' => 1, 'integrator' => 1],
        //     ['gerbang_id' => '13', 'ruas_id' => '11', 'gerbang_nama' => 'CIAWI 2', 'host' => '172.20.16.234', 'port' => 3306, 'user' => 'terminal', 'pass' => '@jago16#', 'database' => 'jago_lattol_16', 'status' => 1, 'integrator' => 1],
        //     ['gerbang_id' => '14', 'ruas_id' => '11', 'gerbang_nama' => 'CIMANGGIS 2', 'host' => '172.20.32.234', 'port' => 3306, 'user' => 'terminal', 'pass' => '@jago17#', 'database' => 'jago_lattol_17', 'status' => 1, 'integrator' => 1],
        //     ['gerbang_id' => '15', 'ruas_id' => '11', 'gerbang_nama' => 'CIMANGGIS 3', 'host' => '172.20.11.234', 'port' => 3306, 'user' => 'terminal', 'pass' => '@jago18#', 'database' => 'jago_lattol_18', 'status' => 1, 'integrator' => 1],
        //     ['gerbang_id' => '16', 'ruas_id' => '11', 'gerbang_nama' => 'CIMANGGIS 4', 'host' => '172.20.22.234', 'port' => 3306, 'user' => 'terminal', 'pass' => '@jago19#', 'database' => 'jago_lattol_19', 'status' => 1, 'integrator' => 1],
        //     ['gerbang_id' => '17', 'ruas_id' => '11', 'gerbang_nama' => 'CIMANGGIS 5', 'host' => '172.20.23.234', 'port' => 3306, 'user' => 'terminal', 'pass' => '@jago20#', 'database' => 'jago_lattol_20', 'status' => 1, 'integrator' => 1],
        //     ['gerbang_id' => '18', 'ruas_id' => '11', 'gerbang_nama' => 'BOGOR SELATAN', 'host' => '172.20.21.234', 'port' => 3306, 'user' => 'terminal', 'pass' => '@jago21#', 'database' => 'jago_lattol_21', 'status' => 1, 'integrator' => 1],
        //     ['gerbang_id' => '19', 'ruas_id' => '11', 'gerbang_nama' => 'SENTUL SELATAN 2', 'host' => '172.20.3.234', 'port' => 3306, 'user' => 'terminal22', 'pass' => '@jago22#', 'database' => 'jago_lattol_22', 'status' => 1, 'integrator' => 1],
        //     ['gerbang_id' => '20', 'ruas_id' => '11', 'gerbang_nama' => 'SENTUL 2', 'host' => '172.20.4.234', 'port' => 3306, 'user' => 'terminal23', 'pass' => '@jago23#', 'database' => 'jago_lattol_23', 'status' => 1, 'integrator' => 1],
        //     ['gerbang_id' => '21', 'ruas_id' => '11', 'gerbang_nama' => 'CITEUREUP 2', 'host' => '172.20.5.234', 'port' => 3306, 'user' => 'terminal24', 'pass' => '@jago24#', 'database' => 'jago_lattol_24', 'status' => 1, 'integrator' => 1],
        //     ['gerbang_id' => '22', 'ruas_id' => '11', 'gerbang_nama' => 'CIBUBUR 2', 'host' => '172.20.9.234', 'port' => 3306, 'user' => 'terminal28', 'pass' => '@jago28#', 'database' => 'jago_lattol_28', 'status' => 1, 'integrator' => 1],
        //     ['gerbang_id' => '23', 'ruas_id' => '11', 'gerbang_nama' => 'RAMP TAMAN MINI 2', 'host' => '172.20.14.234', 'port' => 3306, 'user' => 'terminal29', 'pass' => '@jago29#', 'database' => 'jago_lattol_29', 'status' => 1, 'integrator' => 1],
        // ];

        // $data = [
        //     ['gerbang_id' => '01', 'ruas_id' => '11', 'gerbang_nama' => 'CIAWI 1', 'host' => '172.20.15.252', 'port' => 3306, 'user' => 'mediasi', 'pass' => '@j4g0r4w1', 'database' => 'jago_lattol_01', 'status' => 1, 'integrator' => ]
        // ];

        // Insert the data into the 'tbl_ruas' table
        DB::table('tbl_ruas')->insert($data);
    }
}
