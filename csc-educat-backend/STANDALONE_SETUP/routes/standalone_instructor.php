<?php

declare(strict_types=1);

/**
 * Standalone version of routes/tenants/instructor.php
 *
 * Identical to the original except the three tenancy middlewares have been removed:
 *   - InitializeTenancyByDomain
 *   - PreventAccessFromCentralDomains
 *   - TenantActivated
 *
 * All routes still require auth:api (unchanged).
 */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Tenants\Teacher\AssessmentController;
use App\Http\Controllers\Api\V1\Tenants\Teacher\AssessmentQuestionController;
use App\Http\Controllers\Api\V1\Tenants\Teacher\CourseController;
use App\Http\Controllers\Api\V1\Tenants\Teacher\ClassCategoryController;
use App\Http\Controllers\Api\V1\Tenants\Teacher\ModuleController;
use App\Http\Controllers\Api\V1\Tenants\Teacher\TopicController;
use App\Http\Controllers\Api\V1\Tenants\Teacher\SubTopicController;
use App\Http\Controllers\Api\V1\Tenants\Teacher\RubricController;
use App\Http\Controllers\Api\V1\Tenants\Teacher\PrivateChatController;
use App\Http\Controllers\Api\V1\Tenants\Teacher\GroupChatController;
use App\Http\Controllers\Api\V1\Tenants\Teacher\AnnouncementController;
use App\Http\Controllers\Api\V1\Tenants\Teacher\AnnouncementCommentController;
use App\Http\Controllers\Api\V1\Tenants\Teacher\GradingController;
use App\Http\Controllers\Api\V1\Tenants\Teacher\StudentOutputController;
use App\Http\Controllers\Api\V1\Tenants\Teacher\ZoomMeetingController;
use App\Http\Controllers\Api\V1\Tenants\Teacher\EnrollmentController;
use App\Http\Controllers\Api\V1\Tenants\Teacher\CourseEvaluationController;
use App\Http\Controllers\Api\V1\Tenants\Teacher\CourseEvaluationQuestionController;
use App\Http\Controllers\Api\V1\Tenants\Teacher\ModuleEvaluationController;
use App\Http\Controllers\Api\V1\Tenants\Teacher\ModuleEvaluationQuestionController;
use App\Http\Controllers\Api\V1\Tenants\Teacher\ScormActivityController;
use App\Http\Controllers\Api\V1\Tenants\Teacher\CertificateController;
use App\Http\Controllers\Api\V1\Tenants\Teacher\ReportController;
use App\Http\Controllers\Api\V1\Tenants\Teacher\ExportController;
use App\Http\Controllers\Api\V1\Tenants\Teacher\ScormController;
use App\Http\Controllers\Api\V1\Tenants\Teacher\ScormRegistrationController;
use App\Http\Controllers\Api\V1\Tenants\Teacher\ActivityLogController;
use App\Http\Controllers\Api\V1\Tenants\Teacher\CertificateTemplateController;
use App\Http\Controllers\Api\V1\Tenants\Teacher\ForumPostCommentController;
use App\Http\Controllers\Api\V1\Tenants\Teacher\ForumPostController;
use App\Http\Controllers\Api\V1\Tenants\Teacher\QuestionPoolController;
use App\Http\Controllers\Api\V1\Tenants\Teacher\QuestionPoolQuestionController;
use App\Http\Controllers\Api\V1\Tenants\Teacher\CourseGroupController;
use App\Http\Controllers\Api\V1\Tenants\Teacher\GroupActivityController;

