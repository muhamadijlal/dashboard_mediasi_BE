<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class Utils
{
    public static function paymethod_notran($metoda_bayar)
    {
        $metoda_transaksi = (int) $metoda_bayar;
        if (in_array($metoda_transaksi, [11, 12, 13, 21, 22, 23, 24, 25, 28])) {
            return [$metoda_bayar, 1];
        }

        return [0, 0];
    }

    public static function reduceNotran($mediasiData)
    {
        $groupedData = [];

        foreach ($mediasiData as $mediasi) {
            list($metodaBayar, $jenisNotran) = Self::paymethod_notran($mediasi->metoda_bayar);

            // Create key directly and group in single pass
            $key = "{$mediasi->tgl_lap}_{$mediasi->gerbang_id}_{$metodaBayar}_{$jenisNotran}_{$mediasi->shift}";

            if (!isset($groupedData[$key])) {
                $groupedData[$key] = [
                    'tgl_lap' => $mediasi->tgl_lap,
                    'gerbang_id' => $mediasi->gerbang_id,
                    'metoda_bayar' => $metodaBayar,
                    'jenis_notran' => $jenisNotran,
                    'jenis_dinas' => 0,
                    'shift' => $mediasi->shift,
                    'jumlah_data' => 0,
                    'jumlah_tarif_mediasi' => 0
                ];
            }

            $groupedData[$key]['jumlah_data'] += $mediasi->jumlah_data;
            $groupedData[$key]['jumlah_tarif_mediasi'] += (float)$mediasi->jumlah_tarif_mediasi;
        }

        return $groupedData;
    }


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

    public static function transmetod_jmto_to_jid($metoda_bayar, $jenis_notran = null, $jenis_ktp = null)
    {
        $metode_transaksi = (int) $metoda_bayar;

        // Mapping umum untuk transaksi yang memiliki kondisi jenis_notran == 1
        $payment_map_normal = [
            3 => ['21', '1'],
            13 => ['21', '1'],
            7 => ['24', '1'],
            4 => ['22', '1'],
            5 => ['23', '1'],
            8 => ['25', '1'],
            41 => ['28', '1'],
        ];

        // Jika metode transaksi ditemukan dalam mapping normal
        if (isset($payment_map_normal[$metode_transaksi])) {
            return $payment_map_normal[$metode_transaksi];
        }

        // Mapping untuk transaksi dengan metode 2 berdasarkan jenis_ktp
        $payment_map_metode_2 = [
            1 => ['11', '1'],
            2 => ['12', '1'],
            3 => ['13', '1'],
        ];

        // Cek apakah metode transaksi adalah 2, dan sesuaikan dengan jenis_ktp
        if ($metode_transaksi == 2 && isset($payment_map_metode_2[$jenis_ktp])) {
            return $payment_map_metode_2[$jenis_ktp];
        }

        // Jika tidak ada kondisi yang cocok, kembalikan nilai default (0, 0)
        return [0, 0];
    }

    public static function metode_bayar_jid($metoda_bayar, $jenis_notran = null)
    {
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
                return 'NOTRAN';
        }
    }

    public static function metode_bayar_jidMIY($metoda_bayar, $jenis_notran = null)
    {
        $metodeTransaksi = (int) $metoda_bayar;

        // Handle normal payment methods
        $reversePaymentMap = [
            "11" => "(1,17)", // JMC OPERASI
            "12" => "(12,81)", // JMC KARYAWAN
            "13" => "(13,19)", // JMC MITRA
            "21" => "(3)", // MANDIRI
            "22" => "(20)", // BRI
            "23" => "(21)", // BNI
            "24" => "(23)", // BCA
            "25" => "(25)", // DKI
            "28" => "(29)", // FLO
        ];

        $key = "$metodeTransaksi";
        if (isset($reversePaymentMap[$key])) {
            return "MetodeTransaksi IN " . $reversePaymentMap[$key]; // Normal transaction without special status
        }

        // Return default if no match found
        return "MetodeTransaksi NOT IN (1, 17, 12, 81, 13, 19, 3, 20, 21, 23, 25, 29)";
    }

    public static function transmetod_miy_to_jid($metoda_bayar, $jenis_notran = null, $validasi_notran = null)
    {
        $metodeTransaksi = (int) $metoda_bayar;

        $paymentMap = [
            1 => ["11", "1"], // JMC OPERASI
            17 => ["11", "1"], // JMC OPERASI
            12 => ["12", "1"], // JMC KARYAWAN
            18 => ["12", "1"], // JMC KARYAWAN
            13 => ["13", "1"], // JMC MITRA
            19 => ["13", "1"], // JMC MITRA
            3 => ["21", "1"], // MANDIRI
            20 => ["22", "1"], // BRI
            21 => ["23", "1"], // BNI
            23 => ["24", "1"], // BCA
            25 => ["25", "1"], // DKI
            29 => ["28", "1"], // FLO
        ];

        if (array_key_exists($metodeTransaksi, $paymentMap)) {
            return $paymentMap[$metodeTransaksi];
        }

        // Return a default value or error if no match is found
        return [0, 0];
    }

    public static function transmetod_db_to_jid($metoda_bayar, $jenis_dinas = null, $jenis_notran = null)
    {

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
            default:
                return [0, 0];
        }
    }

    public static function metode_bayar_jidDB($metoda_bayar, $jenis_notran = null, $jenis_dinas = null)
    {

        // $payMethodJMC = [
        //     "21_1"
        // ]

        $reversePaymentMap = [
            "21" => "('11','12')",
            "24" => "('18','19')",
            "22" => "('14','15')",
            "23" => "('9','16','17')",
            "25" => "('5','6')",
            "28" => "('31','32','60','61')",
            "11" => "('20')",
            "13" => "('22')",
        ];

        $key = "$metoda_bayar";
        if (isset($reversePaymentMap[$key])) {
            return "jenis_transaksi IN " . $reversePaymentMap[$key]; // Normal transaction without special status
        }

        // Return default if no match found
        return "jenis_transaksi IN ('40','3','80','82','81','83','84')";
    }

    public static function jmto_investor($ruas_id)
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
