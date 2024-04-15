<?php

use App\Http\Controllers\FlexFieldController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductCategoryController;
use Illuminate\Support\Facades\Route;

Route::controller(FlexFieldController::class)->group(function () {
    Route::get('flex-fields', 'getAll');                            
    Route::get('flex-fields/{id}', 'getOne');                       
    Route::get('flex-fields/config/{entity}', 'getAllOfEntity');    
    Route::post('flex-fields', 'storeOrUpdate');                    
    Route::put('flex-fields/{id}', 'storeOrUpdate');                
    Route::delete('flex-fields/{id}', 'delete');                    
});

// ProductController routes
Route::controller(ProductController::class)->group(function () {
    Route::get('/products', 'getAll');                                            
    Route::get('/products/{id}', 'getOne');                                                           
});

// ProductCategoryController routes
Route::controller(ProductCategoryController::class)->group(function () {
    Route::get('/product-categories', 'getAll');                                                            
    Route::get('/product-categories/{id}', 'getOne');                                 
    Route::post('/product-categories', 'storeOrUpdate');                                                             
    Route::put('/product-categories/{id}', 'storeOrUpdate');                           
    Route::delete('/product-categories/{id}', 'delete');                           
});

// OfferController routes
Route::controller(OfferController::class)->group(function () {
    Route::get('/offers', 'getAll');                                                            
    Route::get('/offers/{id}', 'getOne');                                 
    Route::post('/offers', 'storeOrUpdate');                                                             
    Route::put('/offers/{id}', 'storeOrUpdate');                           
    Route::delete('/offers/{id}', 'delete');                           
});