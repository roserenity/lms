<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

use App\Observers\AssessmentObserver;
use App\Observers\AssessmentAnswerObserver;
use App\Observers\AssessmentQuestionObserver;
use App\Observers\ModuleObserver;
use App\Observers\RubricObserver;
use App\Observers\RubricCriteriaObserver;
use App\Observers\RubricRatingObserver;
use App\Observers\TopicObserver;
use App\Observers\PrivateChatObserver;
use App\Observers\PrivateMessageObserver;
use App\Observers\GroupChatObserver;
use App\Observers\GroupMessageObserver;
use App\Observers\AnnouncementObserver;
use App\Observers\AnnouncementCommentObserver;
use App\Observers\ForumPostCommentObserver;
use App\Observers\TicketObserver;
use App\Observers\TicketMessageObserver;
use App\Observers\CourseEvaluationObserver;
use App\Observers\CourseEvaluationQuestionObserver;
use App\Observers\ModuleEvaluationObserver;
use App\Observers\ModuleEvaluationQuestionObserver;
use App\Observers\InvoiceObserver;
use App\Observers\CertificateObserver;
use App\Observers\StudentCourseEvaluationObserver;
use App\Observers\StudentCourseEvaluationAnswerObserver;
use App\Observers\StudentModuleEvaluationObserver;
use App\Observers\StudentModuleEvaluationAnswerObserver;
use App\Observers\EnrollmentObserver;
use App\Observers\StudentCompleteCourseObserver;
use App\Observers\AdvisoryObserver;
use App\Observers\ForumPostObserver;
use App\Observers\QuestionPoolObserver;
use App\Observers\QuestionPoolQuestionObserver;
use App\Observers\GroupActivityObserver;
use App\Observers\ZoomMeetingObserver;

use App\Models\Assessment;
use App\Models\AssessmentAnswer;
use App\Models\AssessmentQuestion;
use App\Models\Module;
use App\Models\Rubric;
use App\Models\RubricCriteria;
use App\Models\RubricRating;
use App\Models\Topic;
use App\Models\PrivateChat;
use App\Models\PrivateMessage;
use App\Models\GroupChat;
use App\Models\GroupMessage;
use App\Models\Announcement;
use App\Models\AnnouncementComment;
use App\Models\Ticket;
use App\Models\CourseEvaluation;
use App\Models\CourseEvaluationQuestion;
use App\Models\ModuleEvaluation;
use App\Models\ModuleEvaluationQuestion;
use App\Models\Invoice;
use App\Models\Certificate;
use App\Models\Enrollment;
use App\Models\StudentCompleteCourse;
use App\Models\StudentCourseEvaluation;
use App\Models\StudentCourseEvaluationAnswer;
use App\Models\StudentModuleEvaluation;
use App\Models\StudentModuleEvaluationAnswer;
use App\Models\Advisory;
use App\Models\ForumPost;
use App\Models\ForumPostComment;
use App\Models\GroupActivity;
use App\Models\QuestionPool;
use App\Models\QuestionPoolQuestion;
use App\Models\TicketMessage;
use App\Models\ZoomMeeting;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        Assessment::observe(AssessmentObserver::class);
        AssessmentAnswer::observe(AssessmentAnswerObserver::class);
        AssessmentQuestion::observe(AssessmentQuestionObserver::class);
        Module::observe(ModuleObserver::class);
        Rubric::observe(RubricObserver::class);
        RubricCriteria::observe(RubricCriteriaObserver::class);
        RubricRating::observe(RubricRatingObserver::class);
        Topic::observe(TopicObserver::class);
        PrivateMessage::observe(PrivateMessageObserver::class);
        PrivateChat::observe(PrivateChatObserver::class);
        GroupChat::observe(GroupChatObserver::class);
        GroupMessage::observe(GroupMessageObserver::class);
        Announcement::observe(AnnouncementObserver::class);
        AnnouncementComment::observe(AnnouncementCommentObserver::class);
        Ticket::observe(TicketObserver::class);
        CourseEvaluation::observe(CourseEvaluationObserver::class);
        CourseEvaluationQuestion::observe(CourseEvaluationQuestionObserver::class);
        ModuleEvaluation::observe(ModuleEvaluationObserver::class);
        ModuleEvaluationQuestion::observe(ModuleEvaluationQuestionObserver::class);
        Invoice::observe(InvoiceObserver::class);
        Certificate::observe(CertificateObserver::class);
        StudentCourseEvaluation::observe(StudentCourseEvaluationObserver::class);
        StudentCourseEvaluationAnswer::observe(StudentCourseEvaluationAnswerObserver::class);
        StudentModuleEvaluation::observe(StudentModuleEvaluationObserver::class);
        StudentModuleEvaluationAnswer::observe(StudentModuleEvaluationAnswerObserver::class);
        Enrollment::observe(EnrollmentObserver::class);
        StudentCompleteCourse::observe(StudentCompleteCourseObserver::class);
        Advisory::observe(AdvisoryObserver::class);
        ForumPost::observe(ForumPostObserver::class);
        ForumPostComment::observe(ForumPostCommentObserver::class);
        QuestionPool::observe(QuestionPoolObserver::class);
        QuestionPoolQuestion::observe(QuestionPoolQuestionObserver::class);
        TicketMessage::observe(TicketMessageObserver::class);
        GroupActivity::observe(GroupActivityObserver::class);
        ZoomMeeting::observe(ZoomMeetingObserver::class);
    }
}
