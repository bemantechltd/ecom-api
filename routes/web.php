<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/lp-bulk-data-crawler', 'Api\ProductsController@lazzPharmaData');



// Route::get('/check', function () {
//     return "hello";
//     return view('welcome');
// });


// Route::get('/live/timeline-status', 'Api\OrderInfoController@liveOrderTimelineStatus');