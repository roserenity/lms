<?php

namespace App\Exceptions;

use App\Jobs\Tenants\SendErrorNotificationMail;
use App\Mail\Central\ErrorNotification;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Mail;


class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        \League\OAuth2\Server\Exception\OAuthServerException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Flag to prevent recursive email sending errors.
     *
     * @var bool
     */
    protected $isSendingError = false;

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        /* $this->reportable(function (Throwable $e) {
            $this->sendErrorNotification($e);
        }); */
    }

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function report(Throwable $exception)
    {
        // if ($this->shouldReport($exception)) {
        //     $this->sendErrorNotification($exception);
        // }

        parent::report($exception);
    }

    /**
     * Send the error notification via email.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    protected function sendErrorNotification(Throwable $exception)
    {
        if ($this->isSendingError) {
            return;
        }

        // try {
        //     $this->isSendingError = true;

        //     $receiver = config('mail.error_mail_receiver');

        //     $errorDetails = [
        //         'message' => $exception->getMessage(),
        //         'file' => $exception->getFile(),
        //         'line' => $exception->getLine(),
        //         'url' => Request::fullUrl(),
        //         'user_id' => auth()->guard('api')->user() ? auth()->guard('api')->user()->id : null,
        //         'request_data' => Request::all(),
        //         'email' => auth()->guard('api')->user() ? auth()->guard('api')->user()->null : null,
        //         'receiver' => $receiver
        //     ];

        //     SendErrorNotificationMail::dispatch($errorDetails);
            
        // } catch (\Swift_TransportException $e) {
        //     Log::error('SMTP Authentication Error: ' . $e->getMessage());

        // } catch (Throwable $e) {
        //     Log::error('Error in sending error notification email: ' . $e->getMessage());
        // } finally {
        //     $this->isSendingError = false;
        // }
    }

}
