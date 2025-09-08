<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class Select2Controller extends Controller
{
    public function getRuas(Request $request) {

        try{
            $query = DB::connection('mysql')
                        ->table("tbl_ruas")
                        ->select("ruas_id as value","ruas_nama as label")
                        ->where("status", 1);

            if($request['q']){
                $query->where('ruas_nama', 'like', '%' . $request['q'] . '%');
            }

            $result = $query->groupBy("ruas_id","ruas_nama")->get();

            return response()->json(['message' => 'get ruas success!', 'data' => $result ], 201);
        }catch(\Exception $e){
            return response()->json(['message' => $e->getMessage(), 'error' => true ], 500);
        }
    }

    public function getGerbang(Request $request)
    {
        try{
            $request->validate([
                'ruas_id' => "required|string"
            ]);

            $query = DB::connection('mysql')
                ->table("tbl_ruas")
                ->select("gerbang_id as value","gerbang_nama as label")
                ->where("status", 1)
                ->where("ruas_id", $request['ruas_id']);

            if($request['query']){
                $query->where('gerbang_nama', 'like', '%' . $request['query'] . '%');
            }

            $result = $query->groupBy("gerbang_id","gerbang_nama")->get();
            
            return response()->json(['message' => 'get gerbang success!', 'data' => $result], 201);
        }catch(\Exception $e){
            return response()->json(['message' => $e->getMessage(), 'error' => true ], 500);
        }
    }

    public function getGardu(Request $request)
    {
        try{
            $query = DB::connection('mysql')
                ->table("tbl_transaksi_deteksi")
                ->select("gardu_id as value","gardu_id as label")
                ->where("ruas_id", $request['ruas_id']);

            $result = $query->groupBy("gardu_id")->get();

            return response()->json(['message' => 'get gerbang success!', 'data' => $result], 201);
        }catch(\Exception $e){
            return response()->json(['message' => $e->getMessage(), 'error' => true ], 500);
        }
    }

    public function getRuasResi(Request $request) {
        try{
            $query = DB::connection('mysql')
                ->table("tbl_resi_digital")
                ->select("ruas_id as value","ruas_nama as label")
                ->where("status", 1);

            if($request['q']){
                $query->where('ruas_nama', 'like', '%' . $request['q'] . '%');
            }

            $result = $query->groupBy("ruas_id","ruas_nama")->get();

            return response()->json(['message' => 'get ruas success!', 'data' => $result ], 201);
        }catch(\Exception $e){
            return response()->json(['message' => $e->getMessage(), 'error' => true ], 500);
        }
    }

    public function getGerbangResi(Request $request)
    {
        try{
            $request->validate([
                'ruas_id' => "required|string"
            ]);

            $query = DB::connection('mysql')
                ->table("tbl_resi_digital")
                ->select("gerbang_id as value","gerbang_nama as label")
                ->where("status", 1)
                ->where("ruas_id", $request->ruas_id);

            if($request['query']){
                $query->where('gerbang_nama', 'like', '%' . $request['query'] . '%');
            }

            $result = $query->groupBy("gerbang_id","gerbang_nama")->get();
            
            return response()->json(['message' => 'get gerbang success!', 'data' => $result], 201);
        }catch(\Exception $e){
            return response()->json(['message' => $e->getMessage(), 'error' => true ], 500);
        }
    }   
    
    
    public function getKoneksi(Request $request){

        try {
            $conn = 'travoy_db_history';
            Config::set("database.connections.$conn", [
                'driver'    => 'mysql',
                'host'      => '172.16.39.109',
                'port'      => 14045,
                'database'  => 'travoy_db_history',
                'username'  => 'jmto',
                'password'  => '@jmt02024!#',
                'charset'   => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix'    => '',
                'strict'    => false,
            ]);
    
            // 3) Paksa reload koneksi agar config baru terpakai
            DB::purge($conn);
            DB::reconnect($conn);
            DB::connection($conn)->getPdo(); // sanity check
    
    
            $data = DB::connection($conn)->table('tbl_ruas')
            ->select('nama_ruas','id_ruas');

            if($request['q']){
                $data->where('nama_ruas', 'like', '%' . $request['q'] . '%');
            }


            $data = $data->get();
            
            return response()->json(['message' => 'get Koneksi success!', 'data' => $data ], 201);

        }catch(\Exception $e){
            return response()->json(['message' => $e->getMessage(), 'error' => true ], 500);
        }


    }
}
