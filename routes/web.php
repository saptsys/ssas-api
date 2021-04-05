<?php

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use App\Models\Firms;
use Symfony\Component\Yaml\Yaml;
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

Route::get('/', function () {
    $yamlContents = Yaml::parse(file_get_contents(public_path('/update/latest.yml')));
    return view('home',["softwareInfo"=>$yamlContents]);
});