Route::middleware(['auth:api'])->group(function () {
    Route::get('/class-categories', [ClassCategoryController::class, 'index']);
    Route::get('/courses/all', [CourseController::class, 'getAllCourses']);
    Route::post('/courses/{status}', [CourseController::class, 'getCourses']);

    Route::get('/course/{course_id}/modules', [CourseController::class, 'getModules']);
    Route::get('/course/{course_id}/assessments', [CourseController::class, 'getAssessments']);
    Route::get('/course/{course_id}/evaluations', [CourseController::class, 'getCourseEvaluations']);
    Route::post('/course/{course_id}/duplicate', [CourseController::class, 'duplicateCourse']);

    Route::get('/course/{course_id}/certificates/enrollees', [CourseController::class, 'getCourseCertificatesEnrollees']);
    Route::post('/course/{course_id}/certificates/enrollees/assign', [CourseController::class, 'setAssignCertificateEnrollees']);

    Route::put('/course/{course_id}/upload-image', [CourseController::class, 'uploadCourseImage']);
    Route::delete('/course/{course_id}/delete-image', [CourseController::class, 'deleteCourseImage']);
    Route::put('/course/{course_id}/certificate-template', [CourseController::class, 'setCourseCertificateTemplate']);
    Route::put('/course/{course_id}/class-tags', [CourseController::class, 'tagClasses']);
    Route::put('/course/{course_id}/status', [CourseController::class, 'updateCourseStatus']);
    Route::put('/course/{course_id}/updated-at', [CourseController::class, 'updateCourseUpdatedAt']);
    Route::get('/course/training-credits', [CourseController::class, 'getTrainingCredits']);
    Route::post('/course/{course_id}/group-chat', [CourseController::class, 'createGroupChat']);
    Route::get('/course/{course_id}/group-chats', [CourseController::class, 'getCourseGroupChats']);
    Route::delete('/course/{course_id}/group-chat/{group_chat_id}', [CourseController::class, 'deleteCourseGroupChat']);

    Route::get('/share/course/users', [CourseController::class, 'shareCourseUsers']);
    Route::put('/share/course/permission', [CourseController::class, 'shareCoursePermission']);
    Route::put('/share/course/users/remove', [CourseController::class, 'revokeCourseAccess']);
    Route::put('/share/course', [CourseController::class, 'shareCourse']);
    Route::post('/restore/course', [CourseController::class, 'restoreCourse']);
    Route::get('/deleted/courses', [CourseController::class, 'deletedCourses']);
    Route::post('/course/{course_id}/permanent-delete', [CourseController::class, 'forceDelete']);
    Route::resource('/courses', CourseController::class);

    Route::post('/module/sync/prerequisites', [ModuleController::class, 'syncModulePrerequisite']);
    Route::put('/module/sort', [ModuleController::class, 'sortModules']);
    Route::put('/module/{module_id}/validity', [ModuleController::class, 'updateModuleValidity']);
    Route::get('/module/{module_id}/evaluations', [ModuleController::class, 'getModuleEvaluations']);
    Route::get('/module/fields', [ModuleController::class, 'getCustomModuleFields']);
    Route::resource('/modules', ModuleController::class);
    Route::put('/module-content/sort', [ModuleController::class, 'sortModuleContents']);

    Route::put('/topic/sort', [TopicController::class, 'sortTopics']);
    Route::resource('/topics', TopicController::class);

    Route::put('/sub-topic/sort', [SubTopicController::class, 'sortSubTopics']);
    Route::resource('/sub-topics', SubTopicController::class);

    Route::get('/assessment/{assessment_id}', [AssessmentController::class, 'getAssessment']);
    Route::get('/assessment/{assessment_id}/user-access-tags', [AssessmentController::class, 'getTagUserAccess']);
    Route::put('/assessment/{assessment_id}/user-access-tags', [AssessmentController::class, 'tagUserAccess']);
    Route::get('/assessment/{assessment_id}/{assessment_question_id}/answers', [AssessmentController::class, 'assessmentQuestionAnswers']);
    Route::get('/course/{course_id}/assessment/{assessment_id}/class-enrollee', [AssessmentController::class, 'showClassHasUsers']);
    Route::post('/assessment/{assessment_id}/list/export', [AssessmentController::class, 'assessmentResponsesListExport']);
    Route::post('/assessment/{assessment_id}/responses/export', [AssessmentController::class, 'assessmentResponsesSummaryExport']);

    Route::get('/assessment/{assessment_id}/questions', [AssessmentController::class, 'showAssessmentQuestions']);
    Route::put('/assessment/{assessment_id}/sort', [AssessmentController::class, 'sortAssessmentQuestions']);

    Route::post('/assessment/{assessment_id}/share', [AssessmentController::class, 'shareAssessment']);
    Route::post('/assessment/{assessment_id}/duplicate', [AssessmentController::class, 'duplicateAssessment']);
    Route::put('/assessment/{assessment_id}/move-to-course', [AssessmentController::class, 'moveAssessment']);

    Route::get('/assessment/instructor/list', [AssessmentController::class, 'showShareToInstructors']);
    Route::post('/assessment/shared-with-me', [AssessmentController::class, 'showSharedWithMeAssessments']);
    Route::post('/assessment/shared-with-me/move', [AssessmentController::class, 'moveSharedWithMeAssessment']);
    Route::post('/assessment/shared-to-others', [AssessmentController::class, 'showSharedToOtherAssessments']);

    Route::resource('/assessments', AssessmentController::class);
    Route::resource('/assessment/questions', AssessmentQuestionController::class);

    Route::resource('/group-activity', GroupActivityController::class);
    Route::get('/group-activity/{group_activity_id}/groups', [GroupActivityController::class, 'getGroupActivityGroups']);
    Route::delete('/group-activity/{group_activity_id}/groups/{group_id}', [GroupActivityController::class, 'deleteGroupActivityGroup']);
    Route::post('/group-activity/{group_activity_id}/groups/{group_id}/generate', [GroupActivityController::class, 'generateGroupActivityGroupFile']);
    Route::post('/group-activity/{group_activity_id}/groups/{group_id}/members/add', [GroupActivityController::class, 'addGroupActivityGroupMembers']);
    Route::delete('/group-activity/{group_activity_id}/groups/{group_id}/members/remove', [GroupActivityController::class, 'removeGroupActivityGroupMembers']);
    Route::get('/group-activity/{group_activity_id}/groups/non-members', [GroupActivityController::class, 'getGroupActivityGroupNonMembers']);
    Route::get('/group-activity/{group_activity_id}/groups/{group_id}/members', [GroupActivityController::class, 'getGroupActivityGroupMembers']);

    Route::get('/rubric/add-criteria/{rubric_id}', [RubricController::class, 'addCriteria']);
    Route::delete('/rubric/delete-criteria/{rubric_criteria_id}', [RubricController::class, 'deleteRubricCriteria']);
    Route::post('/rubric/update-criteria/{criteria_id}', [RubricController::class, 'updateCriteria']);
    Route::post('/rubric/update-indicator/{indicator_id}', [RubricController::class, 'updateIndicator']);
    Route::get('/rubric/add-rating/{rubric_id}', [RubricController::class, 'addRating']);
    Route::post('/rubric/update-rating/{rating_id}', [RubricController::class, 'updateRating']);
    Route::delete('/rubric/delete-rating/{rating_id}', [RubricController::class, 'deleteRubricRatings']);
    Route::post('/rubric/{rubric_id}/share', [RubricController::class, 'shareRubric']);
    Route::get('/rubric/shared-with-me', [RubricController::class, 'showSharedWithMeRubrics']);
    Route::post('/rubric/shared-with-me/move', [RubricController::class, 'moveSharedWithMeRubric']);
    Route::get('/rubric/shared-to-others', [RubricController::class, 'showSharedToOtherRubrics']);
    Route::get('/rubric/share-to-instructors', [RubricController::class, 'showShareToInstructors']);
    Route::resource('/rubrics', RubricController::class);

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
        Route::delete('/remove/user/group/chat/{group_chat_uuid}', 'removeUserToGroupChat');
        Route::post('/group/chat/photo/{group_chat_uuid}', 'updateGroupPhoto');
        Route::delete('/group/chat/photo/{group_chat_uuid}/remove', 'removeGroupPhoto');
        Route::get('/group/chat/list', 'showGroupChatList');
        Route::get('/group/chat/{group_chat_uuid}', 'showGroupChat');
        Route::get('/group/chat/{group_chat_uuid}/contact/list', 'getContacts');
        Route::post('/group/chat/send', 'sendGroupMessage');
        Route::post('/group/chat/attachment/download', 'downloadAttachment');
    });

    Route::get('/activity-log/stats', [ActivityLogController::class, 'activityLogStats']);
    Route::post('/activity-log/export', [ActivityLogController::class, 'activityLogExport']);
    Route::resource('/activity-log', ActivityLogController::class)->only(['index'])->names([
        'index' => 'instructor.activity-log.index',
    ]);

    Route::get('/announcement/{id}', [AnnouncementController::class, 'getAnnouncement']);
    Route::resource('/announcements', AnnouncementController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
    Route::resource('/announcement/comments', AnnouncementCommentController::class)->names('announcement.comment');

    Route::put('/grading/course-evaluation/update/checked', [GradingController::class, 'updateGradingCourseEvaluationStatus']);
    Route::put('/grading/module-evaluation/update/checked', [GradingController::class, 'updateGradingModuleEvaluationStatus']);
    Route::put('/grading/assessment/update/checked', [GradingController::class, 'updateGradingAssessmentStatus']);
    Route::put('/grading/update/score', [GradingController::class, 'updateGradingScore']);
    Route::post('/grading/check-assessment-answers', [GradingController::class, 'check_assessment']);
    Route::get('/grading/assessment/{assessment_id}', [GradingController::class, 'showGradingAssessment']);
    Route::get('/grading/{assessment_id}/students', [GradingController::class, 'showGradingStudents']);
    Route::get('/grading/group-activity/{group_activity_id}/groups', [GradingController::class, 'showGradingGroupActivityGroups']);
    Route::get('/grading/group-activity/{group_activity_id}/group/{group_id}', [GradingController::class, 'showGradingGroupActivityGroup']);
    Route::put('/grading/group-activity/{group_activity_id}/group/{group_id}/checked', [GradingController::class, 'updateGradingGroupActivityStatus']);
    Route::resource('/grading', GradingController::class)->only(['index', 'show']);

    Route::get('/student-output/courses', [StudentOutputController::class, 'courses']);
    Route::get('/student-output/course/{course_uuid}/enrollees', [StudentOutputController::class, 'enrollees']);
    Route::post('/student-output/course/{course_uuid}/enrollees/export', [StudentOutputController::class, 'exportEnrollees']);
    Route::get('/student-output/course/{course_uuid}/enrollees/{user_id}/progress', [StudentOutputController::class, 'enrolleeProgress']);
    Route::get('/student-output/course/{course_uuid}/enrollees/{user_id}/progress/zoom-meeting', [StudentOutputController::class, 'enrolleeProgressZoomMeeting']);
    Route::get('/student-output/course/{course_uuid}/enrollees/{user_id}/progress/course', [StudentOutputController::class, 'enrolleeProgressCourse']);
    Route::get('/student-output/courses/enrollees/{user_id}/progress/courses', [StudentOutputController::class, 'enrolleeProgressCourses']);
    Route::post('/student-output/courses/enrollees/{user_id}/topic/{topic_id}/mark-as-completed', [StudentOutputController::class, 'markAsCompletedEnrolleeTopic']);
    Route::post('/student-output/courses/enrollees/{user_id}/sub-topic/{subtopic_id}/mark-as-completed', [StudentOutputController::class, 'markAsCompletedtopicEnrolleeSubtopic']);
    Route::get('/student-output/user/{user_uuid}', [StudentOutputController::class, 'studentInfo']);
    Route::get('/student-output/class-categories', [StudentOutputController::class, 'classCategories']);
    Route::get('/student-output/assessments', [StudentOutputController::class, 'assessments']);
    Route::get('/student-output/modules/{class_category_id}/{course_uuid}', [StudentOutputController::class, 'modules']);
    Route::get('/student-output', [StudentOutputController::class, 'studentRecords']);
    Route::get('/student-output/course-progress/{user_uuid}', [StudentOutputController::class, 'showStudentCourseProgress']);
    Route::get('/student-output/course-progress/{course_uuid}/student/{user_uuid}/details', [StudentOutputController::class, 'showStudentCourseProgressDetails']);
    Route::get('/student-output/overall-grading/{user_uuid}', [StudentOutputController::class, 'showStudentOverallGrading']);

    Route::post('zoom-meetings/request/access_token', [ZoomMeetingController::class, 'requestAccessToken']);
    Route::get('/{course_id}/zoom-meetings/participants', [ZoomMeetingController::class, 'zoomMeetingParticipants']);
    Route::post('/{course_id}/zoom-meetings/participants/attendance/update', [ZoomMeetingController::class, 'zoomMeetingParticipantsAttendanceUpdate']);
    Route::post('/{course_id}/zoom-meetings/participants/export', [ZoomMeetingController::class, 'zoomMeetingParticipantsExport']);
    Route::get('/zoom-meetings/{zoom_meeting_id}/participant/{user_id}', [ZoomMeetingController::class, 'getZoomMeetingParticipant']);
    Route::post('/zoom-meetings/{zoom_meeting_id}/participant/{user_id}', [ZoomMeetingController::class, 'postZoomMeetingParticipant']);
    Route::resource('zoom-meetings', ZoomMeetingController::class);

    Route::get('/manual/enrollment/user/{user_id}', [EnrollmentController::class, 'showUser']);
    Route::get('/enrollment/courses', [EnrollmentController::class, 'showCourses']);
    Route::post('/enrollment/courses/export', [EnrollmentController::class, 'exportCourseInvoices']);
    Route::get('/enrollment/course/{course_uuid}/enrollees', [EnrollmentController::class, 'showCourseEnrollees']);
    Route::post('/enrollment/course/{course_uuid}/invoice/export', [EnrollmentController::class, 'exportInvoice']);
    Route::post('/manual/enrollment', [EnrollmentController::class, 'store']);
    Route::post('/manual/enrollment/proof-of-payment', [EnrollmentController::class, 'uploadProofOfPayment']);
    Route::post('/manual/enrollment/commitment-letter', [EnrollmentController::class, 'uploadCommitmentLetter']);
    Route::post('/manual/enrollment/prime-requirements', [EnrollmentController::class, 'uploadPrimeRequirements']);

    Route::get('/course/evaluation/{course_evaluation_id}/respondents', [CourseEvaluationController::class, 'showCourseEvaluationRespondents']);
    Route::get('/course/evaluation/{course_evaluation_id}/respondent/{user_id}/attempts', [CourseEvaluationController::class, 'showCourseEvaluationRespondentAttempts']);
    Route::get('/course/evaluation/attempt/{student_course_evaluation_id}/respondent/{user_id}/answers', [CourseEvaluationController::class, 'showCourseEvaluationRespondentAnswers']);
    Route::put('/course/evaluation/attempt/{student_course_evaluation_id}/check', [CourseEvaluationController::class, 'updateStudentCourseEvaluationStatus']);
    Route::get('/course/evaluation/{course_evaluation_id}/answers', [CourseEvaluationController::class, 'courseEvaluationAnswers']);
    Route::put('/course/evaluation/{course_evaluation_id}/validity', [CourseEvaluationController::class, 'updateCourseEvaluationValidity']);
    Route::put('/course/evaluation/{course_evaluation_id}/sort', [CourseEvaluationController::class, 'sortQuestions']);
    Route::resource('/course/evaluations', CourseEvaluationController::class)->only(['show', 'store', 'update', 'destroy'])->names([
        'show' => 'course_evaluation.show',
        'store' => 'course_evaluation.store',
        'update' => 'course_evaluation.update',
        'destroy' => 'course_evaluation.destroy',
    ]);

    Route::get('/course/evaluation/{course_evaluation_id}/{course_evaluation_question_id}/answers', [CourseEvaluationQuestionController::class, 'courseEvaluationQuestionAnswers']);
    Route::post('/course/evaluation/question/{course_evaluation_question_id}/image', [CourseEvaluationQuestionController::class, 'uploadQuestionImage']);
    Route::delete('/course/evaluation/question/image/{image_id}', [CourseEvaluationQuestionController::class, 'deleteQuestionImage']);
    Route::get('/course/evaluation/question/{course_evaluation_question_id}/image', [CourseEvaluationQuestionController::class, 'getQuestionImages']);
    Route::put('/course/evaluation/questions', [CourseEvaluationQuestionController::class, 'updateQuestions']);
    Route::get('/course/evaluation/questions/{id}', [CourseEvaluationQuestionController::class, 'getQuestions']);
    Route::resource('/course/evaluation/questions', CourseEvaluationQuestionController::class)->only(['store', 'destroy'])->names([
        'store' => 'course_evaluation_questions.store',
        'destroy' => 'course_evaluation_questions.destroy',
    ]);

    Route::get('/module/evaluation/{module_evaluation_id}/respondents', [ModuleEvaluationController::class, 'showModuleEvaluationRespondents']);
    Route::get('/module/evaluation/{module_evaluation_id}/respondent/{user_id}/attempts', [ModuleEvaluationController::class, 'showModuleEvaluationRespondentAttempts']);
    Route::get('/module/evaluation/attempt/{student_module_evaluation_id}/respondent/{user_id}/answers', [ModuleEvaluationController::class, 'showModuleEvaluationRespondentAnswers']);
    Route::put('/module/evaluation/attempt/{student_module_evaluation_id}/check', [ModuleEvaluationController::class, 'updateStudentModuleEvaluationStatus']);
    Route::get('/module/evaluation/{module_evaluation_id}/answers', [ModuleEvaluationController::class, 'moduleEvaluationAnswers']);
    Route::put('/module/evaluation/{module_evaluation_id}/validity', [ModuleEvaluationController::class, 'updateModuleEvaluationValidity']);
    Route::put('/module/evaluation/{module_evaluation_id}/sort', [ModuleEvaluationController::class, 'sortQuestions']);
    Route::resource('/module/evaluations', ModuleEvaluationController::class)->only(['store', 'show', 'update', 'destroy'])->names([
        'store' => 'module_evaluation.store',
        'show' => 'module_evaluation.show',
        'update' => 'module_evaluation.update',
        'destroy' => 'module_evaluation.destroy',
    ]);

    Route::get('/module/evaluation/{module_evaluation_id}/{module_evaluation_question_id}/answers', [ModuleEvaluationQuestionController::class, 'moduleEvaluationQuestionAnswers']);
    Route::post('/module/evaluation/question/{module_evaluation_question_id}/image', [ModuleEvaluationQuestionController::class, 'uploadQuestionImage']);
    Route::delete('/module/evaluation/question/image/{image_id}', [ModuleEvaluationQuestionController::class, 'deleteQuestionImage']);
    Route::get('/module/evaluation/question/{module_evaluation_question_id}/image', [ModuleEvaluationQuestionController::class, 'getQuestionImages']);
    Route::put('/module/evaluation/questions', [ModuleEvaluationQuestionController::class, 'updateQuestions']);
    Route::get('/module/evaluation/questions/{id}', [ModuleEvaluationQuestionController::class, 'getQuestions']);
    Route::resource('/module/evaluation/questions', ModuleEvaluationQuestionController::class)->only(['store', 'destroy'])->names([
        'store' => 'module_evaluation_questions.store',
        'destroy' => 'module_evaluation_questions.destroy',
    ]);

    Route::post('/generate/certificate', [CertificateController::class, 'generateCertificate']);

    Route::controller(ReportController::class)->group(function () {
        Route::get('/report/list/cards', 'showTotalListCards');
        Route::get('/report/list/enrollees', 'showCoursesEnrolleeCount');
        Route::get('/report/list/courses', 'showCourses');
        Route::get('/report/list/courses/export', 'showCoursesExport');
        Route::get('/report/list/course/{course_id}/completers', 'showCourseCompleters');
        Route::get('/report/list/course/{course_id}/completers/export', 'showCourseCompletersExport');
        Route::get('/report/list/course/{course_id}/users', 'showCourseUsers');
        Route::get('/report/list/course/{course_id}/users/export', 'showCourseUsersExport');
        Route::get('/report/list/assessments', 'showAssessments');
        Route::get('/report/list/{course_id}/assessments', 'showAssessmentData');
        Route::post('/report/list/{course_id}/assessments/export', 'showAssessmentDataExport');
        Route::get('/report/total-users-per-course', 'showTotalUsersPerCourse');
        Route::get('/report/custom-registration/field/lists', 'listOfCustomRegistrationFields');
    });

    Route::controller(ScormController::class)->group(function () {
        Route::get('/scorm/course/upload/progress', 'getScormUploadProgress');
        Route::post('/scorm/course/build/preview-link', 'updateScormPreviewLink');
        Route::get('/scorm/course/summary', 'getScormCourseSummary');
        Route::get('/scorm/course/learners/details', 'getScormCourseAllLearnersDetailsSummary');
        Route::get('/scorm/course/learners', 'getScormCourseLearners');
        Route::post('/scorm/course/learners/export', 'scormCourseLearnersExport');
        Route::get('/scorm/course/learner/progress', 'getScormCourseLearnerProgress');
        Route::get('/scorm/course/learner/interactions', 'getScormCourseLearnerInteractions');
        Route::get('/scorm/course/learners/submissions', 'getScormCourseLearnersSubmissions');
    });

    Route::get('/scorm/registration/summary', [ScormRegistrationController::class, 'getScormCourseLearnerRegistrationSummary']);
    Route::resource('/scorm/registration', ScormRegistrationController::class);

    Route::controller(ScormActivityController::class)->group(function () {
        Route::get('/scorm-activity', 'index');
        Route::get('/{course_id}/scorm-activity/{scorm_activity_id}', 'show');
        Route::get('/{course_id}/scorm-activity/{scorm_activity_id}/questions', 'getScormActivityQuestions');
        Route::get('/{course_id}/scorm-activity/{scorm_activity_id}/question/{question_id}/answers', 'getScormActivityQuestionAnswers');
        Route::post('/{course_id}/scorm-activity/{scorm_activity_id}/respondents', 'getScormActivityRespondents');
        Route::post('/{course_id}/scorm-activity/{scorm_activity_id}/respondents/list/export', 'scormActivityRespondentsExport');
        Route::post('/{course_id}/scorm-activity/{scorm_activity_id}/respondents/answers/export', 'scormActivityRespondentsAnswersExport');
        Route::get('/{course_id}/scorm-activity/{scorm_activity_id}/learner/{user_id}', 'getLearnerScormActivityAnswers');
    });

    Route::resource('/certificate-template', CertificateTemplateController::class);
    Route::get('/certificate-template/category/list', [CertificateTemplateController::class, 'getCertificateTemplateCategory']);
    Route::post('/certificate-template/{id}/duplicate', [CertificateTemplateController::class, 'duplicateCertificateTemplate']);

    Route::post('/course/evaluation/list/export', [ExportController::class, 'courseEvaluationListExport']);
    Route::post('/module/evaluation/list/export', [ExportController::class, 'moduleEvaluationListExport']);
    Route::post('/course/evaluation/summary/export', [ExportController::class, 'courseEvaluationSummaryExport']);
    Route::post('/module/evaluation/summary/export', [ExportController::class, 'moduleEvaluationSummaryExport']);
    Route::post('/course/{course_id}/export', [ExportController::class, 'courseExport']);
    Route::resource('/exports', ExportController::class);
    Route::post('/download/exports', [ExportController::class, 'downloadExportFile']);

    Route::resource('/forum/post', ForumPostController::class);
    Route::resource('/forum/post/comment', ForumPostCommentController::class);

    Route::resource('/question-pools', QuestionPoolController::class);
    Route::post('/question-pool/assign/course-evaluation', [QuestionPoolController::class, 'questionPoolCourseEvaluationAssignment']);
    Route::post('/question-pool/assign/module-evaluation', [QuestionPoolController::class, 'questionPoolModuleEvaluationAssignment']);
    Route::post('/question-pool/assign/assessment', [QuestionPoolController::class, 'questionPoolAssessmentAssignment']);
    Route::resource('/question-pool/question', QuestionPoolQuestionController::class);
    Route::post('/question-pool/question/{question_id}/image', [QuestionPoolQuestionController::class, 'uploadQuestionImage']);
    Route::delete('/question-pool/question/{question_id}/image', [QuestionPoolQuestionController::class, 'deleteQuestionImage']);
    Route::get('/question-pool/question/{question_id}/image', [QuestionPoolQuestionController::class, 'getQuestionImages']);

    Route::resource('course-groups', CourseGroupController::class);
    Route::get('/course-groups/{course_group_id}/members', [CourseGroupController::class, 'getCourseGroupMembers']);
    Route::post('/course-groups/{course_group_id}/members/add', [CourseGroupController::class, 'addCourseGroupMembers']);
    Route::delete('/course-groups/{course_group_id}/members/remove', [CourseGroupController::class, 'removeCourseGroupMembers']);
    Route::get('/course-groups/{course_group_id}/user-list', [CourseGroupController::class, 'getCourseGroupUserList']);
    Route::post('/course-groups/{course_group_id}/group-chat', [CourseGroupController::class, 'createGroupChat']);
});
