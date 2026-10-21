<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use App\Http\Middleware\TenantActivated;

use App\Http\Controllers\Api\V1\Tenants\User\AssessmentController;
use App\Http\Controllers\Api\V1\Tenants\User\CourseController;
use App\Http\Controllers\Api\V1\Tenants\User\ModuleController;
use App\Http\Controllers\Api\V1\Tenants\User\PrivateChatController;
use App\Http\Controllers\Api\V1\Tenants\User\GroupChatController;
use App\Http\Controllers\Api\V1\Tenants\User\AnnouncementCommentController;
use App\Http\Controllers\Api\V1\Tenants\User\TicketController;
use App\Http\Controllers\Api\V1\Tenants\User\CalendarController;
use App\Http\Controllers\Api\V1\Tenants\User\ZoomMeetingController;
use App\Http\Controllers\Api\V1\Tenants\User\CourseEvaluationController;
use App\Http\Controllers\Api\V1\Tenants\User\ModuleEvaluationController;
use App\Http\Controllers\Api\V1\Tenants\User\CartController;
use App\Http\Controllers\Api\V1\Tenants\User\CheckoutController;
use App\Http\Controllers\Api\V1\Tenants\User\InvoiceController;
use App\Http\Controllers\Api\V1\Tenants\User\CertificateController;
use App\Http\Controllers\Api\V1\Tenants\User\ForumPostController;
use App\Http\Controllers\Api\V1\Tenants\User\ForumPostCommentController;
use App\Http\Controllers\Api\V1\Tenants\User\StudentModuleProgressController;
use App\Http\Controllers\Api\V1\Tenants\User\ScormController;

