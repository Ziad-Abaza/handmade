<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\VendorController;
use App\Http\Controllers\Api\RegionController;
use App\Http\Controllers\Api\FollowController;
use App\Http\Controllers\Api\PromotionController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Page\HomeController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\ProductDetailController;
use App\Http\Controllers\Api\AdvertisementController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\PaymentStatusController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

/*
|===================================================================
|> Authentication Routes
|===================================================================
*/
// Auth Routes
Route::post('/register', [UserController::class, 'register'])->name('register');
Route::post('/login', [UserController::class, 'login'])->name('login');
Route::post('/auth/google', [UserController::class, 'googleLogin'])->name('auth.google');

/*
|===================================================================
|> User Management Routes (Protected)
|===================================================================
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/user', [UserController::class, 'currentUser'])->name('user.current');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('user.show');
    Route::post('/users/update', [UserController::class, 'update'])->name('user.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('user.destroy');
    Route::post('/logout', [UserController::class, 'logout'])->name('logout');
});
Route::get('/verify/{token}', [UserController::class, 'verifyAccount'])->name('verify.account');

/*
|===================================================================
|> Vendor Management Routes (Protected)
|===================================================================
*/
Route::prefix('vendor')->as('vendor.')->group(function () {
    Route::get('/', [VendorController::class, 'publicIndex'])->name('public.index');
    Route::get('/{vendor}', [VendorController::class, 'show'])->name('show');
        Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/trashed', [VendorController::class, 'trashedVendors'])->name('trashed-vendors');
        Route::post('/', [VendorController::class, 'store'])->name('store');
        Route::post('/{vendor}', [VendorController::class, 'update'])->name('update');
        Route::delete('/{vendor}', [VendorController::class, 'destroy'])->name('destroy');

        Route::post('/{vendor}/approve', [VendorController::class, 'approve'])->name('approve');
        Route::post('/{vendor}/reject', [VendorController::class, 'reject'])->name('reject');

        Route::post('/{id}/restore', [VendorController::class, 'restore'])->name('restore');
    });
});
Route::get('/dashboard/vendor', [VendorController::class, 'index'])->name('vendor.index')->middleware('auth:sanctum');

/*
|===================================================================
|> Region Management Routes (Protected)
|===================================================================
*/
Route::prefix('region')->as('region.')->group(function () {
    Route::get('/', [RegionController::class, 'index'])->name('index');
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/', [RegionController::class, 'store'])->name('store');
        Route::post('/{region}', [RegionController::class, 'update'])->name('update');
        Route::delete('/{region}', [RegionController::class, 'destroy'])->name('destroy');
    });
});

/*
|===================================================================
|> Follow Management Routes (Protected)
|===================================================================
*/
Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('follow')->as('follow.')->group(function () {
        Route::post('/{vendor}', [FollowController::class, 'toggleFollow'])->name('toggle');
        Route::get('/vendors', [FollowController::class, 'getFollowedVendors'])->name('vendors');
        Route::get('/activities', [FollowController::class, 'getLatestActivities'])->name('activities');
    });
});

/*
|===================================================================
|> Promotion Management Routes (Protected)
|===================================================================
*/
Route::prefix('promotion')->as('promotion.')->group(function () {
    Route::get('/', [PromotionController::class, 'index'])->name('index');
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/active', [PromotionController::class, 'vendorSubscriptions'])->name('active');
        Route::post('/', [PromotionController::class, 'store'])->name('store');
        Route::post('/{promotion}', [PromotionController::class, 'update'])->name('update');
        Route::delete('/{promotion}', [PromotionController::class, 'destroy'])->name('destroy');
        Route::post('/vendors/{vendor}/promotions/{promotion}/subscribe', [PromotionController::class, 'subscribe'])->name('vendors.promotions.subscribe');
        Route::put('/vendors/{vendor}/promotions/{promotion}/approve', [PromotionController::class, 'approveSubscription'])->name('vendors.promotions.approve');
        Route::put('/vendors/{vendor}/promotions/{promotion}/reject', [PromotionController::class, 'rejectSubscription'])->name('vendors.promotions.reject');
    });
});
/*
|===================================================================
|> Category Management Routes (Protected)
|===================================================================
*/
Route::prefix('category')->name('category.')->group(function () {
    Route::get('/', [CategoryController::class, 'index'])->name('index');
    Route::get('/{category}/products', [CategoryController::class, 'getProductsByCategory']);
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/', [CategoryController::class, 'store'])->name('store');
        Route::post('/{category}', [CategoryController::class, 'update'])->name('update');
        Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
    });
});

/*
|===================================================================
|> Category Management Routes (Protected)
|===================================================================
*/
Route::prefix('product')->name('product.')->group(function () {
    Route::get('/', [ProductController::class, 'index'])->name('index');
    Route::get('/{product}', [ProductController::class, 'show'])->name('show');
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::post('/{product}', [ProductController::class, 'update'])->name('update');
        Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
    });
});

