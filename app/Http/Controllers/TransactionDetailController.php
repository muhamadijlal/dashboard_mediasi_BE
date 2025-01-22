<?php

namespace App\Http\Controllers;

use App\Models\Integrator;
use App\Http\Requests\FilterRequest;
use Yajra\DataTables\Facades\DataTables;

class TransactionDetailController extends Controller
{
    public function dashboard()
    {
        return view("pages.TransaksiDetail.index", [
            'columns' => [
                [
                    'title' => 'No',
                    'data' => 'DT_RowIndex',
                    'orderable' => false,
                    'searchable' => false,
                ],
                [
                    'title' => 'Gardu',
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
                    'title' => 'Notran',
                    'data' => 'validasi_notran',
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
                ]
            ]
        ]);
    }

    public function getData(FilterRequest $request){
        try {
            $repository = Integrator::get($request->ruas_id, $request->gerbang_id);
            $query = $repository->getDataTransakiDetail($request->ruas_id, $request->gerbang_id, $request->start_date, $request->end_date);

            return DataTables::of($query)
                            ->addColumn('tarif', function ($data) {
                                return 'Rp. '.number_format($data->tarif, 0, '.', '.');
                            })
                            ->addIndexColumn()
                            ->make();

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'error' => true], 500);
        }
    }
}
