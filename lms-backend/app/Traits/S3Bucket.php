<?php

namespace App\Traits;

use Aws\S3\Exception\S3Exception;

trait S3Bucket
{
  public function client()
  {
    return \Aws\Laravel\AwsFacade::createClient('s3');
  }
  //@var Bucket name, Directory
  public function getObject($bucket, $dir)
  {
    try {
      $this->client()->getObject([
        'Bucket' => $bucket,
        'Key' => $dir
      ]);
    } catch (S3Exception $e) {
      return $e;
    }
  }
  //@var filename, bucket name, directory
  //upload files such videos and docx
  public function uploadObject($file, $bucket, $dir)
  {
    $this->client()->putObject([
      'Body'   => fopen($file, 'r+'),
      'Bucket' => $bucket,
      'Key' => $dir,
    ]);
  }

  public function uploadImage($file, $bucket, $dir): void
  {
    $this->client()->putObject([
      'Body'   => $file,
      'Bucket' => $bucket,
      'Key' => $dir,
    ]);
  }

  public function deleteObject($bucket, $dir): void
  {
    $this->client()->deleteObject([
      'Bucket' => $bucket,
      'Key'    => $dir
    ]);
  }
}
