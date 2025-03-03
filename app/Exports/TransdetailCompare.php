<?php

namespace App\Exports;

use App\Http\Requests\FilterRequest;
use App\Models\Integrator;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;


class TransdetailCompare implements FromView
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }


    public function view(): View
    {
        return view('exports.transdetail', [
            'dataCompare' => $this->data
        ]);
    }
}
