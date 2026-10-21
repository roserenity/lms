<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

// Route::get('/health', fn() => response('OK', 200));

Route::get('/', function () {
    // return abort(403);
    return response()->json(["status" => "healthy"], 200);
});

Route::get('/php', function () {
    return phpinfo();
});

Route::get('/sql-mode', function () {
    return DB::select("SHOW VARIABLES LIKE 'sql_mode'");
});

