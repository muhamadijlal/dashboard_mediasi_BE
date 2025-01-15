<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ResponseAPI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RuasController extends Controller
{
    use ResponseAPI;
    public function getRuas()
    {
        try{
            $ruas = DB::connection('mysql')
                ->table("tbl_ruas")
                ->select("ruas_id as value")
                ->where("status", 1)
                ->groupBy("ruas_id")
                ->get();

            return $this->success("Get data success!", $ruas);
        } catch(\Exception $e) {
            return $this->error("Get data failed!", $e->getMessage());
        }
    }

    public function getGerbang(Request $request)
    {
        try{
            $gerbang = DB::connection('mysql')
                ->table("tbl_ruas")
                ->select("gerbang_id as value")
                ->where("status", 1)
                ->where("ruas_id", $request->ruas_id)
                ->groupBy("gerbang_id")
                ->get();
            
            return $this->success("Get data success!", $gerbang);
        } catch(\Exception $e) {
            return $this->error("Get data failed!", $e->getMessage());
        }
    }
}
