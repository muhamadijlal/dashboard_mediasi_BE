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
            case 48:
                return 'ALR/NTK/LOLOS';
            default:
                return $metoda_bayar;
        }
    }

    public static function transmetod_miy_to_jid($metoda_bayar)
    {
        switch((int)$metoda_bayar){
            case 2:
                return 40;
            case 3:
                return 21;
            case 23:
                return 24;
            case 20:
                return 22;
            case 21:
                return 23;
            case 29:
                return 28;
            case 1:
            case 17:
                return 11;
            case 12:
            case 18:
                return 12;
            case 13:
            case 19:
                return 13;
            case 0:
                return 48;
            default:
                return $metoda_bayar;
        }
    }

    public static function transmetod_db_to_jid($metoda_bayar, $jenis_dinas=null){

        // if ((int)$metoda_bayar === 21) {
        //     if (in_array((int)$jenis_dinas, [1, 20, 50])) {
        //         return 11;
        //     } elseif (in_array((int)$jenis_dinas, [2, 21, 51])) {
        //         return 12;
        //     } elseif (in_array((int)$jenis_dinas, [3, 22, 52])) {
        //         return 13;
        //     }
        // }

        switch((int)$metoda_bayar){
            case 1:
            case 2:
                return 40;
            case 11:
            case 12:
                return 21;
            case 18:
            case 19:
                return 24;
            case 14:
            case 15:
                return 22;
            case 16:
            case 17:
                return 23;
            case 5:
            case 6:
                return 25;
            case 31:
            case 32:
            case 60:
            case 61:
                return 28;
            case 20:
                return 11;
            case 21:
                return 12;
            case 22:
                return 13;
            case 80:
            case 82:
                return 40;
            case 81:
            case 83:
            case 84:
                return 48;
            default:
                return $metoda_bayar;
        }
    }
}
