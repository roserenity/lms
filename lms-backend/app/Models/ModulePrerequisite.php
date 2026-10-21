<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModulePrerequisite extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'module_id',
    'prerequisite_module_id',
    'uploader_id'
  ];

  protected $appends = ['module_status'];

  protected $casts = [
    'created_at' => 'date:Y-m-d h:i:s',
    'updated_at' => 'date:Y-m-d h:i:s'
  ];

  protected $hidden = [
    'deleted_at'
  ];

  public function module()
  {
    // return $this->hasOne(Module::class, 'id');
    return $this->belongsTo(Module::class, 'prerequisite_module_id');
  }

  public function getModuleStatusAttribute()
  {
    if(!$this->module) return 'lock';

    $topic_counts = $this->module->topics->count();
    $done_topic_counts = 0;
    foreach ($this->module->topics as $item) {
      if ($item->student_done_status) {
        $done_topic_counts++;
      }
    }
    return $done_topic_counts == $topic_counts ? 'unlocked' : 'lock';
  }
}
