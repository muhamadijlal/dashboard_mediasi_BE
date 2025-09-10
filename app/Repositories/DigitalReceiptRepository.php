<?php

namespace App\Repositories;

use App\Models\DigitalReceipt;
use Illuminate\Support\Facades\DB;

class DigitalReceiptRepository
{
   public static function getDataCompare(string $start_date=null, string $end_date=null, string $isSelisih)
   {
        try {
            // Query untuk tabel mediasi
            $query_mediasi = DB::connection(name: 'mediasi')
                                ->table("jid_transaksi_deteksi")
                                ->select("tgl_lap", "gerbang_id", "shift", "metoda_bayar_sah", DB::raw('COUNT(id) as jumlah_data'))
                                ->whereBetween('tgl_lap', [$start_date, $end_date])
                                ->whereIn('metoda_bayar_sah', ['21','22','23','24'])
                                ->groupBy("tgl_lap", "gerbang_id", "shift", "metoda_bayar_sah");

            // Query untuk tabel integrator
            $query_integrator = DB::connection('integrator')
                                ->table("jid_transaksi_deteksi")
                                ->select("tgl_lap", "gerbang_id", "shift", "metoda_bayar_sah", DB::raw('COUNT(id) as jumlah_data'))
                                ->whereBetween('tgl_lap', [$start_date, $end_date])
                                ->whereIn('metoda_bayar_sah', ['21','22','23','24'])
                                ->groupBy("tgl_lap", "gerbang_id", "shift", "metoda_bayar_sah");

            // Mendapatkan hasil dari query mediasi dan integrator
            $results_mediasi = $query_mediasi->get();
            $results_integrator = $query_integrator->get();

            // Gabungkan hasilnya
            $final_results = [];

            foreach($results_integrator as $integrator) {
                $index = $results_mediasi->search(function($mediasi) use($integrator) {
                    return $mediasi->tgl_lap == $integrator->tgl_lap && 
                        $mediasi->gerbang_id == $integrator->gerbang_id &&
                        $mediasi->shift == $integrator->shift &&
                        $mediasi->metoda_bayar_sah == $integrator->metoda_bayar_sah;
                });

                // Hitung jumlah integrator dan selisih
                $jumlah_data = $integrator->jumlah_data;
                $selisih = $jumlah_data - (($index !== false) ? $results_mediasi[$index]->jumlah_data : 0);

                // Membuat objek stdClass untuk hasil
                $final_result = new \stdClass();
                $final_result->tanggal = $integrator->tgl_lap;
                $final_result->gerbang_id = $integrator->gerbang_id;
                $final_result->shift = $integrator->shift;
                $final_result->metoda_bayar_sah = $integrator->metoda_bayar_sah;
                $final_result->jumlah_data_integrator = $jumlah_data ?? 0;
                $final_result->jumlah_data_mediasi = ($index !== false) ? $results_mediasi[$index]->jumlah_data : 0;
                $final_result->selisih = $selisih;

                if ($isSelisih === '*') {
                    $final_results[] = $final_result;
                } elseif ($isSelisih === '1' && $selisih > 0) {
                    $final_results[] = $final_result;
                } elseif ($isSelisih === '0' && $selisih == 0) {
                    $final_results[] = $final_result;
                }
            }

            return $final_results;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public static function getDataSync($request)
    {
        try {
            DigitalReceipt::switchDB($request->ruas_id, $request->gerbang_id);

            $query = DB::connection('integrator')
                        ->table('jid_transaksi_deteksi')
                        ->select('ruas_id',
                            'asal_gerbang_id',
                            'gerbang_id',
                            'gardu_id',
                            'tgl_lap',
                            'shift',
                            'perioda',
                            'no_resi',
                            'gol_sah',
                            'etoll_id',
                            'metoda_bayar_sah',
                            'jenis_notran',
                            'tgl_transaksi',
                            'kspt_id',
                            'pultol_id',
                            'tgl_entrance',
                            'etoll_hash',
                            'tarif',
                            'sisa_saldo',
                            'trf1',
                            'inv1',
                            'trf2',
                            'inv2',
                            'trf3',
                            'inv3',
                            'trf4',
                            'inv4',
                            'trf5',
                            'inv5',
                            'trf6',
                            'inv6',
                            'trf7',
                            'inv7',
                            'trf8',
                            'inv8',
                            'trf9',
                            'inv9',
                            'trf10',
                            'inv10',
                            'KodeIntegrator',
                            'create_at',
                            'update_at'
                        )
                        ->whereBetween('tgl_lap', [$request->start_date, $request->end_date])
                        ->where('ruas_id', $request->ruas_id)
                        ->where("gerbang_id", $request->gerbang_id*1);

                $query->when($request->has('card_num'), function ($query) use ($request) {
                    $query->where('etoll_id', $request->card_num);
                }, function ($query) use($request) {
                    $query->where("metoda_bayar_sah", $request->metoda_bayar);
                    $query->where("shift", $request->shift);
                });
                        
            return $query;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage()); 
        }
    }

    public static function syncData($request)
    {
        DigitalReceipt::switchDB($request->ruas_id, $request->gerbang_id);
        $query = Self::getDataSync($request);
        
        // Begin a transaction on the "mediasi" connection
        DB::connection('mediasi')->beginTransaction();

        try {
            // Fetch the data to be synced
            $data = self::getDataSync($request);
            $result = $data->get();

            if (count($result) === 0) {
                throw new \Exception("Data empty cannot sync");
            }            

            foreach ($result as $dataItem) {
                // Define the SQL query with placeholders for parameterized queries
                $query = "INSERT INTO jid_transaksi_deteksi (
                        asal_gerbang_id,
                        gerbang_id,
                        gardu_id,
                        tgl_lap,
                        shift,
                        perioda,
                        no_resi,
                        gol_sah,
                        etoll_id,
                        metoda_bayar_sah,
                        jenis_notran,
                        tgl_transaksi,
                        kspt_id,
                        pultol_id,
                        tgl_entrance,
                        etoll_hash,
                        tarif,
                        sisa_saldo,
                        trf1,
                        inv1,
                        trf2,
                        inv2,
                        trf3,
                        inv3,
                        trf4,
                        inv4,
                        trf5,
                        inv5,
                        trf6,
                        inv6,
                        trf7,
                        inv7,
                        trf8,
                        inv8,
                        trf9,
                        inv9,
                        trf10,
                        inv10,
                        KodeIntegrator,
                        create_at,
                        update_at
                    ) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                        ruas_id = VALUES(ruas_id),
                        gerbang_id = VALUES(gerbang_id),
                        gardu_id = VALUES(gardu_id),
                        gol_sah = VALUES(gol_sah),
                        tgl_lap = VALUES(tgl_lap),
                        shift = VALUES(shift),
                        no_resi = VALUES(no_resi),
                        tgl_transaksi = VALUES(tgl_transaksi)
                ";

                // Bind the data for the prepared statement
                $params = [
                    $dataItem->asal_gerbang_id, 
                    $dataItem->gerbang_id, 
                    $dataItem->gardu_id, 
                    $dataItem->tgl_lap, 
                    $dataItem->shift, 
                    $dataItem->perioda, 
                    $dataItem->no_resi, 
                    $dataItem->gol_sah, 
                    $dataItem->etoll_id, 
                    $dataItem->metoda_bayar_sah, 
                    $dataItem->jenis_notran, 
                    $dataItem->tgl_transaksi, 
                    $dataItem->kspt_id, 
                    $dataItem->pultol_id, 
                    $dataItem->tgl_entrance, 
                    $dataItem->etoll_hash, 
                    $dataItem->tarif, 
                    $dataItem->sisa_saldo, 
                    $dataItem->trf1,
                    $dataItem->inv1,
                    $dataItem->trf2,
                    $dataItem->inv2,
                    $dataItem->trf3,
                    $dataItem->inv3,
                    $dataItem->trf4,
                    $dataItem->inv4,
                    $dataItem->trf5,
                    $dataItem->inv5,
                    $dataItem->trf6,
                    $dataItem->inv6,
                    $dataItem->trf7,
                    $dataItem->inv7,
                    $dataItem->trf8,
                    $dataItem->inv8,
                    $dataItem->trf9,
                    $dataItem->inv9,
                    $dataItem->trf10,
                    $dataItem->inv10,
                    $dataItem->KodeIntegrator,
                    $dataItem->create_at,
                    $dataItem->update_at
                ];

                // Execute the statement on the "mediasi" connection
                DB::connection('mediasi')->statement($query, $params);              
            }

            // If all operations were successful, commit the transaction
            DB::connection('mediasi')->commit();

            // Return success message
            return response()->json(['message' => "Syncronize data success!"], 201);
        } catch (\Exception $e) {
            // If an error occurs, roll back the transaction
            DB::connection('mediasi')->rollBack();
            // Throw the exception to be handled elsewhere
            throw new \Exception($e->getMessage());
        }
    }

    public static function getDataTransakiDetail($ruas_id, $gerbang_id,  $start_date, $end_date, $card_num)
    {
        try {
            DigitalReceipt::switchConnection();

            $query = DB::connection('mediasi')
                    ->table("tx_card_toll_history")
                    ->select("tgl_report",
                        "tgl_transaksi",
                        "nama_gerbang",
                        // "tgl_lap",
                        "tgl_transaksi",
                        "kode_gardu",
                        "nama_gerbang_asal",
                        "bank",
                        "shift",
                        "tarif",
                        "no_resi",
                        "saldo"
                    )
                    ->whereBetween("tgl_report", [$start_date, $end_date])
                    ->where("no_kartu", $card_num);

            if($ruas_id != "*")
            {
                $query->where("kode_cabang", $ruas_id);
                $query->where("gerbang", $gerbang_id*1);
            }

            return $query;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage()); 
        }
    }
}