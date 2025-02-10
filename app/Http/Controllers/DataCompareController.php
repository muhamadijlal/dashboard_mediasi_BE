<?php

namespace App\Http\Controllers;

use App\Models\DigitalReceipt;
use App\Http\Requests\FilterRequest;
use App\Models\Integrator;
use App\Models\Utils;
use App\Repositories\DigitalReceiptRepository;
use Yajra\DataTables\DataTables;

class DataCompareController extends Controller
{
    public function transaction_detail_dashboard(){
        return view("pages.mediasi.DataCompare.index", [
            'columns' => [
                [
                    'title' => 'No',
                    'data' => 'DT_RowIndex',
                    'orderable' => false,
                    'searchable' => false,
                ],
                [
                    'title' => 'Tanggal',
                    'data' => 'tanggal',
                    'orderable' => true,
                    'searchable' => true,
                ],
                [
                    'title' => 'Gerbang ID',
                    'data' => 'gerbang_id',
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
                    'title' => 'Gardu ID',
                    'data' => 'gardu_id',
                    'orderable' => true,
                    'searchable' => true,
                ],
                [
                    'title' => 'Jumlah Data Integrator',
                    'data' => 'jumlah_data_integrator',
                    'orderable' => true,
                    'searchable' => true,
                ],
                [
                    'title' => 'Jumlah Data Mediasi',
                    'data' => 'jumlah_data_mediasi',
                    'orderable' => true,
                    'searchable' => true,
                ],
                [
                    'title' => 'Selisih',
                    'data' => 'link',
                    'orderable' => true,
                    'searchable' => true,
                ],
            ]
        ]);
    }

    public function transaction_detail_getData(FilterRequest $request){
        try {
            $repository = Integrator::get($request->ruas_id, $request->gerbang_id);
            $query = $repository->getDataCompare($request->ruas_id, $request->gerbang_id, $request->start_date, $request->end_date, $request->selisih);

            return DataTables::of($query)
                            ->addIndexColumn()
                            ->addColumn('link', function ($row) use($request) {
                                if ($row->selisih > 0) {
                                    return '<a target="_blank" class="text-yellow-400" href="/mediasi/sync/dashboard/'. $request->ruas_id .'/'. $row->tanggal .'/'. $row->gerbang_id .'/'. $row->shift .'/'. $row->metoda_bayar_sah .'">'. $row->selisih .'</a>';
                                }else if($row->selisih < 0) {
                                    return '<span class="text-red-500">'.$row->selisih.'</span>';
                                }else {
                                    return $row->selisih;
                                }
                            })
                            ->rawColumns(['link'])
                            ->make();

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'error' => true], 500);
        }
    }

    public function digital_receipt_dashboard(){
        return view("pages.resi.DataCompare.index", [
            'columns' => [
                [
                    'title' => 'No',
                    'data' => 'DT_RowIndex',
                    'orderable' => false,
                    'searchable' => false,
                ],
                [
                    'title' => 'Tanggal',
                    'data' => 'tanggal',
                    'orderable' => true,
                    'searchable' => true,
                ],
                [
                    'title' => 'Gerbang ID',
                    'data' => 'gerbang_id',
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
                    'title' => 'Metoda Bayar',
                    'data' => 'metoda_bayar_sah',
                    'orderable' => true,
                    'searchable' => true,
                ],
                [
                    'title' => 'Jumlah Data DB Mediasi',
                    'data' => 'jumlah_data_integrator',
                    'orderable' => true,
                    'searchable' => true,
                ],
                [
                    'title' => 'Jumlah Data DB Resi',
                    'data' => 'jumlah_data_mediasi',
                    'orderable' => true,
                    'searchable' => true,
                ],
                [
                    'title' => 'Selisih',
                    'data' => 'link',
                    'orderable' => true,
                    'searchable' => true,
                ],
            ]
        ]);
    }

    public function digital_receipt_getData(FilterRequest $request){
        try {
           DigitalReceipt::switchDB($request->ruas_id, $request->gerbang_id);
           $query = DigitalReceiptRepository::getDataCompare($request->start_date, $request->end_date, $request->selisih);

            return DataTables::of($query)
                            ->addIndexColumn()
                            ->addColumn('link', function ($row) use($request) {
                                if ($row->selisih > 0) {
                                    return '<a target="_blank" class="text-yellow-400" href="/resi/sync/dashboard/'. $request->ruas_id .'/'. $row->tanggal .'/'. $row->gerbang_id .'/'. $row->shift .'/'. $row->metoda_bayar_sah .'">'. $row->selisih .'</a>';
                                }else if($row->selisih < 0) {
                                    return '<span class="text-red-500">'.$row->selisih.'</span>';
                                }else {
                                    return $row->selisih;
                                }
                            })
                            ->addColumn('metoda_bayar_sah', function($row) {
                                return Utils::metode_bayar_jid($row->metoda_bayar_sah);
                            })
                            ->rawColumns(['link'])
                            ->make();

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'error' => true], 500);
        }
    }
}
