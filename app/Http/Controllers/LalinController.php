<?php

namespace App\Http\Controllers;

use App\Models\Services\Lalin\LalinServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class LalinController extends Controller
{
    public function index()
    {
        return view("pages.mediasi.Lalin.index",[
            'columns' => [
                [
                    'title' => 'No',
                    'data' => 'DT_RowIndex',
                    'orderable' => false,
                    'searchable' => false,
                ],
                [
                    'title' => 'Tanggal Laporan',
                    'data' => 'tgl_lap',
                    'orderable' => true,
                    'searchable' => true,
                ],
                [
                    'title' => 'Ruas',
                    'data' => 'ruas_nama',
                    'orderable' => true,
                    'searchable' => true,
                ],
                [
                    'title' => 'Gerbang',
                    'data' => 'gerbang_nama',
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
                    'title' => 'Jumlah Data',
                    'data' => 'jumlah_data',
                    'orderable' => true,
                    'searchable' => true,
                ]
            ]
        ]);
    }

    public function getDataEntrance(Request $request)
    {
        $dataEntrance = LalinServices::mapping($request->start_date, $request->end_date, 'entrance');

        try {
            return DataTables::of($dataEntrance)
                    ->addIndexColumn()
                    ->make();

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'error' => true], 500);
        }
    }

    public function getDataExit(Request $request)
    {
        $dataExit = LalinServices::mapping($request->start_date, $request->end_date, 'exit');

        try {
            return DataTables::of($dataExit)
                    ->addIndexColumn()
                    ->make();

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'error' => true], 500);
        }
    }
}
