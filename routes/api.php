<?php

use App\Http\Controllers\BarangController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/test',function(){
    return response()->json([
        'success'=>'true'
    ]);
});


Route::prefix('/barang')->name('barang.')->controller(BarangController::class)->group(function(){
    Route::get('/','index')->name('index');
    Route::get('/{id}','show')->name('show');
    Route::post('/store','store')->name('store');
    Route::match(['get','post'],'/update/{id}','update')->name('update');
    Route::delete('/delete/{id}','destroy')->name('destroy');
    Route::get('/export/excel','exportxlsx')->name('export_xlsx');
    Route::get('/export/csv','exportcsv')->name('export_csv');
});