Route::middleware([
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
    TenantActivated::class
])->group(function () {

    Route::middleware(['auth:api'])->group(function () {
        Route::post('/courses', [CourseController::class, 'myCourses']);
        Route::get('/courses/{course_uuid}', [CourseController::class, 'showCourse']);
        Route::post('/course-card/color', [CourseController::class, 'updateCourseCardColor']);
        Route::post('/course/favorite', [CourseController::class, 'updateFavoriteCourse']);
        Route::post('/download/course/learning-material', [CourseController::class, 'downloadCourseLearningMaterial']);
        Route::post('/complete-courses', [CourseController::class, 'completedCourses']);
        Route::get('/course/{id}/announcements', [CourseController::class, 'getCourseAnnouncements']);
        Route::get('/course/{id}/zoom-meetings', [CourseController::class, 'getCourseZoomMeetings']);
        Route::get('/course/{uuid}/course-evaluations', [CourseController::class, 'getMyCourseEvaluations']);
        Route::post('/course/scorm/progress/check', [CourseController::class, 'checkScormProgress']);
        Route::post('/course/{course_id}/export', [CourseController::class, 'courseExport']);

        Route::get('/course/class-categories', [CourseController::class, 'getCourseCategories']);
        Route::post('/course/setlog', [CourseController::class, 'setLog']);

        Route::get('/course/{course_id}/modules', [ModuleController::class, 'getModules']);
        Route::get('/course/module/{module_id}', [ModuleController::class, 'getModule']);
        Route::post('/course/progress/controls', [ModuleController::class, 'next']);
        Route::post('/course/save/content', [ModuleController::class, 'saveDoneContent']);
        Route::post('/course/mark-as-complete', [ModuleController::class, 'markCompleteCourse']);
        Route::post('/course/time-spent', [ModuleController::class, 'saveTimeSpent']);

        Route::get('/course/{course_uuid}/progress', [StudentModuleProgressController::class, 'getStudentCourseProgress']);

        Route::get('/assessments/{assessment_uuid}', [AssessmentController::class, 'showAssessment']);
        Route::get('/assessments/{assessment_uuid}/questions', [AssessmentController::class, 'getAssessmentQuestions']);
        Route::post('/assessments/answers', [AssessmentController::class, 'storeAssessmentAnswers']);

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

        Route::get('/announcements/{id}', [AnnouncementCommentController::class, 'showAnnouncement']);
        Route::resource('/announcement/comments', AnnouncementCommentController::class);

        Route::resource('/tickets', TicketController::class);
        Route::post('/ticket-messages', [TicketController::class, 'getTicketMessages']);
        Route::post('/ticket', [TicketController::class, 'submitTicketMessage']);

        Route::post('/calendar/events', [CalendarController::class, 'index']);

        Route::get('zoom-meetings/{id}', [ZoomMeetingController::class, 'getMeetingInfo']);

        Route::get('/course/evaluation/{course_evaluation_id}', [CourseEvaluationController::class, 'getCourseEvaluation']);
        Route::resource('/course/evaluations', CourseEvaluationController::class)->only(['store', 'show']);

        Route::get('/module/evaluation/{module_evaluation_id}', [ModuleEvaluationController::class, 'getModuleEvaluation']);
        Route::resource('/module/evaluations', ModuleEvaluationController::class)->only(['store', 'show'])
            ->names([
                'store' => 'user_module_evaluation.store',
                'show' => 'user_module_evaluation.show',
            ]);

        Route::get('/add-to-cart/items', [CartController::class, 'addToCartItems']);
        Route::post('/add-to-cart', [CartController::class, 'addItemToCart']);
        Route::post('/remove-from-cart', [CartController::class, 'removeItemFromCart']);
        Route::post('/empty-cart', [CartController::class, 'emptyCart']);

        Route::post('/checkout', [CheckoutController::class, 'checkout']);
        // Route::post('/checkout/online-portal', [CheckoutController::class, 'checkoutOnlinePortal']);
        Route::post('/checkout/nomination', [CheckoutController::class, 'checkoutNomination']);
        Route::post('/checkout/own-account', [CheckoutController::class, 'checkoutOwnAccount']);
        Route::post('/checkout/offline', [CheckoutController::class, 'checkoutOffline']);

        Route::post('/transaction-history/invoices', [InvoiceController::class, 'getTransactionHistory']);
        Route::put('/upload/nomination-form/invoice/{invoice_id}', [InvoiceController::class, 'uploadNominationForm']);
        Route::post('/upload/proof-of-payment/invoice', [InvoiceController::class, 'uploadProofOfPayment']);
        Route::post('/upload/commitment-letter/invoice', [InvoiceController::class, 'uploadCommitmentLetter']);
        Route::post('/upload/prime-requirements/invoice', [InvoiceController::class, 'uploadPrimeRequirements']);
        Route::post('/cancel/transaction/invoice/{invoice_id}', [InvoiceController::class, 'cancelTransaction']);
        Route::post('/update/delivery-receipt/invoice/{invoice_id}', [InvoiceController::class, 'updateDeliveryReceipt']);
        
        Route::post('/download/certificate', [CertificateController::class, 'downloadCertificate']);
        Route::post('/generate/certificate', [CertificateController::class, 'generateCertificate']);
        Route::post('/certificates', [CertificateController::class, 'certificates']);

        Route::post('/scorm/submission', [ScormController::class, 'storeScormSubmission']);
        Route::post('/scorm/score/set', [ScormController::class, 'storeScormScore']);
        Route::post('/scorm/progress', [ScormController::class, 'storeScormProgress']);
        Route::get('/scorm/progress', [ScormController::class, 'getScormProgress']);

        Route::get('/forum/post/own', [ForumPostController::class, 'getOwnedForumPost']);

        Route::resource('/forum/post', ForumPostController::class)->only(['index', 'show', 'store', 'update', 'destroy'])->names([
            'index' => 'user_forum_post.index',
            'show' => 'user_forum_post.show',
            'store' => 'user_forum_post.store',
            'update' => 'user_forum_post.update',
            'destroy' => 'user_forum_post.destroy',
        ]);
        
        Route::resource('/forum/post/comment', ForumPostCommentController::class)->only(['store', 'update', 'destroy'])->names([
            'store' => 'user_forum_post_comment.store',
            'update' => 'user_forum_post_comment.update',
            'destroy' => 'user_forum_post_comment.destroy',
        ]);
    });
});
