<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Vimeo;

class LearningMaterial extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'url',
    'file_type',
    'learnable_id',
    'learnable_type',
    'is_downloadable'
  ];

  protected $casts = [
    'created_at' => 'date:Y-m-d h:i:s',
    'updated_at' => 'date:Y-m-d h:i:s'
  ];

  //protected $appends = [];

  public function getVimeoAttribute()
  {
    if ($this->file_type == 'mp4' && config('app.env') != 'local') {
      $request_file = Vimeo::request('/me' . $this->url, [], 'GET');
      $file_exist = $request_file['status'] != 404 ? $request_file : NULL;

      if(isset($file_exist)) {
        $response = [
          'uri' => $file_exist['body']['uri'],
          'link' => $file_exist['body']['link'],
          'player_embed_url' => $file_exist['body']['player_embed_url'],
          'embed_html' => $file_exist['body']['embed']['html']
        ];
      } else {
        $response = NULL;
      }

    } else {
      $response = NULL;
    }

    return $this->attributes['vime_file_url'] = $response;
  }

  public function getVimeoFileTranscodingStatusAttribute()
  {
    if ($this->file_type == 'mp4') {
      $request_file = Vimeo::request('/me' . $this->url . '?fields=transcode.status', [], 'GET');
      $response = $request_file['status'] != 404 ? $request_file : NULL;
    } else {
      $response = NULL;
    }
    return $this->attributes['vimeo_file_transcoding_status'] = $response['body']['transcode']['status'] ?? NULL;
  }

  public function appendVimeoAttributes()
  {
      $this->append(['vimeo', 'vimeo_file_transcoding_status']);
  }

  public function learnable()
  {
    return $this->morphTo();
  }
}
