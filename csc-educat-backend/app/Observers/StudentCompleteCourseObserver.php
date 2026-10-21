<?php

namespace App\Observers;

use App\Models\StudentCompleteCourse;
use App\Models\User;

use Str;

class StudentCompleteCourseObserver
{
    public function creating(StudentCompleteCourse $studentCompleteCourse)
    {
    }

    /**
     * Handle the StudentCompleteCourse "created" event.
     *
     * @param  \App\Models\StudentCompleteCourse  $studentCompleteCourse
     * @return void
     */
    public function created(StudentCompleteCourse $studentCompleteCourse)
    {
        $user = User::find($studentCompleteCourse->user_id);

        activity('user_course_complete')
            ->causedBy($user)
            ->withProperties([
                'user_id' => $studentCompleteCourse->user_id,
                'email' => $user->email,
                'course_id' => $studentCompleteCourse->course_id,
            ])
            ->event('created')
            ->performedOn($user)
            ->log('User Course Completed');
    }

    /**
     * Handle the StudentCompleteCourse "updated" event.
     *
     * @param  \App\Models\StudentCompleteCourse  $studentCompleteCourse
     * @return void
     */
    public function updated(StudentCompleteCourse $studentCompleteCourse)
    {
        //
    }

    /**
     * Handle the StudentCompleteCourse "deleted" event.
     *
     * @param  \App\Models\StudentCompleteCourse  $studentCompleteCourse
     * @return void
     */
    public function deleted(StudentCompleteCourse $studentCompleteCourse)
    {
        //
    }

    /**
     * Handle the StudentCompleteCourse "restored" event.
     *
     * @param  \App\Models\StudentCompleteCourse  $studentCompleteCourse
     * @return void
     */
    public function restored(StudentCompleteCourse $studentCompleteCourse)
    {
        //
    }

    /**
     * Handle the StudentCompleteCourse "force deleted" event.
     *
     * @param  \App\Models\StudentCompleteCourse  $studentCompleteCourse
     * @return void
     */
    public function forceDeleted(StudentCompleteCourse $studentCompleteCourse)
    {
        //
    }
}
