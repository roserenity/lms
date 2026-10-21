<?php

namespace App\Traits;

use Aws\MediaConvert\MediaConvertClient;
use Aws\Exception\AwsException;

trait MediaConvert
{
  public function endPoint()
  {
    $client = new MediaConvertClient([
      'version' => '2017-08-29',
      'region' => 'ap-southeast-1',
    ]);
    try {
      $result = $client->describeEndpoints([]);
    } catch (AwsException $e) {
      return $e->getMessage();
    }

    return $result['Endpoints'][0]['Url'];
  }

  public function jobSettings($file, $dir, $bucket)
  {
    return [
      "OutputGroups" => [
        [
          "Name" => "File Group",
          "Outputs" => [
            [
              "Preset" => "System-Generic_Sd_Mp4_Avc_Aac_16x9_Sdr_640x360p_30Hz_0.8Mbps_Qvbr_Vq7",
              "Extension" => "mp4",
              "NameModifier" => "_640p"
            ],
            [
              "Preset" => "System-Generic_Hd_Mp4_Avc_Aac_16x9_Sdr_1280x720p_30Hz_5Mbps_Qvbr_Vq9",
              "Extension" => "mp4",
              "NameModifier" => "_720p"
            ],
            [
              "Preset" => "System-Generic_Hd_Mp4_Avc_Aac_16x9_Sdr_1920x1080p_30Hz_10Mbps_Qvbr_Vq9",
              "Extension" => "mp4",
              "NameModifier" => "_1080p"
            ],
          ],
          "OutputGroupSettings" => [
            "Type" => "FILE_GROUP_SETTINGS",
            "FileGroupSettings" => [
              "Destination" => "s3://{$bucket}/{$dir}/converted/"
            ]
          ]
        ]
      ],
      "AdAvailOffset" => 0,
      "Inputs" => [
        [
          "AudioSelectors" => [
            "Audio Selector 1" => [
              "Offset" => 0,
              "DefaultSelection" => "DEFAULT",
              "ProgramSelection" => 1
            ]
          ],
          "VideoSelector" => [
            "ColorSpace" => "FOLLOW",
            "Rotate" => "DEGREE_0",
            "AlphaBehavior" => "DISCARD"
          ],
          "FilterEnable" => "AUTO",
          "PsiControl" => "USE_PSI",
          "FilterStrength" => 0,
          "DeblockFilter" => "DISABLED",
          "DenoiseFilter" => "DISABLED",
          "InputScanType" => "AUTO",
          "TimecodeSource" => "ZEROBASED",
          "FileInput" => "s3://{$bucket}/{$dir}/{$file}"
        ]
      ],
      "AccelerationSettings" => [
        "Mode" =>  "DISABLED"
      ],
      "StatusUpdateInterval" => "SECONDS_60",
      "Priority" => 0,
      "HopDestinations" => []
    ];
  }

  public function mediaJob($video, $file, $dir, $bucket)
  {
    $mediaConvertClient = new MediaConvertClient([
      'version' => '2017-08-29',
      'region' => 'ap-southeast-1',
      'endpoint' => $this->endPoint()
    ]);

    try {
      $result = $mediaConvertClient->createJob([
        "Role" => config('services.mediaconvert.role'),
        "Settings" => $this->jobSettings($file, $dir, $bucket),
        "Queue" => config('services.mediaconvert.queue'),
        "UserMetadata" => [
          "Customer" => "Amazon"
        ],
      ]);
    } catch (AwsException $e) {

      $result = $e->getMessage();
    }

    $video->update([
      'job_id' => $result['Job']['Id'],
      'processed' => $result['Job']['Status'],
    ]);

    return $result;
  }
}
