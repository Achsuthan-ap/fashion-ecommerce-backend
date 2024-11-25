<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerNotificationController;
use App\Http\Controllers\FlexFieldController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductOfferController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StaticPageController;
use App\Http\Controllers\StockOrderController;
use App\Http\Controllers\StockOrderItemController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VendorController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

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
    Route::delete('/products/{id}', 'delete');                                                           
    Route::post('/subscribe/{productId}', 'subscribeToStockNotification');                                                           
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

// StaticPageController routes
Route::controller(StaticPageController::class)->group(function () {
    Route::get('/static-pages', 'getAll');                                                            
    Route::get('/static-pages/{id}', 'getOne');                                 
    Route::post('/static-pages', 'storeOrUpdate');                                                        
    Route::put('/static-pages/{id}', 'storeOrUpdate');                                                        
    Route::delete('/static-pages/{id}', 'delete');                                                        
});

// VenodrController routes
Route::controller(VendorController::class)->group(function () {
    Route::get('/vendors', 'getAll');                                                            
    Route::get('/vendors/{id}', 'getOne');                                                                
    Route::post('/vendors', 'storeOrUpdate');                                                             
    Route::put('/vendors/{id}', 'storeOrUpdate');                           
    Route::delete('/vendors/{id}', 'delete');                           
});

// StockOrderController routes
Route::controller(StockOrderController::class)->group(function () {
    Route::get('/stock-orders', 'getAll');                                                            
    Route::get('/stock-orders/{id}', 'getOne');                                                                
    Route::post('/stock-orders', 'storeOrUpdate');                                                             
    Route::put('/stock-orders/{id}', 'storeOrUpdate');                           
    Route::delete('/stock-orders/{id}', 'delete');                           
});

// StockOrderItemController routes
Route::controller(StockOrderItemController::class)->group(function () {
    Route::get('/stock-order-items', 'getAll');                                                            
    Route::get('/stock-order-items/{id}', 'getOne');                                                                
    Route::post('/stock-order-items', 'storeOrUpdate');                                                             
    Route::put('/stock-order-items/{id}', 'storeOrUpdate');                           
    Route::delete('/stock-order-items/{id}', 'delete');                           
});

// ProductOfferController routes
Route::controller(ProductOfferController::class)->group(function () {
    Route::get('/product-offers', 'getAll');                                                            
    Route::get('/product-offers/{id}', 'getOne');                                 
    Route::post('/product-offers', 'storeOrUpdate');                                                             
    Route::put('/product-offers/{id}', 'storeOrUpdate');                           
    Route::delete('/product-offers/{id}', 'delete');                           
});

Route::controller(ReportController::class)->group(function () {
    Route::post('/reports', 'generateReport');                                                                                      
});

Route::controller(PromotionController::class)->group(function () {
    Route::post('/promotions', 'store');
    Route::get('/promotions',  'index');
    Route::delete('/promotions/{id}',  'destroy');                                                                                  
});

Route::controller(PaymentController::class)->group(function () {
    Route::post('/payhere/notify', 'handlePayHereIPN');     
    Route::post('/payhere-proxy', 'handlePayHereProxy');
    Route::post('/create-checkout-session', 'createCheckoutSession');
                                                                                 
});

// CustomerNotificationController routes
Route::controller(CustomerNotificationController::class)->group(function () {
    Route::get('/customer-notifications', 'getAll');                                                            
    Route::get('/customer-notifications/{id}', 'getOne');                                 
    Route::post('/customer-notifications', 'storeOrUpdate');                                                             
    Route::put('/customer-notifications/{id}', 'storeOrUpdate');                           
    Route::delete('/customer-notifications/{id}', 'delete');                           
    Route::post('/subscription/send-notification/{id}', 'sendNotification');                           
});


