<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
}
