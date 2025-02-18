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

    public static function metode_bayar_jid($metoda_bayar, $jenis_notran = null) {

        if((int)$metoda_bayar === 48 && (int)$jenis_notran === 2) {
            return 'LOLOS/ALR/NTK';
        } else if((int)$metoda_bayar === 49 && (int)$jenis_notran === 2) {
            return 'INDAMAL';
        } else if((int)$metoda_bayar === 50 && (int)$jenis_notran === 2) {
            return 'MAJU MUNDUR';
        } else if((int)$metoda_bayar === 46 && (int)$jenis_notran === 2) {
            return 'MAJU MUNDUR';
        } else if((int)$metoda_bayar === 40 && (int)$jenis_notran === 3) {
            return 'LSB/NAK';
        }

        switch((int)$metoda_bayar){
            case 21:
                return 'MANDIRI';
            case 22:
                return 'BRI';
            case 23:
                return 'BNI';
            case 24:
                return 'BCA';
            case 40:
                return 'TUNAI';
            case 25:
                return 'DKI';
            case 28:
                return 'FLO';
            case 11:
                return 'JMC OPERASI';
            case 12:
                return 'JMC KARYAWAN';
            case 13:
                return 'JMC MITRA';
            default:
                return $metoda_bayar;
        }
    }
}
