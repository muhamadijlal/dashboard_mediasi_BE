<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class Utils
{
    public static function getRuasnGerbangName($ruas_id, $gerbang_id)
    {
        $data = DB::connection("mysql")
            ->table('tbl_ruas')
            ->select('ruas_nama', 'gerbang_nama')
            ->where('ruas_id', $ruas_id)
            ->where('gerbang_id', $gerbang_id * 1)
            ->where('status', 1)
            ->first();

        return $data;
    }

    public static function metoda_bayar_sah($metoda_bayar, $jenis_notran, $jenis_ktp)
    {
        $metode_transaksi = (int) $metoda_bayar;

        // Mapping umum untuk transaksi yang memiliki kondisi jenis_notran == 1
        $payment_map_normal = [
            1 => ['40', '3'],
            3 => ['21', '1'],
            7 => ['24', '1'],
            4 => ['22', '1'],
            5 => ['23', '1'],
            8 => ['25', '1'],
            41 => ['28', '1'],
        ];
        if ($metode_transaksi == 21 && $jenis_notran == 1) {
            return ['21', '1'];
        }

        // Mapping untuk transaksi dengan metode 2 berdasarkan jenis_ktp
        $payment_map_metode_2 = [
            1 => ['11', '1'],
            2 => ['12', '1'],
            3 => ['13', '1'],
        ];

        // Mapping untuk transaksi dengan metode 0 dan jenis_notran 7, 8, 9
        $payment_map_metode_0 = [
            7 => ['48', '2'],
            8 => ['49', '2'],
            9 => ['50', '2'],
        ];

        // Cek apakah metode transaksi adalah 2, dan sesuaikan dengan jenis_ktp
        if ($metode_transaksi == 2 && isset($payment_map_metode_2[$jenis_ktp])) {
            return $payment_map_metode_2[$jenis_ktp];
        }

        // Cek apakah metode transaksi adalah 0 dan jenis_notran 7, 8, atau 9
        if ($metode_transaksi == 0 && isset($payment_map_metode_0[$jenis_notran])) {
            return $payment_map_metode_0[$jenis_notran];
        }

        // Jika metode transaksi ditemukan dalam mapping normal
        if (isset($payment_map_normal[$metode_transaksi])) {
            return $payment_map_normal[$metode_transaksi];
        }

        // Jika tidak ada kondisi yang cocok, kembalikan nilai default (0, 0)
        return ['', ''];
    }

    public static function metode_bayar_jid($metoda_bayar, $jenis_notran = null)
    {
        // Special cases handled with an associative array
        $specialCases = [
            48 => [2 => 'LOLOS/ALR/NTK'],
            41 => [3 => 'PEMBAYARAN DITANGGUHKAN'],
            49 => [2 => 'INDAMAL'],
            50 => [2 => 'MAJU MUNDUR'],
            46 => [2 => '33 LK'],
            40 => [3 => 'LSB/NAK'],
        ];

        // Check for special case first
        if (isset($specialCases[(int)$metoda_bayar]) && isset($specialCases[(int)$metoda_bayar][(int)$jenis_notran])) {
            return $specialCases[(int)$metoda_bayar][(int)$jenis_notran];
        }

        // Default cases in a switch
        switch ((int)$metoda_bayar) {
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

    public static function transmetod_miy_to_jid($metoda_bayar, $jenis_notran = null, $validasi_notran = null)
    {
        $metodeTransaksi = (int) $metoda_bayar;

        $paymentMap = [
            1 => ["11", "1"],
            3 => ["21", "1"],
            12 => ["12", "1"],
            13 => ["13", "1"],
            17 => ["11", "1"],
            18 => ["12", "1"],
            19 => ["13", "1"],
            20 => ["22", "1"],
            21 => ["23", "1"],
            23 => ["24", "1"],
            25 => ["25", "1"],
            29 => ["28", "1"],
        ];

        // Jenis Notran ( 2 ALR | 3 LSB )
        if (($metodeTransaksi == 2 || $metodeTransaksi == 7) &&
            ($jenis_notran == "NAK" || $jenis_notran == "NTK" || $jenis_notran == "LSB") &&
            ($validasi_notran != "(3-3) L/K" || $validasi_notran == "Pembayaran Transaksi Tidak Normal")
        ) {
            return ["40", "3"]; // Tunai LSB
        } elseif (($metodeTransaksi == 2 || $metodeTransaksi == 8 || $metodeTransaksi == 0) &&
            $jenis_notran == "NTK" &&
            $validasi_notran == "Tidak Dapat Menyerahkan KTM"
        ) {
            return ["43", "3"]; // Lolos, ALR
        } elseif (($metodeTransaksi == 2 || $metodeTransaksi == 7) &&
            ($jenis_notran == "NTK" || $jenis_notran == "NAK" || $jenis_notran == "LSB") &&
            $validasi_notran == "Pembayaran Kurang"
        ) {
            return ["45", "3"]; // Pembayaran Kurang, LSB
        } elseif (
            $metodeTransaksi == 2 &&
            ($jenis_notran == "NTK" || $jenis_notran == "NAK" || $jenis_notran == "LSB") &&
            $validasi_notran == "(3-3) L/K"
        ) {
            return ["46", "3"]; // 33 LK, LSB
        } elseif (($metodeTransaksi == 2 || $metodeTransaksi == 8 || $metodeTransaksi == 0) &&
            $jenis_notran == "NTK" &&
            $validasi_notran != "(3-3) L/K"
        ) {
            return ["48", "2"]; // Lolos, ALR
        } elseif (($metodeTransaksi == 2 || $metodeTransaksi == 8 || $metodeTransaksi == 0) &&
            ($jenis_notran == "NTK" || $jenis_notran == "ALR") &&
            $validasi_notran == "Indamal"
        ) {
            return ["49", "2"]; // Indamal, ALR
        } elseif (($metodeTransaksi == 2 || $metodeTransaksi == 8 || $metodeTransaksi == 0) &&
            ($jenis_notran == "NTK" || $jenis_notran == "ALR") &&
            $validasi_notran == "Maju Mundur"
        ) {
            return ["50", "2"]; // Maju Mundur, ALR
        }

        if (array_key_exists($metodeTransaksi, $paymentMap)) {
            return $paymentMap[$metodeTransaksi];
        }

        // Return a default value or error if no match is found
        return ["", ""];
    }

    public static function transmetod_db_to_jid($metoda_bayar, $jenis_dinas = null, $jenis_notran = null)
    {
        // Handle jenis_notran modifications first
        if ((int)$jenis_notran == 81) {
            $jenis_notran = 2;
        } else if (in_array((int)$jenis_notran, [80, 82])) {
            $jenis_notran = 3;
        }

        // Handle special case for metoda_bayar 21
        if ((int)$metoda_bayar === 21) {
            if (in_array((int)$jenis_dinas, [1, 20, 50])) {
                return ['11', '1'];
            } elseif (in_array((int)$jenis_dinas, [2, 21, 51])) {
                return ['12', '1'];
            } elseif (in_array((int)$jenis_dinas, [3, 22, 52])) {
                return ['13', '1'];
            }
        }

        // Handle other cases
        switch ((int)$metoda_bayar) {
            case 1:
            case 2:
                return ['40', '1'];
            case 11:
            case 12:
                return ['21', '1'];
            case 18:
            case 19:
                return ['24', '1'];
            case 14:
            case 15:
                return ['22', '1'];
            case 16:
            case 17:
                return ['23', '1'];
            case 5:
            case 6:
                return ['25', '1'];
            case 31:
            case 32:
            case 60:
            case 61:
                return ['28', '1'];
            case 20:
                return ['11', '1'];
            case 22:
                return ['13', '1'];
            case 80:
            case 82:
                return ['40', '3'];
            case 81:
            case 83:
            case 84:
                return ['48', '2'];
            default:
                return ['', ''];
        }
    }

    public static function miy_investor($ruas_id)
    {
        if ((int)$ruas_id == 11) { // JAGORAWI
            return [
                "JAGO",
            ];
        } else if ((int)$ruas_id == 39) { // CSJ
            return [
                "MTN",
                "JANGER",
                "MMS",
                "BSD",
                "JKC",
                "CSJ",
            ];
        } else if ((int)$ruas_id == 34) { // MTN
            return [
                "MTN",
                "BSD",
                "MMS",
                "BSD",
                "JKC",
                "CSJ",
            ];
        } else if ((int)$ruas_id == 40) { // JKC
            return [
                "MTN",
                "BSD",
                "MMS",
                "BSD",
                "JKC",
                "CSJ",
            ];
        }
    }
}
