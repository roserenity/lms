<?php

declare(strict_types=1);

/**
 * Standalone version of routes/tenants/admin.php
 *
 * Identical to the original except the three tenancy middlewares have been removed:
 *   - InitializeTenancyByDomain
 *   - PreventAccessFromCentralDomains
 *   - TenantActivated
 *
 * All routes still require auth:api (unchanged).
 */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Tenants\Admin\ClassCategoryController;
use App\Http\Controllers\Api\V1\Tenants\Admin\UserManagementController;
use App\Http\Controllers\Api\V1\Tenants\Admin\CustomTableController;
use App\Http\Controllers\Api\V1\Tenants\Admin\CustomRegistrationController;
use App\Http\Controllers\Api\V1\Tenants\Admin\EnrollmentController;
use App\Http\Controllers\Api\V1\Tenants\Admin\DashboardController;
use App\Http\Controllers\Api\V1\Tenants\Admin\TicketsController;
use App\Http\Controllers\Api\V1\Tenants\Admin\ActivityLogController;
use App\Http\Controllers\Api\V1\Tenants\Admin\AdvisoryController;
use App\Http\Controllers\Api\V1\Tenants\Admin\CertificateTemplateCategoryController;
use App\Http\Controllers\Api\V1\Tenants\Admin\DocumentController;
use App\Http\Controllers\Api\V1\Tenants\Admin\CustomModuleSettingController;
use App\Http\Controllers\Api\V1\Tenants\Admin\ForumPostCommentController;
use App\Http\Controllers\Api\V1\Tenants\Admin\ForumPostController;
use App\Http\Controllers\Api\V1\Tenants\Admin\AnnouncementController;
use App\Http\Controllers\Api\V1\Tenants\Admin\AnnouncementCommentController;
use App\Http\Controllers\Api\V1\Tenants\Admin\BannedWordController;
use App\Http\Controllers\Api\V1\Tenants\Admin\ExportController;
use App\Http\Controllers\Api\V1\Tenants\Admin\GroupChatController;
use App\Http\Controllers\Api\V1\Tenants\Admin\LandingPageController;
use App\Http\Controllers\Api\V1\Tenants\Admin\PrivateChatController;
use App\Http\Controllers\Api\V1\Tenants\Admin\TrainingCreditController;

