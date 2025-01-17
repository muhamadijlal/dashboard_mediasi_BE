<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FilterRequest;
use App\Models\Integrator;

class DataCompareController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(FilterRequest $request)
    {
        $limit = $request->limit ?? 10;

        $repository = Integrator::get($request->ruas_id, $request->gerbang_id);
        return $repository->getDataCompare($request->ruas_id, $request->gerbang_id, $request->start_date, $request->end_date, $limit);
    }
}
