<?php

use App\Http\Controllers\Api\DataCompareController;
use App\Http\Controllers\Api\RekapAT4Controller;
use App\Http\Controllers\Api\TransaksiDetailController;
use App\Http\Controllers\Api\RekapPendapatanController;
use App\Http\Controllers\Api\RuasController;
use Illuminate\Support\Facades\Route;

Route::prefix("transaksi_detail")->group(function () {
    Route::post("/getData", TransaksiDetailController::class);
});

Route::prefix("rekap_at4")->group(function(){
    Route::post("/getData",RekapAT4Controller::class);
});

Route::prefix("rekap_pendapatan")->group(function(){
    Route::post("/getData",RekapPendapatanController::class);
});

Route::prefix("data_compare")->group(function(){
    Route::post("/getData",DataCompareController::class);
});

Route::prefix("select")->group(function(){
    Route::post("/getRuas",[RuasController::class, 'getRuas']);
    Route::post("/getGerbang",[RuasController::class, 'getGerbang']);
});
