<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;

class CreateS3 implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tenant;
    public $domain;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($tenant, $domain)
    {
        $this->tenant = $tenant;
        $this->domain = $domain;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      try {
          $s3 = \Aws\Laravel\AwsFacade::createClient('s3');

          $exp = explode('-', $this->tenant);
          $prod = config('services.app_env');
          $bucketName = 'bucket'.'-'.$exp[0].'-'.$prod;
          $result = $s3->createBucket(['Bucket' => $bucketName]);
          $policy =  [
            'Version' => "2012-10-17",
            "Statement" => [
              "Sid" => "Stmt1405592139000",
              "Effect" => "Allow",
              "Principal" => "*",
              "Action" => "s3:*",
              "Resource" => "arn:aws:s3:::{$bucketName}/*",
              "Condition" => [
                "StringLike" => [
                  "aws:Referer" => [
                    "https://{$this->domain}/*",
                    "http://{$this->domain}/*",
                    "http://localhost:8081/*",
                    "http://localhost:8082/*",
                  ]
                ]
              ]
            ]
          ];


          $s3->putBucketPolicy([
              'Bucket' => $bucketName,
              'Policy' => json_encode($policy),
          ]);

          $s3->putBucketCors([
            'Bucket' => $bucketName,
            'CORSConfiguration' => [
              'CORSRules' => [
                [
                  'AllowedHeaders' => ['*'],
                  'AllowedMethods' => ['PUT', 'POST', 'GET'],
                  'AllowedOrigins' => ['*'],
                  'ExposeHeaders' => [],
                ],
              ],
            ],
          ]);

          return 'The bucket\'s location is: ' .
            $result['Location'] . '. ' .
            'The bucket\'s effective URI is: ' .
            $result['@metadata']['effectiveUri'];
      } catch (S3Exception $e) {

          return response()->json($e->getAwsErrorMessage(), 402);
      }
    }
}
