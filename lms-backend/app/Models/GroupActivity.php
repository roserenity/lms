<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GroupActivity extends Model
{
    use HasFactory, SoftDeletes;

    const IS_GDOC = 'GDOC';
    const IS_GSHEET = 'GSHEET';
    const IS_GSLIDE = 'GSLIDE';

    protected $fillable = [
        'uuid',
        'course_id',
        'module_id',
        'title',
        'uploader_id',
        'group_activity_tool',
        'description',
        'due_date',
        'group_count'
    ];

    protected $hidden = [
        'deleted_at'
    ];

    protected $appends = [
        'group_activity_is_locked',
        // 'student_done_status'
    ];
    
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function uploader()
    {
      return $this->belongsTo(User::class);
    }
    
    public function group_activity_has_topics()
    {
        return $this->belongsToMany(Topic::class, 'group_activity_has_topics');
    }

    public function group_activity_has_sub_topics()
    {
        return $this->belongsToMany(SubTopic::class, 'group_activity_has_sub_topics');
    }
    
    public function student_module_progress()
    {
        return $this->morphMany(StudentModuleProgress::class, 'model');
    }

    public function module_content()
    {
        return $this->morphOne(ModuleContent::class, 'contentable');
    }

    public function group_activity_questions()
    {
        return $this->hasMany(GroupActivityQuestion::class);
    }

    public function group_activity_groups()
    {
        return $this->hasMany(GroupActivityGroup::class);
    }
    
    public function getGroupActivityIsLockedAttribute()
    {
      if(!$this->due_date) return false;
  
      try {
        $currentDate = Carbon::now('UTC')->startOfDay();
        $validUntilDate = Carbon::createFromFormat('Y-m-d', $this->due_date, 'UTC')->startOfDay();
        
        if ($currentDate->greaterThanOrEqualTo($validUntilDate)) {
            return true;
        }

        return false;
      } catch (\Exception $e) {
          return false;
      }
    }

    public function getStudentGroupActivityStatus($user_id)
    {
        return $this->morphOne(StudentModuleProgress::class, 'model')->where('status', StudentModuleProgress::STATUS_DONE)->where('user_id', $user_id)->count() > 0 ? true : false;
    }

    public function getStudentGroupActivityProgress($user_id)
    {
        return $this->student_module_progress()->where('user_id', $user_id)->where('status', StudentModuleProgress::STATUS_DONE)->first();
    }

    public function getStudentGroupActivityGroup($user_id)
    {
        return $this->group_activity_groups()->whereHas('group_activity_has_group_members', function($query) use ($user_id) {
                $query->where('user_id', $user_id);
            })->first();
    }


}
