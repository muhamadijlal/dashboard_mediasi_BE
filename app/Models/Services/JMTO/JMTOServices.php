<?php

namespace App\Models\Services\JMTO;

use App\Models\Utils;

class JMTOServices
{
    private static function reduceNotran($mediasiData)
    {
        $groupedData = [];

        foreach ($mediasiData as $mediasi) {
            list($metodaBayar, $jenisNotran) = Utils::paymethod_notran($mediasi->metoda_bayar);

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

    private static function reducePaymethodJMTO($integratorData)
    {
        $groupedData = [];

        foreach ($integratorData as $integrator) {
            list($metodaBayar, $jenisNotran) = Utils::transmetod_jmto_to_jid(metoda_bayar: $integrator->metoda_bayar, jenis_ktp: $integrator->ktp_jenis_id);

            // Create key directly and group in single pass
            $key = "{$integrator->tgl_lap}_{$integrator->gerbang_id}_{$metodaBayar}_{$jenisNotran}_{$integrator->shift}";

            if (!isset($groupedData[$key])) {
                $groupedData[$key] = [
                    'tgl_lap' => $integrator->tgl_lap,
                    'gerbang_id' => $integrator->gerbang_id,
                    'metoda_bayar' => $metodaBayar,
                    'jenis_notran' => $jenisNotran,
                    'jenis_dinas' => 0,
                    'shift' => $integrator->shift,
                    'jumlah_data' => 0,
                    'jumlah_tarif_integrator' => 0
                ];
            }

            $groupedData[$key]['jumlah_data'] += $integrator->jumlah_data;
            $groupedData[$key]['jumlah_tarif_integrator'] += (float)$integrator->jumlah_tarif_integrator;
        }

        return $groupedData;
    }

    public static function mappingDataJMTO($integratorData, $mediasiData, $filterSelisih)
    {
        $final_results = [];
        $groupedData = Self::reducePaymethodJMTO($integratorData);
        $groupedMediasi = Utils::reduceNotran($mediasiData);

        foreach ($groupedData as $key => $group) {

            // Hitung jumlah integrator dan selisih
            $jumlah_data = $group['jumlah_data'];
            $selisih = $jumlah_data - ($groupedMediasi[$key] ? $groupedMediasi[$key]['jumlah_data'] : 0);

            // Membuat objek stdClass untuk hasil
            $final_result = new \stdClass();
            $final_result->tanggal = $group['tgl_lap'];
            $final_result->gerbang_id = $group['gerbang_id'];
            $final_result->metoda_bayar = $group['metoda_bayar'];
            $final_result->jenis_notran = $group['jenis_notran'];
            $final_result->jenis_dinas = $group['jenis_dinas'];
            $final_result->metoda_bayar_name = Utils::metode_bayar_jid($group['metoda_bayar'], $group['jenis_notran']);
            $final_result->shift = $group['shift'];
            $final_result->jumlah_data_integrator = $jumlah_data ?? 0;
            $final_result->jumlah_data_mediasi = $groupedMediasi[$key] ? $groupedMediasi[$key]['jumlah_data'] : 0;
            $final_result->selisih = $selisih;
            $final_result->jumlah_tarif_integrator = $groupedMediasi[$key] ? $group['jumlah_tarif_integrator'] : 0;
            $final_result->jumlah_tarif_mediasi = $groupedMediasi[$key] ? $groupedMediasi[$key]['jumlah_tarif_mediasi'] : 0;

            if ($filterSelisih === '*') {
                $final_results[] = $final_result;
            } elseif ($filterSelisih === '1' && $selisih > 0) {
                $final_results[] = $final_result;
            } elseif ($filterSelisih === '0' && $selisih == 0) {
                $final_results[] = $final_result;
            }
        }

        return $final_results;
    }
}
