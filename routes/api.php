<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


/** Buyers **/

Route::resource(
    'buyers',
    'Buyer\BuyerController',
    ['only' => ['index', 'show']]
);

/** Buyer for Transactions **/

Route::resource(
    'buyers.transactions',
    'Buyer\BuyerTransactionController',
    ['only' => ['index']]
);

/** Buyer for Products **/

Route::resource(
    'buyers.products',
    'Buyer\BuyerProductController',
    ['only' => ['index']]
);

/** Buyer for Sellers **/

Route::resource(
    'buyers.sellers',
    'Buyer\BuyerSellerController',
    ['only' => ['index']]
);

/** Buyer for Categories **/

Route::resource(
    'buyers.categories',
    'Buyer\BuyerCategoryController',
    ['only' => ['index']]
);

/** Sellers **/

Route::resource(
    'sellers',
    'Seller\SellerController',
    ['only' => ['index', 'show']]
);

/** Seller for Transactions **/

Route::resource(
    'sellers.transactions',
    'Seller\SellerTransactionController',
    ['only' => ['index']]
);

/** Seller for Categories **/

Route::resource(
    'sellers.categories',
    'Seller\SellerCategoryController',
    ['only' => ['index']]
);

/** Seller for Buyers **/

Route::resource(
    'sellers.buyers',
    'Seller\SellerBuyerController',
    ['only' => ['index']]
);

/** Seller for Products **/

Route::resource(
    'sellers.products',
    'Seller\SellerProductController',
    ['except' => ['create', 'edit']]
);

/** Categories **/

Route::resource(
    'categories',
    'Category\CategoryController',
    ['except' => ['create', 'edit']]
);

/** Category for Products**/

Route::resource(
    'categories.products',
    'Category\CategoryProductController',
    ['only' => ['index']]
);

/** Category for Sellers**/

Route::resource(
    'categories.sellers',
    'Category\CategorySellerController',
    ['only' => ['index']]
);

/** Category for Transactions**/

Route::resource(
    'categories.transactions',
    'Category\CategoryTransactionController',
    ['only' => ['index']]
);

/** Category for Buyers **/

Route::resource(
    'categories.buyers',
    'Category\CategoryBuyerController',
    ['only' => ['index']]
);

/** Products **/

Route::resource(
    'products',
    'Product\ProductController',
    ['only' => ['index', 'show']]
);

/** Products for Transactions **/

Route::resource(
    'products.transactions',
    'Product\ProductTransactionController',
    ['only' => ['index']]
);

/** Products for Buyers **/

Route::resource(
    'products.buyers',
    'Product\ProductBuyerController',
    ['only' => ['index']]
);

/** Products for Categories **/

Route::resource(
    'products.categories',
    'Product\ProductCategoryController',
    ['only' => ['index', 'update', 'destroy']]
);

/** Products for Buyers and Transactions **/

Route::resource(
    'products.buyers.transactions',
    'Product\ProductBuyerTransactionController',
    ['only' => ['store']]
);

/** Transactions **/

Route::resource(
    'transactions',
    'Transaction\TransactionController',
    ['only' => ['index', 'show']]
);

/** Transactions for Category **/

Route::resource(
    'transactions.categories',
    'Transaction\TransactionCategoryController',
    ['only' => ['index']]
);

/** Transactions for Seller **/

Route::resource(
    'transactions.sellers',
    'Transaction\TransactionSellerController',
    ['only' => ['index']]
);

/** Email verification **/
Route::name('verify')->get('users/verify/{token}', 'User\UserController@verify');

/** Resend verification email**/
Route::name('resend')->get('users/{user}/resend', 'User\UserController@resend');

/** Oauth Token **/
Route::post(
    'outh/token',
    '\Laravel\Passport\Http\Controllers\AccessTokenController@issueToken'
);

/** Identifying an authenticated user **/
Route::name('me')->get('users/me', 'User\UserController@me');


/** Users **/

Route::resource(
    'users',
    'User\UserController',
    ['except' => ['create', 'edit']]
);
