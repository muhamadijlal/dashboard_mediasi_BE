<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilterRequest;
use App\Models\Integrator;
use Yajra\DataTables\DataTables;

class RekapAT4Controller extends Controller
{
    public function dashboard()
    {
        return view("pages.RekapAt4.index", [
            'columns' => [
                [
                    'title' => 'No',
                    'data' => 'DT_RowIndex',
                    'orderable' => false,
                    'searchable' => false,
                ],
                [
                    'title' => 'Shift',
                    'data' => 'Shift',
                    'orderable' => true,
                    'searchable' => true,
                ],
                [
                    'title' => 'Tunai',
                    'data' => 'Tunai',
                    'orderable' => true,
                    'searchable' => true,
                ],
                [
                    'title' => 'Dinas Opr',
                    'data' => 'DinasOpr',
                    'orderable' => true,
                    'searchable' => true,
                ],
                [
                    'title' => 'Dinas Mitra',
                    'data' => 'DinasMitra',
                    'orderable' => true,
                    'searchable' => true,
                ],
                [
                    'title' => 'Dinas Kary',
                    'data' => 'DinasKary',
                    'orderable' => true,
                    'searchable' => true,
                ],
                [
                    'title' => 'Etoll Mandiri',
                    'data' => 'eMandiri',
                    'orderable' => true,
                    'searchable' => true,
                ],
                [
                    'title' => 'Etoll BRI',
                    'data' => 'eBri',
                    'orderable' => true,
                    'searchable' => true,
                ],
                [
                    'title' => 'Etoll BNI',
                    'data' => 'eBni',
                    'orderable' => true,
                    'searchable' => true,
                ],
                [
                    'title' => 'Etoll BCA',
                    'data' => 'eBca',
                    'orderable' => true,
                    'searchable' => true,
                ],
                [
                    'title' => 'Etoll FLO',
                    'data' => 'eFlo',
                    'orderable' => true,
                    'searchable' => true,
                ],
                [
                    'title' => 'Pendapatan Tunai',
                    'data' => 'RpTunai',
                    'orderable' => true,
                    'searchable' => true,
                ],
                [
                    'title' => 'Pendapatan Dinas Opr',
                    'data' => 'RpDinasOpr',
                    'orderable' => true,
                    'searchable' => true,
                ],
                [
                    'title' => 'Pendapatan Dinas Mitra',
                    'data' => 'RpDinasMitra',
                    'orderable' => true,
                    'searchable' => true,
                ],
                [
                    'title' => 'Pendapatan Etoll Mandiri',
                    'data' => 'RpeMandiri',
                    'orderable' => true,
                    'searchable' => true,
                ],
                [
                    'title' => 'Pendapatan Etoll BRI',
                    'data' => 'RpeBri',
                    'orderable' => true,
                    'searchable' => true,
                ],
                [
                    'title' => 'Pendapatan Etoll BNI',
                    'data' => 'RpeBni',
                    'orderable' => true,
                    'searchable' => true,
                ],
                [
                    'title' => 'Pendapatan Etoll BCA',
                    'data' => 'RpeBca',
                    'orderable' => true,
                    'searchable' => true,
                ],
                [
                    'title' => 'Pendapatan Etoll FLO',
                    'data' => 'RpeFlo',
                    'orderable' => true,
                    'searchable' => true,
                ],
            ]
        ]);
    }

    public function getData(FilterRequest $request){
        try {
            $repository = Integrator::get($request->ruas_id, $request->gerbang_id);
            $query = $repository->getDataRekapAT4($request->ruas_id, $request->gerbang_id, $request->start_date, $request->end_date);

            return DataTables::of($query)
                            ->addIndexColumn()
                            ->addColumn('RpTunai', function($row){
                                return 'Rp. '.number_format($row->RpTunai, 0,'.','.');
                            })
                            ->addColumn('RpDinasOpr', function($row){
                                return 'Rp. '.number_format($row->RpDinasOpr, 0,'.','.');
                            })
                            ->addColumn('RpDinasMitra', function($row){
                                return 'Rp. '.number_format($row->RpDinasMitra, 0,'.','.');
                            })
                            ->addColumn('RpDinasKary', function($row){
                                return 'Rp. '.number_format($row->RpDinasKary, 0,'.','.');
                            })
                            ->addColumn('RpeMandiri', function($row){
                                return 'Rp. '.number_format($row->RpeMandiri, 0,'.','.');
                            })
                            ->addColumn('RpeBri', function($row){
                                return 'Rp. '.number_format($row->RpeBri, 0,'.','.');
                            })
                            ->addColumn('RpeBni', function($row){
                                return 'Rp. '.number_format($row->RpeBni, 0,'.','.');
                            })
                            ->addColumn('RpeBca', function($row){
                                return 'Rp. '.number_format($row->RpeBca, 0,'.','.');
                            })
                            ->addColumn('RpeFlo', function($row){
                                return 'Rp. '.number_format($row->RpeFlo, 0,'.','.');
                            })
                            ->make();

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'error' => true], 500);
        }
    }
}
