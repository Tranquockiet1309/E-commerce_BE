<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProductStoreController;
use App\Http\Controllers\ProductSaleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\AttributeController;
use App\Http\Controllers\ProductAttributeController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\MomoController;


// ----------------------------
// API RESOURCES
// ----------------------------
Route::apiResources([
    'product_attribute' => ProductAttributeController::class,
    'banner' => BannerController::class,
    'category' => CategoryController::class,
    'contact' => ContactController::class,
    'menu' => MenuController::class,
    'product' => ProductController::class,
    'productstore' => ProductStoreController::class,
    'productsale' => ProductSaleController::class,
    'topic' => TopicController::class,
    'post' => PostController::class,
    'user' => UserController::class,
    'order' => OrderController::class,
    'setting' => SettingController::class,
    'attribute' => AttributeController::class,
]);

// ----------------------------
// AUTH
// ----------------------------
// Login (trả về token/session)
Route::post('/login', [UserController::class, 'login'])->name('login');
Route::get('/sanctum/csrf-cookie', function () {
    return response()->json(['csrf_token' => csrf_token()]);
});



// // Lấy thông tin user hiện tại (chỉ khi đã login)
// Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
//     return response()->json([
//         'success' => true,
//         'user' => $request->user()
//     ]);
// });
//cart
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/add', [CartController::class, 'add']);
    Route::delete('/cart/remove/{id}', [CartController::class, 'remove']);
    Route::delete('/cart/clear', [CartController::class, 'clear']);
    Route::put('/cart/update/{itemId}', [CartController::class, 'updateQuantity']);
    Route::post('/cart/merge', [CartController::class, 'merge']);
    Route::get('/cart/count', [CartController::class, 'count']);
    Route::get('/my-orders', [OrderController::class, 'myOrders']);

});

Route::put('/orders/{id}/cancel', [OrderController::class, 'cancel']);
// Logout
Route::middleware('auth:sanctum')->post('/logout', [UserController::class, 'logout']);

// ----------------------------
// PRODUCTS
// ----------------------------
Route::get('product-new/{limit?}', [ProductController::class, 'product_new']);
Route::get('product-sale', [ProductController::class, 'product_sale']);
Route::get('product-category/{id}', [ProductController::class, 'product_by_category']);
Route::get('product-all', [ProductController::class, 'product_all']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::post('/product/{id}', [ProductController::class, 'update']);

// ----------------------------
// CATEGORIES
// ----------------------------
Route::get('category-new/{limit?}', [CategoryController::class, 'category_new']);
Route::get('/categories', [CategoryController::class, 'indexClient']);
Route::put('/categories/{id}', [CategoryController::class, 'update']);
Route::get('/category/{id}', [CategoryController::class, 'show']);

// ----------------------------
// BANNER
// ----------------------------
Route::get('/banner-client', [BannerController::class, 'indexClient']);

// ----------------------------
// POSTS
// ----------------------------
Route::get('/posts', [PostController::class, 'indexClient']);
Route::get('/posts/{id}', [PostController::class, 'show']);

// ----------------------------
// PRODUCTSTORE IMPORT
// ----------------------------
Route::post('/productstore/import', [ProductStoreController::class, 'import']);

//menu
Route::get('/menuClient', [MenuController::class, 'menuClient']);

// ----------------------------
// PRODUCTSALE
// ----------------------------

Route::get('/indexClient', [ProductSaleController::class, 'indexClient']);
Route::get('/productsale/{id}', [ProductSaleController::class, 'show']);

// ----------------------------
// SETTINGS
// ----------------------------
Route::get('/settingClient', [SettingController::class, 'indexClient']);

// ----------------------------
// ATTRIBUTE FILTERS
// ----------------------------
Route::get('/products_filters', [ProductController::class, 'getFilters']);

// ----------------------------
// API MOMO
Route::post('/payment/momo', [MomoController::class, 'createPayment']);
Route::post('/payment/momo/ipn', [MomoController::class, 'ipn']);

// -------------user---------------
Route::post('/user/{id}/update-client', [UserController::class, 'updateClient']);
Route::post('/register', [UserController::class, 'register']);
Route::post('/user/{id}/change-password', [UserController::class, 'changePassword']);
