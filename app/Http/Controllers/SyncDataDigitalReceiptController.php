<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilterRequest;
use App\Models\DigitalReceipt;
use App\Models\Utils;
use Illuminate\Http\Request;
use App\Repositories\DigitalReceiptRepository;
use Yajra\DataTables\Facades\DataTables;

class SyncDataDigitalReceiptController extends Controller
{
    public function dashboard($ruas_id = null, $tanggal = null, $gerbang_id = null, $shift = null, $metoda_bayar = null) {

        $filter = Utils::getRuasnGerbangName($ruas_id, $gerbang_id);

        return view("pages.SyncData.DigitalReceipt.index", [
            'columns' => [
                [
                    'title' => 'No',
                    'data' => 'DT_RowIndex',
                    'orderable' => false,
                    'searchable' => false,
                ],
                [
                    'title' => 'Gardu ID',
                    'data' => 'gardu_id',
                    'orderable' => true,
                    'searchable' => true,
                ],
                [
                    'title' => 'Shift',
                    'data' => 'shift',
                    'orderable' => true,
                    'searchable' => true,
                ],
                [
                    'title' => 'Perioda',
                    'data' => 'perioda',
                    'orderable' => true,
                    'searchable' => true,
                ],
                [
                    'title' => 'No Resi',
                    'data' => 'no_resi',
                    'orderable' => true,
                    'searchable' => true,
                ],
                [
                    'title' => 'Golongan',
                    'data' => 'gol_sah',
                    'orderable' => true,
                    'searchable' => true,
                ],
                [
                    'title' => 'Metoda Bayar',
                    'data' => 'metoda_bayar_sah',
                    'orderable' => true,
                    'searchable' => true,
                ],
                [
                    'title' => 'Jenis Notran',
                    'data' => 'jenis_notran',
                    'orderable' => true,
                    'searchable' => true,
                ],
                [
                    'title' => 'Etoll Hash',
                    'data' => 'etoll_hash',
                    'orderable' => true,
                    'searchable' => true,
                ],
                [
                    'title' => 'Tarif',
                    'data' => 'tarif',
                    'orderable' => true,
                    'searchable' => true,
                ],
            ],
            'params' => [
                'ruas_id' => $ruas_id,
                'ruas_nama' => $filter->ruas_nama,
                'start_date' => $tanggal,
                'end_date' => $tanggal,
                'gerbang_id' =>  $gerbang_id, 
                'gerbang_nama' => $filter->gerbang_nama,
                'metoda_bayar' => $metoda_bayar,
                'shift' => $shift,
            ]
        ]);
    }

    public function getData(Request $request) 
    {
        try {
            $request->validate([
                'ruas_id' => 'required|string',
                'start_date' => 'required|date|date_format:Y-m-d',
                'end_date' => 'required|date|date_format:Y-m-d',
                'gerbang_id' => 'required|string',
                'metoda_bayar' => 'required|string',
                'shift' => 'required|string',
            ]);

            DigitalReceipt::switchDB($request->ruas_id, $request->gerbang_id);
            $query = DigitalReceiptRepository::getDataSync($request);

            return DataTables::of($query)
                        ->addColumn('tarif', function($row){
                            return  "Rp. ".number_format($row->tarif, 0, '.', '.');
                        })
                        ->addIndexColumn()
                        ->make();

        } catch(\Exception $e) { 
            return response()->json(['message' => $e->getMessage(), 'error' => true], 500);
        }
    }

    public function syncData(Request $request) {
        try{

            $request->validate([
                'ruas_id' => 'required|string',
                'start_date' => 'required|date|date_format:Y-m-d',
                'end_date' => 'required|date|date_format:Y-m-d',
                'gerbang_id' => 'required|string',
                'metoda_bayar' => 'required|string',
                'shift' => 'required|string',
            ]);

            return DigitalReceiptRepository::syncData($request);

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage()); 
        }
    }

    public function transcation_detail_dashboard() {
        return view("pages.SyncData.DigitalReceipt.TransactionDetail.index", [
            'columns' => [
                [
                    'title' => 'No',
                    'data' => 'DT_RowIndex',
                    'orderable' => false,
                    'searchable' => false,
                ],
                [
                    'title' => 'Gardu ID',
                    'data' => 'gardu_id',
                    'orderable' => true,
                    'searchable' => true,
                ],
                [
                    'title' => 'Shift',
                    'data' => 'shift',
                    'orderable' => true,
                    'searchable' => true,
                ],
                [
                    'title' => 'Perioda',
                    'data' => 'perioda',
                    'orderable' => true,
                    'searchable' => true,
                ],
                [
                    'title' => 'No Resi',
                    'data' => 'no_resi',
                    'orderable' => true,
                    'searchable' => true,
                ],
                [
                    'title' => 'Golongan',
                    'data' => 'gol_sah',
                    'orderable' => true,
                    'searchable' => true,
                ],
                [
                    'title' => 'Metoda Bayar',
                    'data' => 'metoda_bayar_sah',
                    'orderable' => true,
                    'searchable' => true,
                ],
                [
                    'title' => 'Jenis Notran',
                    'data' => 'jenis_notran',
                    'orderable' => true,
                    'searchable' => true,
                ],
                [
                    'title' => 'Etoll Hash',
                    'data' => 'etoll_hash',
                    'orderable' => true,
                    'searchable' => true,
                ],
                [
                    'title' => 'Tarif',
                    'data' => 'tarif',
                    'orderable' => true,
                    'searchable' => true,
                ],
            ]
        ]);
    }

    public function transcation_detail_getData(FilterRequest $request)
    {
        try {
            // add custome validated field
            $request->validate([
                'card_num' => 'required|string'
            ]);

            DigitalReceipt::switchDB($request->ruas_id, $request->gerbang_id);
            $query = DigitalReceiptRepository::getDataSync($request);

            return DataTables::of($query)
                            ->addIndexColumn()
                            ->make();
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'error' => true], 500);
        }
    }

    public function transcation_detail_syncData(FilterRequest $request)
    {
        try{
            $request->validate([
                'card_num' => 'required|string'
            ]);
    
            DigitalReceiptRepository::syncData($request);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'error' => true], 500);
        }
    }
}
