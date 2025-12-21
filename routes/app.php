<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

use App\Http\Controllers\Page\WalletController;
use App\Http\Controllers\Page\WishlistController;
use App\Http\Controllers\Page\PaymentController;
use App\Http\Controllers\Page\OrderController;
use App\Http\Controllers\Page\FollowingStoresController;
use App\Http\Controllers\Page\StoreController;
use App\Http\Controllers\Page\HomeController;
use App\Http\Controllers\Page\ProductCollectionController;

/*
|===================================================================
|> Home Route
|===================================================================
*/

Route::get('/following', [FollowingStoresController::class, 'index'])->name('following.index')->middleware('auth:sanctum');
Route::get('/store/{id}', [StoreController::class, 'index'])->name('store.index');

Route::get('/home', [HomeController::class, 'index'])->name('home.index');
Route::get('/home/following', [HomeController::class, 'getFollowedProducts'])->name('home.following')->middleware('auth:sanctum');


Route::get('/collections/most-demanded', [ProductCollectionController::class, 'mostDemanded']);
Route::get('/collections/promoted',      [ProductCollectionController::class, 'promoted']);
Route::get('/collections/followed',      [ProductCollectionController::class, 'followed']);
Route::get('/collections/trending/{categoryId}', [ProductCollectionController::class, 'trendingByCategory']);
Route::get('/collections/event',      [ProductCollectionController::class, 'getVendorProducts']);


/*
|===================================================================
|> Wallet Routes
|===================================================================
*/

// Wallet dashboard
Route::get('/wallet', [WalletController::class, 'index'])
    ->name('wallet.index')
    ->middleware('auth:sanctum');

// Add funds routes
Route::get('/wallet/add-funds', [WalletController::class, 'showAddFunds'])
    ->name('wallet.add-funds.show')
    ->middleware('auth:sanctum');
    
Route::post('/wallet/add-funds', [WalletController::class, 'addFunds'])
    ->name('wallet.add-funds')
    ->middleware('auth:sanctum');

// Withdraw funds routes
Route::get('/wallet/withdraw', [WalletController::class, 'showWithdrawFunds'])
    ->name('wallet.withdraw.show')
    ->middleware('auth:sanctum');
    
Route::post('/wallet/withdraw', [WalletController::class, 'withdrawFunds'])
    ->name('wallet.withdraw')
    ->middleware('auth:sanctum');

// Transaction success page
Route::get('/wallet/transaction/{transaction}/success', [WalletController::class, 'transactionSuccess'])
    ->name('wallet.transaction.success')
    ->middleware('auth:sanctum');

/*
|===================================================================
|> Wishlist Routes
|===================================================================
*/

// Wishlist resource routes
Route::middleware('auth:sanctum')->group(function () {
    // Wishlist CRUD
    Route::get('/wishlists', [WishlistController::class, 'index'])->name('wishlists.index');
    Route::get('/wishlists/create', [WishlistController::class, 'create'])->name('wishlists.create');
    Route::post('/wishlists', [WishlistController::class, 'store'])->name('wishlists.store');
    Route::get('/wishlists/{wishlist}', [WishlistController::class, 'show'])->name('wishlists.show');
    Route::get('/wishlists/{wishlist}/edit', [WishlistController::class, 'edit'])->name('wishlists.edit');
    Route::put('/wishlists/{wishlist}', [WishlistController::class, 'update'])->name('wishlists.update');
    Route::delete('/wishlists/{wishlist}', [WishlistController::class, 'destroy'])->name('wishlists.destroy');
    
    // Wishlist items management
    Route::post('/wishlists/{wishlist}/items', [WishlistController::class, 'addItem'])->name('wishlists.items.store');
    Route::delete('/wishlists/{wishlist}/items/{item}', [WishlistController::class, 'removeItem'])->name('wishlists.items.destroy');
    
    // Set default wishlist
    Route::post('/wishlists/{wishlist}/set-default', [WishlistController::class, 'setAsDefault'])
        ->name('wishlists.set-default');
});

/*
|===================================================================
|> Payment Routes
|===================================================================
*/

// Public routes (available to all users)
Route::get('/payment-methods', [PaymentController::class, 'index'])
    ->name('payment.methods.index');

Route::get('/payment-methods/{paymentMethod}', [PaymentController::class, 'show'])
    ->name('payment.methods.show');

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Process payment
    Route::post('/payment/process/{paymentMethod}', [PaymentController::class, 'process'])
        ->name('payment.process');
        
    // Payment success/failure pages
    Route::get('/payment/success', [PaymentController::class, 'success'])
        ->name('payment.success');
        
    Route::get('/payment/failure', [PaymentController::class, 'failure'])
        ->name('payment.failure');
});

/*
|===================================================================
|> Order Routes
|===================================================================
*/

// Public routes (no authentication required)
Route::get('/track-order', [OrderController::class, 'showTrackForm'])
    ->name('orders.track.form');
    
Route::post('/track-order', [OrderController::class, 'track'])
    ->name('orders.track');

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Order listing and details
    Route::get('/orders', [OrderController::class, 'index'])
        ->name('orders.index');
        
    Route::get('/orders/{order}', [OrderController::class, 'show'])
        ->name('orders.show');
    
    // Order actions
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])
        ->name('orders.cancel');
        
    Route::post('/orders/{order}/return', [OrderController::class, 'returnOrder'])
        ->name('orders.return');
        
    // Order items
    Route::post('/orders/{order}/items/{item}/return', [OrderController::class, 'returnItem'])
        ->name('orders.items.return');
        
    // Order downloads (if applicable)
    Route::get('/orders/{order}/download/{item}', [OrderController::class, 'download'])
        ->name('orders.download');
});
