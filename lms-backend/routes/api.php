<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;

Route::post('/csp-report-to', function (Request $request) {
    $reportData = $request->all();
    $receiver = config('mail.error_mail_receiver');

    Mail::raw(json_encode($reportData, JSON_PRETTY_PRINT), function ($message) use ($receiver){
        $message->to($receiver)
                ->subject('CSP Violation Report');
    });

    return response()->json(['status' => 'sent via Laravel Mail']);
});