/*
|===================================================================
|> Product Details Management Routes (Protected)
|===================================================================
*/
Route::prefix('product/{product}/details')->name('product.details.')->group(function () {
    Route::get('/', [ProductDetailController::class, 'index'])->name('index');
    Route::get('/{detail}', [ProductDetailController::class, 'show'])->name('show');
        Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/', [ProductDetailController::class, 'store'])->name('store');
        Route::post('/{detail}', [ProductDetailController::class, 'update'])->name('update');
        Route::delete('/{detail}', [ProductDetailController::class, 'destroy'])->name('destroy');
    });
});

/*
|===================================================================
|> Payment Status Management Routes
|===================================================================
*/
Route::apiResource('payment-statuses', PaymentStatusController::class)->middleware('auth:sanctum');

/*
|===================================================================
|> Payment Method Management Routes
|===================================================================
*/
Route::prefix('payment-methods')->as('payment-methods.')->middleware('auth:sanctum')->group(function () {
    // List all payment methods (with filters)
    Route::get('/', [\App\Http\Controllers\API\PaymentMethodController::class, 'index'])->name('index');
    
    // Get active payment methods (public endpoint)
    Route::get('/active', [\App\Http\Controllers\API\PaymentMethodController::class, 'activeMethods'])
        ->name('active')
        ->withoutMiddleware('auth:sanctum');
    
    // Create a new payment method (admin only)
    Route::post('/', [\App\Http\Controllers\API\PaymentMethodController::class, 'store'])->name('store');
    
    // Payment method-specific routes
    Route::prefix('{method}')->group(function () {
        // Get payment method details
        Route::get('/', [\App\Http\Controllers\API\PaymentMethodController::class, 'show'])->name('show');
        
        // Update payment method
        Route::post('/', [\App\Http\Controllers\API\PaymentMethodController::class, 'update'])->name('update');
        
        // Delete payment method
        Route::delete('/', [\App\Http\Controllers\API\PaymentMethodController::class, 'destroy'])->name('destroy');
        
        // Toggle active status
        Route::post('/toggle-status', [\App\Http\Controllers\API\PaymentMethodController::class, 'toggleStatus'])->name('toggle-status');
    });
});

/*
|===================================================================
|> Order Status Management Routes
|===================================================================
*/
Route::prefix('order-statuses')->as('order-statuses.')->middleware('auth:sanctum')->group(function () {
    // List all order statuses
    Route::get('/', [\App\Http\Controllers\API\OrderStatusController::class, 'index'])->name('index');
    
    // Create a new order status (admin only)
    Route::post('/', [\App\Http\Controllers\API\OrderStatusController::class, 'store'])->name('store');
    
    // Order status-specific routes
    Route::prefix('{status}')->group(function () {
        // Get order status details
        Route::get('/', [\App\Http\Controllers\API\OrderStatusController::class, 'show'])->name('show');
        
        // Update order status
        Route::post('/', [\App\Http\Controllers\API\OrderStatusController::class, 'update'])->name('update');
        
        // Delete order status
        Route::delete('/', [\App\Http\Controllers\API\OrderStatusController::class, 'destroy'])->name('destroy');
        
        // Set as default status
        Route::post('/set-default', [\App\Http\Controllers\API\OrderStatusController::class, 'setDefault'])->name('set-default');
    });
});

/*
|===================================================================
|> Order Management Routes
|===================================================================
*/
Route::prefix('orders')->as('orders.')->middleware('auth:sanctum')->group(function () {
    // List all orders (with filters)
    Route::get('/', [\App\Http\Controllers\API\OrderController::class, 'index'])->name('index');
    
    // Create a new order
    Route::post('/', [\App\Http\Controllers\API\OrderController::class, 'store'])->name('store');
    
    // Get order statuses
    Route::get('/statuses', [\App\Http\Controllers\API\OrderController::class, 'statuses'])->name('statuses');
    
    // Order-specific routes
    Route::prefix('{order}')->group(function () {
        // Get order details
        Route::get('/', [\App\Http\Controllers\API\OrderController::class, 'show'])->name('show');
        
        // Update order
        Route::post('/', [\App\Http\Controllers\API\OrderController::class, 'update'])->name('update');
        
        // Cancel order
        Route::delete('/', [\App\Http\Controllers\API\OrderController::class, 'destroy'])->name('destroy');
        
        
        // Order history
        Route::prefix('history')->as('history.')->group(function () {
            // Get order history
            Route::get('/', [\App\Http\Controllers\API\OrderHistoryController::class, 'index'])->name('index');
            
            // Get latest status
            Route::get('/latest', [\App\Http\Controllers\API\OrderHistoryController::class, 'latest'])->name('latest');
            
            // Add a new history entry (e.g., status change)
            Route::post('/', [\App\Http\Controllers\API\OrderHistoryController::class, 'store'])->name('store');
            
            // Specific history entry
            Route::get('/{history}', [\App\Http\Controllers\API\OrderHistoryController::class, 'show'])
                ->name('show')
                ->scopeBindings();
        });
    });
});

