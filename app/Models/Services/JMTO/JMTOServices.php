<?php

namespace App\Models\Services\JMTO;

use App\Models\Utils;

class JMTOServices
{
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

    public static function mappingDataJMTO($ruas_id, $shift_id, $metoda_bayar_id, $integratorData, $mediasiData, $filterSelisih)
    {
        $final_results = [];
        $groupedData = Self::reducePaymethodJMTO($integratorData);
        $groupedMediasi = Utils::reduceNotran($mediasiData);

        $source = count($groupedData) === 0 ? $groupedMediasi : $groupedData;

        foreach ($source as $key => $group) {
            $isFromMediasi = (count($groupedData) === 0);

            $jumlah_integrator = $isFromMediasi ? 0 : $group['jumlah_data'];
            $jumlah_mediasi = $isFromMediasi ? $group['jumlah_data'] : ($groupedMediasi[$key]['jumlah_data'] ?? 0);
            $selisih = $jumlah_integrator - $jumlah_mediasi;

            $final_result = new \stdClass();
            $final_result->tanggal = $group['tgl_lap'];
            $final_result->gerbang_id = $group['gerbang_id'];
            $final_result->gerbang_nama = Utils::gerbang_nama($ruas_id, $group['gerbang_id']);
            $final_result->metoda_bayar = $group['metoda_bayar'];
            $final_result->jenis_notran = $group['jenis_notran'];
            $final_result->jenis_dinas = $group['jenis_dinas'];
            $final_result->metoda_bayar_name = Utils::metode_bayar_jid($group['metoda_bayar']);
            $final_result->shift = $group['shift'];
            $final_result->jumlah_data_integrator = $jumlah_integrator;
            $final_result->jumlah_data_mediasi = $jumlah_mediasi;
            $final_result->selisih = $selisih;
            $final_result->jumlah_tarif_integrator = $isFromMediasi ? 0 : ($group['jumlah_tarif_integrator'] ?? 0);
            $final_result->jumlah_tarif_mediasi = $isFromMediasi
                ? $group['jumlah_tarif_mediasi']
                : ($groupedMediasi[$key]['jumlah_tarif_mediasi'] ?? 0);

            $shouldIncludeBySelisih =
                $filterSelisih === '*' ||
                ($filterSelisih === '1' && $selisih > 0) ||
                ($filterSelisih === '0' && $selisih == 0);

            $shouldIncludeByShift =
                $shift_id === '*' || $shift_id === (string)$group['shift'];

            $shouldIncludeByMetodaBayar =
                $metoda_bayar_id === '*' || $metoda_bayar_id === (string)$group['metoda_bayar'];

            if ($shouldIncludeBySelisih && $shouldIncludeByShift && $shouldIncludeByMetodaBayar) {
                $final_results[] = $final_result;
            }
        }

        return $final_results;
    }
}
