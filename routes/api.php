<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\FlexFieldController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// FLexFieldController routes
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
    Route::post('/products', 'storeOrUpdate');                                                           
    Route::put('/products/{id}', 'storeOrUpdate');                                                           
    Route::delete('/products/{id}', 'storeOrUpdate');                                                           
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

// CusotmerController routes
Route::controller(CustomerController::class)->group(function () {
    Route::get('/customers', 'getAll');                                                            
    Route::get('/customers/{id}', 'getOne');                                 
    Route::get('/customers/user/{userId}', 'getOneByUserId');                                 
    Route::post('/customers', 'storeOrUpdate');                                                             
    Route::put('/customers/{id}', 'storeOrUpdate');                           
    Route::delete('/customers/{id}', 'delete');                           
});

// UserController routes
Route::controller(UserController::class)->group(function () {
    Route::post('register', 'register');
    Route::post('login', 'login');
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('user', 'getAuthenticatedUser');
        Route::get('users', 'getAll');
        Route::get('user/{id}', 'getOne');
        Route::post('user', 'storeOrUpdate');
        Route::put('user/{id}', 'storeOrUpdate');
        Route::delete('user/{id}', 'delete');
    });
});

// CartController routes
Route::controller(CartController::class)->group(function () {
    Route::get('/carts', 'getAll');                                                            
    Route::get('/carts/{id}', 'getOne');                                 
    Route::get('/carts/user/{userId}', 'getUserCart');
    Route::post('/carts/user', 'storeOrUpdateUserCart');                                                        
    Route::put('/carts/user/{userId}', 'storeOrUpdateUserCart');                                                        
    Route::delete('/carts/user/{userId}/{itemId}', 'deleteUserCartProduct');                                                        
    Route::delete('/carts/user/{userId}', 'deleteUserCart');                                                        
});

// OrderController routes
Route::controller(OrderController::class)->group(function () {
    Route::get('/orders', 'getAll');                                                            
    Route::get('/orders/{id}', 'getOne');                                 
    Route::post('/orders', 'storeOrUpdate');                                                        
    Route::put('/orders/{id}', 'storeOrUpdate');                                                        
    Route::delete('/orders/{id}', 'delete');                                                        
});