<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class Utils
{
    public static function getRuasnGerbangName($ruas_id, $gerbang_id) {
        $data = DB::connection("mysql")
                    ->table('tbl_ruas')
                    ->select('ruas_nama', 'gerbang_nama')
                    ->where('ruas_id', $ruas_id)
                    ->where('gerbang_id', $gerbang_id*1)
                    ->where('status', 1)
                    ->first();

        return $data;
    }

    public static function metode_bayar_jid($metoda_bayar) {
        switch((int)$metoda_bayar){
            case 21:
                return 'Mandiri';
            case 22:
                return 'BRI';
            case 23:
                return 'BNI';
            case 24:
                return 'BCA';
            default:
                return $metoda_bayar;
        }
    }
}
