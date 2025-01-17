<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FilterRequest;
use App\Models\Integrator;
use App\Traits\ResponseAPI;

class TransaksiDetailController extends Controller
{
    use ResponseAPI;

    public function __invoke(FilterRequest $request)
    {
        $limit = $request->limit ?? 10;

        try {
            $repository = Integrator::get($request->ruas_id, $request->gerbang_id);

            return $repository->getDataTransakiDetail($request->ruas_id, $request->gerbang_id, $request->start_date, $request->end_date, $limit);
        } catch(\Exception $e) {
            return $this->error($e->getMessage());
        }

        
    }
}