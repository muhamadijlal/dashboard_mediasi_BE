<?php

namespace App\Models\Services\MIY;

use App\Models\Utils;

class MIYServices
{
    private static function reducePaymethodMIY($integratorData)
    {
        $groupedData = [];

        foreach ($integratorData as $integrator) {
            list($metodaBayar, $jenisNotran) = Utils::transmetod_miy_to_jid($integrator->metoda_bayar);

            // Create key directly and group in single pass
            $key = "{$integrator->tgl_lap}_{$integrator->gerbang_id}_{$metodaBayar}_{$jenisNotran}_{$integrator->shift}";

            if (!isset($groupedData[$key])) {
                $groupedData[$key] = [
                    'tgl_lap' => $integrator->tgl_lap,
                    'gerbang_id' => $integrator->gerbang_id,
                    'metoda_bayar' => $metodaBayar,
                    'metoda_bayar_old' => $integrator->metoda_bayar,
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

    public static function mappingDataMIY($ruas_id, $shift_id, $metoda_bayar_id, $integratorData, $mediasiData, $filterSelisih)
    {
        $final_results = [];
        $groupedData = Self::reducePaymethodMIY($integratorData);
        $groupedMediasi = Utils::reduceNotran($mediasiData);

        $source = count($groupedData) === 0 ? $groupedMediasi : $groupedData;

        foreach ($source as $key => $group) {
            $isFromMediasi = count($groupedData) === 0;

            $jumlah_integrator = $isFromMediasi ? 0 : ($group['jumlah_data'] ?? 0);
            $jumlah_mediasi = $isFromMediasi ? $group['jumlah_data'] : ($groupedMediasi[$key]['jumlah_data'] ?? 0);
            $selisih = $jumlah_integrator - $jumlah_mediasi;

            $final_result = new \stdClass();
            $final_result->tanggal = $group['tgl_lap'];
            $final_result->gerbang_id = $group['gerbang_id'];
            $final_result->gerbang_nama = Utils::gerbang_nama($ruas_id, $group['gerbang_id']);
            $final_result->metoda_bayar = $group['metoda_bayar'];
            $final_result->metoda_bayar_name = Utils::metode_bayar_jid($group['metoda_bayar']);
            $final_result->jenis_notran = $group['jenis_notran'];
            $final_result->jenis_dinas = $group['jenis_dinas'] ?? null;
            $final_result->shift = $group['shift'];
            $final_result->jumlah_data_integrator = $jumlah_integrator;
            $final_result->jumlah_data_mediasi = $jumlah_mediasi;
            $final_result->selisih = $selisih;
            $final_result->jumlah_tarif_integrator = $isFromMediasi ? 0 : ($group['jumlah_tarif_integrator'] ?? 0);
            $final_result->jumlah_tarif_mediasi = $isFromMediasi ? ($group['jumlah_tarif_mediasi'] ?? 0) : ($groupedMediasi[$key]['jumlah_tarif_mediasi'] ?? 0);

            // Filter berdasarkan selisih
            $isSelisihValid = $filterSelisih === '*' ||
                ($filterSelisih === '1' && $selisih > 0) ||
                ($filterSelisih === '0' && $selisih === 0);

            // Filter shift
            $isShiftValid = $shift_id === '*' || $group['shift'] == $shift_id;

            // Filter metoda bayar
            $isMetodaBayarValid = $metoda_bayar_id === '*' || $group['metoda_bayar'] == $metoda_bayar_id;

            if ($isSelisihValid && $isShiftValid && $isMetodaBayarValid) {
                $final_results[] = $final_result;
            }
        }

        return $final_results;
    }
}
