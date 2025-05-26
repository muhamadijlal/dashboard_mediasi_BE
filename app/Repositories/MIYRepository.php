<?php

namespace App\Repositories;

use App\Models\DatabaseConfig;
use App\Models\Integrator;
use App\Models\Services\MIY\MIYServices;
use App\Models\Utils;
use Illuminate\Support\Facades\DB;

class MIYRepository
{
    public function getDataTransakiDetail($ruas_id, $gerbang_id, $start_date, $end_date, $shift_id, $golongan_id, $metoda_bayar_id)
    {
        try {
            DatabaseConfig::switchConnection($ruas_id, $gerbang_id);

            $query = DB::connection('mediasi')
                ->table("jid_transaksi_deteksi")
                ->select("gardu_id",
                    "shift",
                    "perioda",
                    "tgl_lap",
                    "tgl_transaksi",
                    "no_resi",
                    "gol_sah",
                    "metoda_bayar_sah",
                    "jenis_notran as validasi_notran",
                    "etoll_hash",
                    "tarif"
                )
                ->whereBetween('tgl_lap', values: [$start_date, $end_date]);

                if($shift_id != '*') {
                    $query->where("shift", $shift_id);
                }

                if($golongan_id != '*') {
                    $query->where("gol_sah", $golongan_id);
                }

                if($metoda_bayar_id != '*') {
                    $query->where("metoda_bayar_sah", $metoda_bayar_id);
                }

            return $query;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function getDataRekapAT4($ruas_id, $gerbang_id, $shift_id, $start_date, $end_date)
    {
        try {
            DatabaseConfig::switchConnection($ruas_id, $gerbang_id);

            $query = DB::connection('mediasi')
                ->table("jid_rekap_at4_miy")
                ->select("Shift", "Tanggal", "Tunai", "DinasOpr", "DinasMitra", "DinasKary", "eMandiri", "eBri", "eBni", "eBca", "eFlo", "RpTunai", DB::raw("0 AS RpDinasOpr"), "RpDinasMitra", "RpDinasKary", "RpeMandiri", "RpeBri", "RpeBni", "RpeBca", "RpeFlo")
                ->whereBetween('Tanggal', [$start_date, $end_date]);

            if($shift_id && $shift_id != '*')
            {
                $query = $query->where('shift', $shift_id);
            }

            return $query;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function getDataCompare($ruas_id, $gerbang_id, $shift_id, $metoda_bayar_id, $start_date, $end_date, $isSelisih)
    {
        try {
            DatabaseConfig::switchMultiConnection($ruas_id, $gerbang_id, 'integrator');
            $services = Integrator::services($ruas_id, $gerbang_id);

            // Query untuk tabel mediasi
            $query_mediasi = DB::connection('mediasi')
                ->table("jid_transaksi_deteksi")
                ->select(
                    "tgl_lap",
                    "gerbang_id",
                    "metoda_bayar_sah as metoda_bayar",
                    "shift",
                    DB::raw('COUNT(*) as jumlah_data'),
                    DB::raw('SUM(tarif) as jumlah_tarif_mediasi')
                )
                ->whereNotNull("ruas_id")
                ->whereBetween('tgl_lap', [$start_date, $end_date])
                ->where("gerbang_id", $gerbang_id * 1)
                ->groupBy("tgl_lap", "gerbang_id", "metoda_bayar_sah", "shift");

            $query_integrator = $services->getSourceCompare($start_date, $end_date, $gerbang_id);

            // Mendapatkan hasil dari query mediasi dan integrator
            $results_mediasi = $query_mediasi->get();
            $results_integrator = $query_integrator->get();

            $final_results = MIYServices::mappingDataMIY($ruas_id, $shift_id, $metoda_bayar_id, $results_integrator, $results_mediasi, $isSelisih);

            return $final_results;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function getDataSync($request)
    {
        try {
            DatabaseConfig::switchConnection($request->ruas_id, $request->gerbang_id, 'integrator');
            $services = Integrator::services($request->ruas_id, $request->gerbang_id);
            $query = $services->getSourceSync($request);

            return $query;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function syncData($request)
    {
        // Switch to the correct database connection based on the request parameters
        DatabaseConfig::switchConnection($request->ruas_id, $request->gerbang_id);

        // Begin a transaction on the "mediasi" connection
        DB::connection('mediasi')->beginTransaction();

        try {
            $mappedData = [];

            // Fetch the data to be synced
            $this->getDataSync($request)->orderBy('tgl_lap', 'ASC')->chunk(1000, function($chunkData) use(&$mappedData) {
                foreach ($chunkData as $item) {
                    list($metoda_bayar, $jenis_notran) = Utils::transmetod_miy_to_jid($item->metoda_bayar_sah);

                    $row = [
                        'asal_gerbang_id'            => $this->asalGerbang($item->asal_gerbang_id ?? NULL),
                        'gerbang_id'                 => $item->gerbang_id,
                        'gardu_id'                   => $item->gardu_id,
                        'tgl_lap'                    => $item->tgl_lap,
                        'shift'                      => $item->shift,
                        'perioda'                    => $item->perioda,
                        'no_resi'                    => $item->no_resi,
                        'gol_sah'                    => $item->gol_sah,
                        'etoll_id'                   => $this->add_zero_cardnum($item->NomorKartu),
                        'metoda_bayar_sah'           => $metoda_bayar,
                        'jenis_notran'               => $jenis_notran, # jenis notran
                        'tgl_transaksi'              => $item->tgl_transaksi,
                        'kspt_id'                    => $item->KsptId,
                        'pultol_id'                  => $item->PLTId,
                        'tgl_entrance'               => $item->tgl_entrance,
                        'etoll_hash'                 => $item->etoll_hash,
                        'tarif'                      => $item->tarif,
                        'sisa_saldo'                 => $item->Saldo,
                        'trf1'                       => $item->TarifInvestor1 ?? NULL,
                        'inv1'                       => $item->KodeInvestor1 ?? NULL,
                        'trf2'                       => $item->TarifInvestor2 ?? NULL,
                        'inv2'                       => $item->KodeInvestor2 ?? NULL,
                        'trf3'                       => $item->TarifInvestor3 ?? NULL,
                        'inv3'                       => $item->KodeInvestor3 ?? NULL,
                        'trf4'                       => $item->TarifInvestor4 ?? NULL,
                        'inv4'                       => $item->KodeInvestor4 ?? NULL,
                        'trf5'                       => $item->TarifInvestor5 ?? NULL,
                        'inv5'                       => $item->KodeInvestor5 ?? NULL,
                        'trf6'                       => $item->TarifInvestor6 ?? NULL,
                        'inv6'                       => $item->KodeInvestor6 ?? NULL,
                        'trf7'                       => $item->TarifInvestor7 ?? NULL,
                        'inv7'                       => $item->KodeInvestor7 ?? NULL,
                        'trf8'                       => $item->TarifInvestor8 ?? NULL,
                        'inv8'                       => $item->KodeInvestor8 ?? NULL,
                        'trf9'                       => $item->TarifInvestor9 ?? NULL,
                        'inv9'                       => $item->KodeInvestor9 ?? NULL,
                        'trf10'                      => $item->TarifInvestor10 ?? NULL,
                        'inv10'                      => $item->KodeInvestor10 ?? NULL,
                        'KodeIntegrator'             => $item->KodeIntegrator ?? NULL
                    ];

                    $mappedData[] = $row;
                }

                // Upsert per chunk (1000 data sekali proses)
                DB::connection('mediasi')->table('jid_transaksi_deteksi')->upsert(
                    $mappedData,
                    ['gerbang_id', 'gardu_id', 'tgl_lap', 'shift', 'perioda', 'no_resi', 'tgl_transaksi'], // unique key
                    [ // columns to update on duplicate
                        'asal_gerbang_id', 'gol_sah', 'etoll_id', 'metoda_bayar_sah', 'jenis_notran',
                        'kspt_id', 'pultol_id', 'tgl_entrance', 'etoll_hash', 'tarif',
                        'trf1', 'trf2', 'trf3', 'trf4', 'trf5', 'trf6', 'trf7', 'trf8', 'trf9', 'trf10',
                        'create_at', 'update_at'
                    ]
                );

                // Reset mappedData for next chunk
                $mappedData = [];
            });

            // Jika semua operasi berhasil, commit transaksi
            DB::connection('mediasi')->commit();

            return response()->json(['message' => "Syncronize data success!"], 201);
        } catch (\Exception $e) {
            DB::connection('mediasi')->rollBack();
            throw new \Exception($e->getMessage());
        }
    }

    private function asalGerbang($asalGerbangId)
    {
        return in_array($asalGerbangId, [NULL, "Null", "0", "", 0]) ? 0 : $asalGerbangId;
    }

    private function add_zero_cardnum($cardNumber)
    {
        if ($cardNumber == '' ||  $cardNumber == NULL) {
            return '';
        }

        $cardNumber = (string)$cardNumber;
        if (substr($cardNumber, 0, 2) == '14') {
            return "0" . (string)$cardNumber;
        }

        return $cardNumber;
    }
}