/*
|===================================================================
|> Cart Management Routes
|===================================================================
*/
Route::prefix('cart')->as('cart.')->group(function () {
    // Public routes (no auth required for guest carts)
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::get('/count', [CartController::class, 'count'])->name('count');
    
    // Protected routes (require authentication)
    Route::middleware('auth:sanctum')->group(function () {
        // Cart items
        Route::post('/', [CartController::class, 'store'])->name('store');
        Route::post('/items/{item}', [CartController::class, 'updateItem'])->name('items.update');
        Route::delete('/items/{item}', [CartController::class, 'removeItem'])->name('items.destroy');
        
        // Coupons
        Route::post('/coupons', [CartController::class, 'applyCoupon'])->name('coupons.apply');
        Route::delete('/coupons', [CartController::class, 'removeCoupon'])->name('coupons.remove');
        
        // Cart operations
        Route::delete('/clear', [CartController::class, 'clear'])->name('clear');
    });
});

/*
|===================================================================
|> Wishlist Management Routes (Protected)
|===================================================================
*/
Route::prefix('wishlists')->as('wishlists.')->middleware('auth:sanctum')->group(function () {
    // Wishlist CRUD
    Route::get('/', [WishlistController::class, 'index'])->name('index');
    Route::post('/', [WishlistController::class, 'store'])->name('store');
    Route::get('/default', [WishlistController::class, 'getDefaultWishlist'])->name('default');
    Route::get('/{wishlist}', [WishlistController::class, 'show'])->name('show');
    Route::post('/{wishlist}', [WishlistController::class, 'update'])->name('update');
    Route::delete('/{wishlist}', [WishlistController::class, 'destroy'])->name('destroy');

    // Wishlist items
    Route::post('/{wishlist}/items', [WishlistController::class, 'addItem'])->name('items.store');
    Route::post('/{wishlist}/items/{item}', [WishlistController::class, 'updateItem'])->name('items.update');
    Route::delete('/{wishlist}/items/{item}', [WishlistController::class, 'removeItem'])->name('items.destroy');
    Route::post('/{wishlist}/items/{item}/move', [WishlistController::class, 'moveItem'])->name('items.move');

    // Product check
    Route::get('/check-product/{product}', [WishlistController::class, 'checkProductInWishlists'])->name('check-product');
});


/*
|===================================================================
|> Favorite Management Routes (Protected)
|===================================================================
*/
Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('favorite')->name('favorite.')->group(function () {
        Route::post('/toggle/{product}', [FavoriteController::class, 'toggle'])->name('toggle');
        Route::delete('/remove/{product}', [FavoriteController::class, 'remove'])->name('remove');
        Route::get('/', [FavoriteController::class, 'index'])->name('index');
        Route::get('/check/{product}', [FavoriteController::class, 'check'])->name('check');
    });
});

/*
|===================================================================
|> Reviews Management Routes (Protected)
|===================================================================
*/
Route::prefix('product/{product}/reviews')->name('product.reviews.')->group(function () {
    Route::get('/', [ReviewController::class, 'index'])->name('index');
        Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/', [ReviewController::class, 'store'])->name('store');
        Route::post('/{review}', [ReviewController::class, 'update'])->name('update');
        Route::delete('/{review}', [ReviewController::class, 'destroy'])->name('destroy');
    });
});

/*
|===================================================================
|> Advertisement Management Routes (Protected)
|===================================================================
*/
Route::prefix('advertisement')->as('advertisement.')->group(function () {
    Route::get('/', [AdvertisementController::class, 'index'])->name('index');
    Route::get('/{advertisement}', [AdvertisementController::class, 'show'])->name('show');

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/', [AdvertisementController::class, 'store'])->name('store');
        Route::post('/{advertisement}', [AdvertisementController::class, 'update'])->name('update');
        Route::delete('/{advertisement}', [AdvertisementController::class, 'destroy'])->name('destroy');
    });
});

/*
|===================================================================
|> Notification Management Routes (Protected)
|===================================================================
*/
Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('markAllAsRead');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('markAsRead');
    });
});

/*
|===================================================================
|> Static Pages & Support Routes
|===================================================================
*/
Route::get('/static-pages/{slug}', [\App\Http\Controllers\Api\StaticPageController::class, 'getPage']);
Route::get('/static-pages/terms-conditions', [\App\Http\Controllers\Api\StaticPageController::class, 'getPage'])->where('slug', 'terms-conditions');
Route::get('/static-pages/privacy-policy', [\App\Http\Controllers\Api\StaticPageController::class, 'getPage'])->where('slug', 'privacy-policy');
Route::get('/faqs', [\App\Http\Controllers\Api\StaticPageController::class, 'getFaqs']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/support/tickets', [\App\Http\Controllers\Api\SupportController::class, 'store']);
});

require __DIR__ . '/server.php';
require __DIR__ . '/app.php';