Route::middleware(['auth:api'])->group(function () {
    Route::post('/enrollment/import/users', [EnrollmentController::class, 'importEnrollees']);
    Route::get('/enrollments/user/{user_id}', [EnrollmentController::class, 'showEnrolledCourses']);
    Route::post('/enrollments/user/{user_id}', [EnrollmentController::class, 'enrollCourses']);
    Route::post('/enrollments/user/{user_id}/enroll', [EnrollmentController::class, 'enrollCourse']);
    Route::delete('/enrollments/{enrollment_id}', [EnrollmentController::class, 'deleteEnrolledCourse']);

    Route::get('/enrollment/courses', [EnrollmentController::class, 'showCourses']);
    Route::post('/enrollment/courses/export', [EnrollmentController::class, 'exportCourseInvoices']);
    Route::get('/enrollment/course/{course_uuid}/enrollees', [EnrollmentController::class, 'showCourseEnrollees']);
    Route::put('/enrollment/invoice/status', [EnrollmentController::class, 'updateInvoiceStatus']);
    Route::put('/enrollment/invoice/type_of_payment', [EnrollmentController::class, 'updateTypeOfPayment']);
    Route::put('/enrollment/invoice/type_of_participant', [EnrollmentController::class, 'updateTypeOfParticipant']);
    Route::post('/enrollment/course/{course_uuid}/invoice/export', [EnrollmentController::class, 'exportInvoice']);
    Route::post('/enrollment/upload/proof-of-payment', [EnrollmentController::class, 'uploadProofOfPayment']);
    Route::post('/enrollment/upload/commitment-letter', [EnrollmentController::class, 'uploadCommitmentLetter']);
    Route::post('/enrollment/upload/prime-requirements', [EnrollmentController::class, 'uploadPrimeRequirements']);

    // Route::post('/payment-status/check', [EnrollmentController::class, 'paymentStatusCheck']);

    Route::post('/user-management/import/users', [UserManagementController::class, 'importUsers']);
    Route::post('/user-management/import/logs', [UserManagementController::class, 'importLogs']);
    Route::delete('/user-management/import/logs', [UserManagementController::class, 'deleteAllLogs']);
    Route::delete('/user-management/import/logs/{id}', [UserManagementController::class, 'deleteLogs']);
    Route::put('/user-management/update/status', [UserManagementController::class, 'updateUserStatus']);
    Route::put('/user-management/update/role', [UserManagementController::class, 'updateUserRole']);
    Route::put('/user-management/prompt-update/all', [UserManagementController::class, 'updatePromptUpdateAll']);
    Route::put('/user-management/prompt-update/{id}', [UserManagementController::class, 'updatePromptUpdateUser']);
    Route::post('/user-management/export', [UserManagementController::class, 'exportUsers']);
    Route::resource('/user-management', UserManagementController::class);

    Route::resource('/class-categories', ClassCategoryController::class);

    Route::resource('/exports', ExportController::class)->only(['index', 'destroy'])->names([
        'index' => 'admin_exports.index',
        'destroy' => 'admin_exports.destroy',
    ]);
    Route::post('/download/exports', [ExportController::class, 'downloadExportFile']);

    Route::resource('/certificate-template-category', CertificateTemplateCategoryController::class);
    Route::resource('/training-credits', TrainingCreditController::class);

    Route::get('/module-settings/fields', [CustomModuleSettingController::class, 'getCustomModuleFields']);
    Route::post('/module-settings/fields', [CustomModuleSettingController::class, 'addCustomModuleField']);
    Route::post('/module-settings/fields/{id}', [CustomModuleSettingController::class, 'updateCustomModuleField']);
    Route::delete('/module-settings/fields/{id}', [CustomModuleSettingController::class, 'deleteCustomModuleField']);

    Route::get('/custom-tables', [CustomTableController::class, 'index']);
    Route::get('/custom-table/columns/{table}', [CustomTableController::class, 'show']);
    Route::post('/custom-table/change-column/position', [CustomTableController::class, 'changeColumnPosition']);
    Route::post('/custom-table/create-table', [CustomTableController::class, 'newTable']);
    Route::post('/custom-table/drop-table', [CustomTableController::class, 'deleteTable']);
    Route::post('/custom-table/sort-table', [CustomTableController::class, 'sortTable']);
    Route::post('/custom-table/add-columns', [CustomTableController::class, 'addColumns']);
    Route::post('/custom-table/drop-column', [CustomTableController::class, 'deleteColumn']);
    Route::post('/custom-table/update-column', [CustomTableController::class, 'updateColumn']);

    Route::controller(CustomRegistrationController::class)->group(function () {
        Route::get('/custom-registration/pages', 'index');
        Route::post('/custom-registration/pages', 'createRegistrationPage');
        Route::put('/custom-registration/pages/{custom_registration_page_id}', 'updateRegistrationPage');
        Route::delete('/custom-registration/pages/{custom_registration_page_id}', 'deleteRegistrationPage');
        Route::post('/sort/custom-registration/pages', 'sortRegistrationPage');

        Route::post('/custom-registration/fields', 'createRegistrationField');
        Route::put('/custom-registration/fields/{custom_registration_field_id}', 'updateRegistrationField');
        Route::delete('/custom-registration/fields/{custom_registration_field_id}', 'deleteRegistrationField');
        Route::post('/sort/custom-registration/fields', 'sortRegistrationField');
    });

    Route::controller(PrivateChatController::class)->group(function () {
        Route::get('/contact/list', 'showContactList');
        Route::get('/private/chat/list', 'showPrivateChatList');
        Route::get('/private/chat/{private_chat_uuid}', 'showPrivateChat');
        Route::post('/private/chat/send', 'sendPrivateMessage');
        Route::post('/private/chat/attachment/download', 'downloadAttachment');
    });

    Route::controller(GroupChatController::class)->group(function () {
        Route::post('/group/chat', 'createGroupChat');
        Route::post('/add/users/group/chat/{group_chat_uuid}', 'addUsersToGroupChat');
        Route::post('/group/chat/photo/{group_chat_uuid}', 'updateGroupPhoto');
        Route::delete('/group/chat/photo/{group_chat_uuid}/remove', 'removeGroupPhoto');
        Route::get('/group/chat/list', 'showGroupChatList');
        Route::get('/group/chat/{group_chat_uuid}', 'showGroupChat');
        Route::get('/group/chat/{group_chat_uuid}/contact/list', 'getContacts');
        Route::post('/group/chat/send', 'sendGroupMessage');
        Route::post('/group/chat/attachment/download', 'downloadAttachment');
    });

    Route::controller(DashboardController::class)->group(function () {
        Route::get('/list/cards', 'showTotalListCards');
        Route::get('/pie-graphs', 'showTotalPieGraphs');
        Route::get('/courses', 'showCourses');
        Route::get('/course/users', 'showCourseUsers');
        Route::get('/course/completers', 'showCourseCompleters');
        Route::get('/total-users-per-course', 'showTotalUsersPerCourse');
        Route::get('/custom-registration/field/lists', 'listOfCustomRegistrationFields');
        Route::get('/custom-registration/field/lists-options', 'listOfCustomRegistrationFieldsWithOptions');
        Route::post('/dashboard/courses/export', 'dashboardCoursesExport');
        Route::post('/dashboard/course/users/export', 'dashboardCourseUsersExport');
        Route::post('/dashboard/course/completers/export', 'dashboardCourseCompletersExport');
    });

    Route::controller(TicketsController::class)->group(function () {
        Route::post('/tickets', 'showTickets');
        Route::post('/ticket-count', 'showTicketCount');
        Route::post('/ticket-messages', 'getTicketMessages');
        Route::post('/ticket', 'store');
        Route::post('/ticket/status', 'updateTicketStatus');
        Route::post('/ticket/export', 'exportTickets');
    });

    Route::get('/activity-log/stats', [ActivityLogController::class, 'activityLogStats']);
    Route::post('/activity-log/export', [ActivityLogController::class, 'activityLogExport']);
    Route::resource('/activity-log', ActivityLogController::class)->only(['index'])->names([
        'index' => 'admin.activity-log.index',
    ]);

    Route::resource('/advisories', AdvisoryController::class);

    Route::get('/landing-page/backgrounds', [LandingPageController::class, 'showLandingPageBackgrounds']);
    Route::post('/landing-page/background', [LandingPageController::class, 'createLandingPageBackground']);
    Route::delete('/landing-page/background/{id}', [LandingPageController::class, 'deleteLandingPageBackground']);

    Route::resource('/documents', DocumentController::class)->only(['index', 'store', 'update']);

    Route::resource('/forum/banned-words', BannedWordController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
    Route::post('/forum/banned-words/save', [BannedWordController::class, 'updateBannedWords']);

    Route::resource('/forum/post', ForumPostController::class)->only(['index', 'show', 'store', 'update', 'destroy'])->names([
        'index' => 'admin_forum_post.index',
        'show' => 'admin_forum_post.show',
        'store' => 'admin_forum_post.store',
        'update' => 'admin_forum_post.update',
        'destroy' => 'admin_forum_post.destroy',
    ]);

    Route::resource('/forum/post/comment', ForumPostCommentController::class)->only(['store', 'update', 'destroy'])->names([
        'store' => 'admin_forum_post_comment.store',
        'update' => 'admin_forum_post_comment.update',
        'destroy' => 'admin_forum_post_comment.destroy',
    ]);

    Route::get('/announcement/{id}', [AnnouncementController::class, 'getAnnouncement']);
    Route::resource('/announcements', AnnouncementController::class)->only(['index', 'show', 'store', 'update', 'destroy'])
        ->names([
            'index' => 'admin_announcements.index',
            'show' => 'admin_announcements.show',
            'store' => 'admin_announcements.store',
            'update' => 'admin_announcements.update',
            'destroy' => 'admin_announcements.destroy',
        ]);

    Route::resource('/announcement/comments', AnnouncementCommentController::class)->names('admin_announcement.comment');
});
