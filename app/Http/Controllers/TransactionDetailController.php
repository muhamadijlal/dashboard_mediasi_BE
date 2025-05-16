<?php

namespace App\Http\Controllers;

use App\Models\Integrator;
use App\Http\Requests\FilterRequest;
use App\Models\Utils;
use App\Repositories\DigitalReceiptRepository;
use Yajra\DataTables\Facades\DataTables;

class TransactionDetailController extends Controller
{
    public function dashboard()
    {
        return view("pages.mediasi.TransaksiDetail.index", [
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
                    'title' => 'Waktu Transaksi',
                    'data' => 'tgl_transaksi',
                    'orderable' => true,
                    'searchable' => true,
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
            $query = $repository->getDataTransakiDetail($request->ruas_id, $request->gerbang_id, $request->start_date, $request->end_date, $request->shift_id, $request->golongan_id, $request->metoda_bayar_id);
            
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

    public function dashboard_resi()
    {
        return view("pages.resi.TransactionDetail.index", [
            'columns' => [
                [
                    'title' => 'No',
                    'data' => 'DT_RowIndex',
                    'orderable' => false,
                    'searchable' => false,
                ],
                [
                    'title' => 'Tanggal',
                    'data' => 'tgl_report',
                    'orderable' => true,
                    'searchable' => true,
                ],
                [
                    'title' => 'Waktu Transaksi',
                    'data' => 'tgl_transaksi',
                    'orderable' => true,
                    'searchable' => true,
                ],
                [
                    'title' => 'Gerbang',
                    'data' => 'nama_gerbang',
                    'orderable' => true,
                    'searchable' => true,
                ],
                [
                    'title' => 'Gardu',
                    'data' => 'kode_gardu',
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
                    'title' => 'Gerbang Asal',
                    'data' => 'nama_gerbang_asal',
                    'orderable' => true,
                    'searchable' => true,
                ],
                [
                    'title' => 'Metode Bayar',
                    'data' => 'bank',
                    'orderable' => true,
                    'searchable' => true,
                ],
                [
                    'title' => 'Tarif',
                    'data' => 'tarif',
                    'orderable' => true,
                    'searchable' => true,
                ],
                [
                    'title' => 'No Resi',
                    'data' => 'no_resi',
                    'orderable' => true,
                    'searchable' => true,
                ]
            ]
        ]);
    }

    public function getData_resi(FilterRequest $request)
    {
        try {
            // add custome validated field
            $request->validate([
                'card_num' => 'required|string'
            ]);
           
            $query = DigitalReceiptRepository::getDataTransakiDetail($request->ruas_id, $request->gerbang_id, $request->start_date, $request->end_date, $request->card_num);

            return DataTables::of($query)
                            ->addIndexColumn()
                            ->addColumn('tarif', function($row){
                                return 'Rp. '.number_format($row->tarif, 0,'.', '.');
                            })
                            ->addColumn('saldo', function($row){
                                return 'Rp. '.number_format($row->saldo, 0,'.', '.');
                            })
                            ->addColumn('bank', function($row){
                                return Utils::metode_bayar_jid($row->bank);
                            })
                            ->make();

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'error' => true], 500);
        }
    }
}
