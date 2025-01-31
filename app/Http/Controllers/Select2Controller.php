<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Select2Controller extends Controller
{
    public function getRuas() {
        try{
            $ruas = DB::connection('mysql')
                ->table("tbl_ruas")
                ->select("ruas_id as value","ruas_nama as label")
                ->where("status", 1)
                ->groupBy("ruas_id","ruas_nama")
                ->get();

            return response()->json(['message' => 'get ruas success!', 'data' => $ruas ], 201);
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

            $gerbang = DB::connection('mysql')
                ->table("tbl_ruas")
                ->select("gerbang_id as value","gerbang_nama as label")
                ->where("status", 1)
                ->where("ruas_id", $request->ruas_id)
                ->groupBy("gerbang_id","gerbang_nama")
                ->get();
            
            return response()->json(['message' => 'get gerbang success!', 'data' => $gerbang], 201);
        }catch(\Exception $e){
            return response()->json(['message' => $e->getMessage(), 'error' => true ], 500);
        }
    }

    public function getRuasResi() {
        try{
            $ruas = DB::connection('mysql')
                ->table("tbl_resi_digital")
                ->select("ruas_id as value","ruas_nama as label")
                ->where("status", 1)
                ->groupBy("ruas_id","ruas_nama")
                ->get();

            return response()->json(['message' => 'get ruas success!', 'data' => $ruas ], 201);
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

            $gerbang = DB::connection('mysql')
                ->table("tbl_resi_digital")
                ->select("gerbang_id as value","gerbang_nama as label")
                ->where("status", 1)
                ->where("ruas_id", $request->ruas_id)
                ->groupBy("gerbang_id","gerbang_nama")
                ->get();
            
            return response()->json(['message' => 'get gerbang success!', 'data' => $gerbang], 201);
        }catch(\Exception $e){
            return response()->json(['message' => $e->getMessage(), 'error' => true ], 500);
        }
    }
}
