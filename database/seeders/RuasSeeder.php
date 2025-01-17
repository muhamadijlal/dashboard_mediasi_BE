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
        $data = [
            ['gerbang_id' => '01', 'ruas_id' => '', 'gerbang_nama' => 'Cililitan', 'host' => '179.10.100.252', 'port' => 3306, 'user' => 'mediasi', 'pass' => '@d4lk0t', 'database' => 'dalkot_lattol_01', 'status' => 1, 'integrator' => 1, 'data_source' => 1],
            ['gerbang_id' => '', 'ruas_id' => '', 'gerbang_nama' => '', 'host' => '', 'port' => 0, 'user' => '', 'pass' => '', 'database' => '', 'status' => 0, 'integrator' => 0, 'data_source' => 1],

            ['gerbang_id' => '', 'ruas_id' => '', 'gerbang_nama' => '', 'host' => '', 'port' => 0, 'user' => '', 'pass' => '', 'database' => '', 'status' => 0, 'integrator' => 0, 'data_source' => 1],
            ['gerbang_id' => '', 'ruas_id' => '', 'gerbang_nama' => '', 'host' => '', 'port' => 0, 'user' => '', 'pass' => '', 'database' => '', 'status' => 0, 'integrator' => 0, 'data_source' => 2],

            ['gerbang_id' => '', 'ruas_id' => '', 'gerbang_nama' => '', 'host' => '', 'port' => 0, 'user' => '', 'pass' => '', 'database' => '', 'status' => 0, 'integrator' => 0, 'data_source' => 1],
            ['gerbang_id' => '', 'ruas_id' => '', 'gerbang_nama' => '', 'host' => '', 'port' => 0, 'user' => '', 'pass' => '', 'database' => '', 'status' => 0, 'integrator' => 0, 'data_source' => 2],
        ];

        // Insert the data into the 'tbl_ruas' table
        DB::table('tbl_ruas')->insert($data);
    }
}
