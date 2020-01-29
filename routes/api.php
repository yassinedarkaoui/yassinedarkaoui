<?php

use Illuminate\Http\Request;

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

Route::post('login', 'API\UserController@login');
Route::post('register', 'API\UserController@register');

Route::get('allCategories', 'API\CategoryController@allCategories');

Route::get('allPharmacies', 'API\UserController@getAllPharmacy');

Route::get('allNews', 'API\NewsController@allNews');
Route::post('addNewsComment', 'API\NewsController@addNewsComment');

Route::post('addGuestCategoryReport', 'API\ReportController@addGuestCategoryReport');
Route::post('addGuestPharmacyReport', 'API\ReportController@addGuestPharmacyReport');
Route::post('addGuestNewsReport', 'API\ReportController@addGuestNewsReport');
Route::post('addGuestNewsComment', 'API\NewsController@addGuestNewsComment');
Route::post('recoverPassword', 'API\UserController@recoverPassword');


Route::group(['middleware' => 'auth:api'], function(){
    Route::post('allUser', 'API\UserController@allUsers');
//pharmacy
    Route::post('getCart', 'API\CartController@getCart');
    Route::post('addCart', 'API\CartController@addCart');
    Route::post('removeCart', 'API\CartController@removeCart');
    Route::post('resetCart', 'API\CartController@resetCart');
    Route::post('updateCartQuantity', 'API\CartController@updateCartQuantity');
//employee
    Route::post('addTransaction', 'API\TransactionController@addTransaction');
    Route::post('allTransaction', 'API\TransactionController@allTransactions');
    Route::post('rejectTransaction', 'API\TransactionController@rejectTransaction');
    Route::post('removeTransactionCategory', 'API\TransactionController@removeTransactionCategory');
    Route::post('updateTransactionCategory', 'API\TransactionController@updateTransactionCategory');
    Route::post('allRequest', 'API\TransactionController@getAllRequest');
    Route::post('updateCategoryQuantity', 'API\CategoryController@updateCategoryQuantity');
    Route::post('updateRequestStatus', 'API\TransactionController@updateRequestStatus');
    Route::post('saveRequestNote', 'API\TransactionController@saveRequestNote');
    Route::post('updateCurrentLocation', 'API\UserController@updateCurrentLocation');
//notification
    Route::post('sendBuzzNotification', 'API\NotificationController@sendBuzzNotification');
    Route::post('chatNotification', 'API\NotificationController@chatNotification');
    Route::post('sendEmail', 'API\NotificationController@sendEmail');
//admin
    Route::post('allEmployee', 'API\UserController@getAllEmployee');
    Route::post('newEmployee', 'API\UserController@addEmployee');
    Route::post('newPharmacy', 'API\UserController@addPharmacy');
    Route::post('adminAllPharmacy', 'API\UserController@getAllPharmacy');
    Route::post('addItem', 'API\CategoryController@addItem');
    Route::post('addNews', 'API\NewsController@addNews');
    Route::post('userBlock', 'API\UserController@userBlockOrUnblock');
    Route::post('updateUser', 'API\UserController@updateUser');
    Route::get('adminAllNews', 'API\NewsController@adminAllNews');
    Route::post('updateNewsActive', 'API\NewsController@updateNewsActive');
    Route::post('removeNews', 'API\NewsController@removeNews');
    Route::post('updateNews', 'API\NewsController@updateNews');
    Route::post('addNews', 'API\NewsController@addNews');
    Route::post('addCategory', 'API\CategoryController@addCategory');
    Route::post('updateCategory', 'API\CategoryController@updateCategory');
    Route::get('allConfigure', 'API\ConfigureController@allConfigure');
    Route::post('newConfigure', 'API\ConfigureController@addConfigure');
    Route::post('updateConfigure', 'API\ConfigureController@updateConfigure');
    Route::get('allUserCategories', 'API\CategoryController@allUserCategories');

    Route::get('getCategoryReport', 'API\ReportController@getCategoryReport');
    Route::get('getPharmacyReport', 'API\ReportController@getPharmacyReport');
    Route::get('getNewsReport', 'API\ReportController@getNewsReport');
    Route::post('addCategoryReport', 'API\ReportController@addCategoryReport');
    Route::post('addPharmacyReport', 'API\ReportController@addPharmacyReport');
    Route::post('addNewsReport', 'API\ReportController@addNewsReport');
    Route::post('addNewsComment', 'API\NewsController@addNewsComment');
    Route::post('settingUpdate', 'API\UserController@settingUpdate');
    Route::post('updateProfile', 'API\UserController@updateProfile');
    Route::get('getSoldCategory', 'API\UserController@getSoldCategory');
    Route::get('getChatBasedOrderNumber', 'API\UserController@getChatBasedOrderNumber');
});
