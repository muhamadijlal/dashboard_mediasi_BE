<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DataCompareController;
use App\Http\Controllers\RekapAT4Controller;
use App\Http\Controllers\Select2Controller;
use App\Http\Controllers\SyncDataController;
use App\Http\Controllers\TransactionDetailController;

Route::redirect("/","/transaction_detail/dashboard");

Route::prefix("transaction_detail")->name("transaction_detail.")->group(function(){
    Route::get("/dashboard", [TransactionDetailController::class, "dashboard"])->name("dashboard");
    Route::post("/getData", [TransactionDetailController::class, "getData"])->name("getData");
});

Route::prefix("recap_at4")->name("recap_at4.")->group(function(){
    Route::get("/dashboard", [RekapAT4Controller::class, "dashboard"])->name("dashboard");
    Route::post("/getData", [RekapAT4Controller::class, "getData"])->name("getData");
});

Route::prefix("data_compare")->name("data_compare.")->group(function(){
    Route::prefix("transaction_detail")->name('transaction_detail.')->group(function(){
        Route::get("/dashboard", [DataCompareController::class, "transaction_detail_dashboard"])->name("dashboard");
        Route::post("/getData", [DataCompareController::class, "transaction_detail_getData"])->name("getData");
    });

    // Route::prefix("digital_receipt")->name('digital_receipt.')->group(function(){
    //     Route::get("/dashboard", [DataCompareController::class, "digital_receipt_dashboard"])->name("dashboard");
    //     Route::post("/getData", [DataCompareController::class, "digital_receipt_getData"])->name("getData");
    // });
});

Route::prefix("sync")->name("sync.")->group(function(){
    Route::get("/dashboard/{ruas_id?}/{tanggal?}/{gerbang_id?}/{golongan?}/{gardu_id?}/{shift?}", [SyncDataController::class, "dashboard"])->name("dashboard");
    Route::post("/getData", [SyncDataController::class, "getData"])->name("getData");
    Route::post("/syncData", [SyncDataController::class, "syncData"])->name("syncData");
});

Route::prefix("select2")->name("select2.")->group(function() {
    Route::post("/getRuas", [Select2Controller::class, "getRuas"])->name("getRuas");
    Route::post("/getGerbang", [Select2Controller::class, "getGerbang"])->name("getGerbang");
});
