<?php

namespace App\Models\Services\JMTO;

class JMTOServices {
    public static function mergeMandiriPayMethod($collection)
    {
        // Separate and filter the collections for metoda_bayar = 3 and metoda_bayar = 13
        $metoda_bayar_3 = $collection->filter(function ($item) {
            return $item->metoda_bayar == 3;
        });

        $metoda_bayar_13 = $collection->filter(function ($item) {
            return $item->metoda_bayar == 13;
        });

        $merged = $metoda_bayar_3->map(function ($item) use ($metoda_bayar_13) {
            // Find corresponding metoda_bayar = 13 for the same shift and gerbang_id
            $matching_13 = $metoda_bayar_13->firstWhere('shift', $item->shift);

            if ($matching_13) {
                // Merge the values
                $item->jumlah_data += $matching_13->jumlah_data;
                $item->jumlah_tarif_integrator = (int)$item->jumlah_tarif_integrator + (int)$matching_13->jumlah_tarif_integrator;
            }

            // Change metoda_bayar from 3 to 21
            $item->metoda_bayar = 21;
            $item->jenis_notran = 1;

            return $item;
        });

        // Merge the entries for metoda_bayar = 13 that were not matched with metoda_bayar = 3
        $merged = $merged->merge($metoda_bayar_13->reject(function ($item) use ($metoda_bayar_3) {
            return $metoda_bayar_3->contains('shift', $item->shift);
        }));

        $result = $collection->filter(function ($item) {
            return !in_array($item->metoda_bayar, [13]);
        });

        return $result;
    }
}