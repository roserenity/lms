<?php

declare(strict_types=1);

/**
 * Standalone version of routes/tenant.php
 *
 * Identical to the original except the three tenancy middlewares have been removed:
 *   - InitializeTenancyByDomain
 *   - PreventAccessFromCentralDomains
 *   - TenantActivated
 *
 * All routes are open to the single configured domain.
 */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Tenants\AuthController;
use App\Http\Controllers\Api\V1\Tenants\NotificationController;
use App\Http\Controllers\Api\V1\Central\TenantController;

Route::controller(AuthController::class)->group(function () {
    Route::post('register', 'register');

    Route::post('login', 'login')->middleware(['throttle.login:3,1']);
    Route::post('logout', 'logout');

    Route::post('email/verification-notification', 'sendVerificationEmail');
    Route::get('email/verify', 'verifyEmail')->name('verification.verify');

    Route::post('forgot-password', 'sendResetPasswordForm');
    Route::get('/reset-password/{token}', 'showResetPasswordForm')->name('password.reset');
    Route::post('/reset-password', 'resetPassword');

    Route::put('/update/password', 'updatePassword');
    Route::put('/update/accepted-terms', 'updateAcceptedTerms');
    Route::put('/update/details', 'updateDetails');
    Route::post('/update/profile/photo', 'updateProfilePhoto');
    Route::put('/update/info-update-flag', 'updateNeedsInfoFlag');

    Route::get('user', 'user');
    Route::get('/login/history', 'loginHistory');
    Route::get('/activity-log', 'activityLogs');
});

Route::get('/user/notifications', [NotificationController::class, 'allNotifications']);
Route::post('/user/mark/notification', [NotificationController::class, 'markNotification']);
Route::get('/user/mark/all/notifications', [NotificationController::class, 'markAllNotifications']);

Route::get('/tenants/info', [TenantController::class, 'showTenantInfo']);
Route::get('/tenants/custom-registration/pages', [TenantController::class, 'showCustomRegistrationPage']);
Route::get('/tenants/class-categories', [TenantController::class, 'showClassCategories']);
Route::get('/tenants/random/class-categories', [TenantController::class, 'showRandomClassCategories']);
Route::get('/tenants/browse-courses', [TenantController::class, 'showGroupedCourses']);
Route::get('/tenants/course-availability-dates', [TenantController::class, 'showAvailbilityDates']);
Route::get('/tenants/training-credits', [TenantController::class, 'showTrainingCredits']);
Route::get('/tenants/backgrounds', [TenantController::class, 'showBackgrounds']);

Route::get('/tenants/courses', [TenantController::class, 'courses']);
Route::post('/tenants/courses/all', [TenantController::class, 'allCourses']);
Route::get('/tenants/courses/free', [TenantController::class, 'freeCourses']);
Route::get('/tenants/courses/{course_uuid}', [TenantController::class, 'showCourse']);

Route::get('/tenants/advisories', [TenantController::class, 'showAdvisories']);

Route::get('/tenants/view/certificate/{certificate_uuid}', [TenantController::class, 'showCertificate']);

// PAYMENT GATEWAY
// Route::get('/tenants/payment-status/check', [TenantController::class, 'paymentStatusCheck']);
// Route::post('/tenants/request/official-receipt', [TenantController::class, 'requestGatewayOfficialReceipt']);
// Route::post('/tenants/checkout/callback', [TenantController::class, 'callback']);

Route::post('/tenants/send/contact/mail', [TenantController::class, 'sendContactMail']);

// SCORM CLOUD
Route::post('/tenants/course/upload/callback', [TenantController::class, 'scormCloudCourseUploadCallback']);
Route::post('/tenants/course/registration/postback', [TenantController::class, 'scormCloudCourseRegistrationPostBack']);

Route::get('/', function () {
    return view('welcome');
});
