<?php

namespace App\Http\Controllers;

use App\Models\Integrator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Yajra\DataTables\DataTables;

class SyncDataController extends Controller
{
    public function dashboard($ruas_id = null, $tanggal = null, $gerbang_id = null, $golongan = null, $gardu_id = null, $shift = null) {
        return view("pages.SyncData.index", [
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
                    'data' => 'gol',
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
                    'data' => 'notran_id_sah',
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
                'tanggal' => $tanggal,
                'gerbang_id' =>  $gerbang_id, 
                'golongan' => $golongan,
                'gardu_id' => $gardu_id,
                'shift' => $shift,
            ]
        ]);
    }

    public function getData(Request $request) {
        try {
            $request->validate([
                'ruas_id' => 'required|string',
                'tanggal' => 'required|date|date_format:Y-m-d',
                'gerbang_id' => 'required|string',
                'golongan' => 'required|string',
                'gardu_id' => 'required|string',
                'shift' => 'required|string',
            ]);

            $repository = Integrator::get($request->ruas_id, $request->gerbang_id);
            $query = $repository->getDataSync($request);

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
    public function syncData() {}
}
