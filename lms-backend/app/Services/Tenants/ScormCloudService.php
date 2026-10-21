<?php

namespace App\Services\Tenants;

use App\Models\LearningMaterial;
use Illuminate\Support\Facades\Http;
use App\Helpers\StandaloneConfig;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Scorm;
use App\Models\ScormRegistration;

class ScormCloudService
{
    
    private static function requestAccessToken()
    {
        $scormcloud_app_id = config('scormcloud.app_id');
        $scormcloud_app_secret = config('scormcloud.app_secret');
    
        $scope = "write:course read:course read:registration write:registration";
        $expiration = 300; // 5 minutes
    
        $response = Http::withBasicAuth($scormcloud_app_id, $scormcloud_app_secret)
            ->asForm()
            ->post('https://cloud.scorm.com/api/v2/oauth/authenticate/application/token', [
                'scope' => $scope,
                'expiration' => $expiration
            ]);
            
        if ($response->ok()) {
            return $response; // Return the entire JSON response
        } else {            
            Log::error('requestAccessToken error: '. json_encode($response->json()));
            return $response;
        }
    }
    
    private static function requestReadReportingAccessToken()
    {
        $scormcloud_app_id = config('scormcloud.app_id');
        $scormcloud_app_secret = config('scormcloud.app_secret');
    
        $scope = "read:reporting";
        $expiration = 300; // 5 minutes
    
        $response = Http::withBasicAuth($scormcloud_app_id, $scormcloud_app_secret)
            ->asForm()
            ->post('https://cloud.scorm.com/api/v2/oauth/authenticate/application/token', [
                'scope' => $scope,
                'expiration' => $expiration
            ]);
            
        if ($response->ok()) {
            return $response; // Return the entire JSON response
        } else {            
            Log::error('requestReadReportingAccessToken error: '. json_encode($response->json()));
            return $response;
        }
    }

    private static function requestReportageAccessToken()
    {
        try {
            $tokenData = self::requestReadReportingAccessToken(); // Call static method
            
            if(isset($tokenData['access_token'])) {
            
                $access_token = $tokenData['access_token'];
                
                // Make the POST request with multipart data.
                $response = Http::withHeaders([
                        'Authorization' => "Bearer {$access_token}",
                    ])
                    ->get('https://cloud.scorm.com/api/v2/reporting/reportageAuth?navPermission=FREENAV&admin=true');

                if ($response->ok()) {

                    return [
                        'result' => 'success',
                        'queryString' => $response['queryString'],
                        'tokenData' => $tokenData['access_token']
                    ];

                } else {
                    Log::error('request reportage access token error' . $response);

                    return [
                        'result' => 'error',
                        'message' => $response,
                    ];
                }
            } else {
                
                return [
                    'result' => 'error'
                ];

            }
        } catch (\Exception $e) {
            Log::error('Failed to retrieve access token:'. $e);
            throw new \Exception('Failed to retrieve access token');
        }
    }

    private static function requestDeleteRegistrationAccessToken()
    {
        $scormcloud_app_id = config('scormcloud.app_id');
        $scormcloud_app_secret = config('scormcloud.app_secret');
    
        $scope = "delete:registration";
        $expiration = 300; // 5 minutes
    
        $response = Http::withBasicAuth($scormcloud_app_id, $scormcloud_app_secret)
            ->asForm()
            ->post('https://cloud.scorm.com/api/v2/oauth/authenticate/application/token', [
                'scope' => $scope,
                'expiration' => $expiration
            ]);
            
        if ($response->ok()) {
            return $response; // Return the entire JSON response
        } else {            
            Log::error('requestDeleteRegistrationAccessToken error: '. json_encode($response->json()));
            return $response;
        }
    }

    private static function requestDeleteCourseAccessToken()
    {
        $scormcloud_app_id = config('scormcloud.app_id');
        $scormcloud_app_secret = config('scormcloud.app_secret');
    
        $scope = "delete:course";
        $expiration = 300; // 5 minutes
    
        $response = Http::withBasicAuth($scormcloud_app_id, $scormcloud_app_secret)
            ->asForm()
            ->post('https://cloud.scorm.com/api/v2/oauth/authenticate/application/token', [
                'scope' => $scope,
                'expiration' => $expiration
            ]);
            
        if ($response->ok()) {
            return $response; // Return the entire JSON response
        } else {            
            Log::error('requestDeleteCourseAccessToken error: '. json_encode($response->json()));
            return $response;
        }
    }

    public static function uploadCourse($scorm_cloud_course_id, $title, $file, $course_id, $model_id, $model_type)
    {
        try {
            $tokenData = self::requestAccessToken(); // Call static method
            
            if(isset($tokenData['access_token'])) {
                $access_token = $tokenData['access_token'];

                $queryParams = [
                    'courseId' => $scorm_cloud_course_id,
                    'mayCreateNewVersion' => true,
                ];
                
                if (config('app.env') != 'local') {
                    $queryParams['postbackUrl'] = 'https://' . StandaloneConfig::apiEndpoint() . '/v1/tenants/course/upload/callback';
                }

                $queryString = http_build_query($queryParams);

                // Make the POST request with multipart data.
                $response = Http::withHeaders([
                        'Authorization' => "Bearer {$access_token}",
                        'uploadedContentType' => 'application/zip',
                    ])
                    ->asMultipart()
                    ->post('https://cloud.scorm.com/api/v2/courses/importJobs/upload?' . $queryString, [
                        [
                            'name' => 'file',
                            'contents' => fopen($file, 'r'),  // Ensure the file is opened for reading
                        ],
                    ]);

                if ($response->ok()) {

                    Scorm::create([
                        'course_id' => $course_id,
                        'model_id' => $model_id,
                        'model_type' => $model_type,
                        'title' => $title,
                        'scorm_cloud_course_id' => $scorm_cloud_course_id,
                        'job_id' => $response['result']
                    ]);

                    return [
                        'result' => 'success',
                        'job_id' => $response['result'],
                    ];

                } else {
                    Log::error('Course upload error' . $response);

                    return [
                        'result' => 'error',
                        'message' => $response,
                    ];
                }
            } else {
                return [
                    'result' => 'error'
                ];
            }
        } catch (\Exception $e) {
            Log::error('Upload course error:'. $e);
            throw new \Exception('Failed to retrieve access token');
        }
    }

    public static function getCourseUploadStatus($job_id)
    {
        try {
            $tokenData = self::requestAccessToken(); // Call static method
            
            if(isset($tokenData['access_token'])) {
                $access_token = $tokenData['access_token'];
                
                // Make the POST request with multipart data.
                $response = Http::withHeaders([
                        'Authorization' => "Bearer {$access_token}",
                    ])
                    ->get('https://cloud.scorm.com/api/v2/courses/importJobs/'.$job_id);

                if ($response->ok()) {
                    
                    $scorm = Scorm::where('job_id', $job_id)->first();

                    $scorm->update([
                        "status" => $response['status'], 
                        "data" => json_encode($response)
                    ]);

                    $scorm->save();

                    return [
                        'result' => 'success',
                        'job_status' => $response['status']
                    ];

                } else {
                    Log::error('Course upload error' . $response);

                }
            }  else {
                return [
                    'result' => 'error'
                ];
            }
        } catch (\Exception $e) {
            Log::error('Upload course error:'. $e);
            throw new \Exception('Failed to retrieve access token');
        }
    }

    public static function setCourseTitle($scorm_cloud_course_id, $title)
    {
        $data = [
            'title' => $title, // Use 'title' as the key and assign it the value of $title
        ];
        
        try {

            $tokenData = self::requestAccessToken(); // Call static method
                
            if(isset($tokenData['access_token'])) {
                $access_token = $tokenData['access_token'];
            
                $response = Http::withHeaders([
                        'Authorization' => "Bearer {$access_token}", // Bearer token for authorization
                        'Content-Type' => 'application/json', // Set content type to JSON
                    ])
                    ->put('https://cloud.scorm.com/api/v2/courses/'.$scorm_cloud_course_id.'/title', $data);


                if ($response->status() === 204) {
                    Log::info('Course title updated');

                    return [
                        'result' => 'success',
                        'course_title' => $title
                    ];
                } else {
                    Log::error('Course title update failed'. $response->json());

                    return [
                        'result' => 'error',
                        'message' => $response->json(),
                    ];
                }
            } else {
                return [
                    'result' => 'error'
                ];
            }
        } catch (\Exception $e) {
            Log::error('Course title update failed. Upload course error:'. $e);

            return [
                'result' => 'error',
                'message' => $e,
            ];
        }
    }

    public static function setCourseConfiguration($scorm_cloud_course_id)
    {
        $data = [
            "settings" => [
              [
                "settingId" => "PlayerLaunchType",
                "value" => "FRAMESET",
                "explicit" => false
              ],
              [
                "settingId" => "PlayerScoLaunchType",
                "value" => "FRAMESET",
                "explicit" => false
              ]
            ]
        ];
        
        try {

            $tokenData = self::requestAccessToken(); // Call static method
                
            if(isset($tokenData['access_token'])) {
                $access_token = $tokenData['access_token'];
            
                $response = Http::withHeaders([
                        'Authorization' => "Bearer {$access_token}", // Bearer token for authorization
                        'Content-Type' => 'application/json', // Set content type to JSON
                    ])
                    ->post('https://cloud.scorm.com/api/v2/courses/'.$scorm_cloud_course_id.'/configuration', $data);

                if ($response->status() === 204) {
                    Log::error('Course configuration updated');

                    return [
                        'result' => 'success',
                    ];
                } else {
                    Log::error('Course configuration update failed: '. $response);

                    return [
                        'result' => 'error',
                        'message' => $response
                    ];
                }
            } else {
                return [
                    'result' => 'error'
                ];
            }
        } catch (\Exception $e) {
            Log::error('Course configuration update failed:'. $e);

            return [
                'result' => 'error',
                'message' => $e,
            ];
        }
    }

    public static function createPreviewLink($scorm_cloud_course_id)
    {
        try {
            $tokenData = self::requestAccessToken();

            $data = [
                "expiry" => 0,
            ];
            
            if(isset($tokenData['access_token'])) {
                $access_token = $tokenData['access_token'];
            
                $response = Http::withHeaders([
                        'Authorization' => "Bearer {$access_token}", // Bearer token for authorization
                        'Content-Type' => 'application/json', // Set content type to JSON
                    ])
                    ->post('https://cloud.scorm.com/api/v2/courses/'.$scorm_cloud_course_id.'/preview', $data);

                if ($response->ok()) {
                    Log::info('response preview link' . $response['launchLink']);

                    $scorm = Scorm::where('scorm_cloud_course_id', $scorm_cloud_course_id)->first();

                    $scorm->update([
                        'preview_link' => $response['launchLink']
                    ]);

                    $scorm->save();

                    LearningMaterial::updateOrCreate(
                        [   'learnable_id' => $scorm->model_id, 'learnable_type' =>  $scorm->model_type],
                        [
                            'url' => $response['launchLink'],
                            'file_type' => 'scorm_file',
                        ]
                    );

                    return [
                        'result' => 'success',
                        'preview_link' => $response['launchLink']
                    ];

                } else {
                    Log::error('Course preview link error'. $response);

                    return [
                        'result' => 'error',
                        'message' => $response
                    ];
                }
            } else {
                return [
                    'result' => 'error',
                    'message' => $response
                ];
            }

        } catch (\Exception $e) {
            Log::error('Course preview link creation failed:'. $e);

            return [
                'result' => 'error',
                'message' => $e,
            ];
        }
    }

    public static function getRegistration($user_id, $scorm_cloud_course_id)
    {
        $tokenData = self::requestAccessToken();

        if(isset($tokenData['access_token'])) {
            
            $access_token = $tokenData['access_token'];
        
            $registration_id = $scorm_cloud_course_id.'-'.$user_id;
    
            $response = Http::withToken($access_token)
                ->head('https://cloud.scorm.com/api/v2/registrations/' . $registration_id);
                if ($response->status() === 200) {
                    Log::error('Registration Exists!: ');

                    return [
                        'result' => 'success',
                    ];
                } elseif ($response->status() === 404) {
                    Log::error('Registration does not exist!: ');

                    return [
                        'result' => 'error',
                        'message' => 'Registration does not exist'. $response,
                    ];
                }
        } else {
            return [
                'result' => 'error'
            ];

        }
        
    }

    public static function createRegistration($user_id, $scorm_cloud_course_id)
    {
        $tokenData = self::requestAccessToken();

        if(isset($tokenData['access_token'])) {
            $access_token = $tokenData['access_token'];
            $user = User::find($user_id);
        
            $registration_id = $scorm_cloud_course_id.'-'.$user_id;
            
            $data = [
                "courseId" => $scorm_cloud_course_id,
                "learner" => [
                    "id" => $user->id,
                    "email" => $user->email,
                    "firstName" => $user->first_name,
                    "lastName" => $user->last_name
                ],
                "registrationId" => $registration_id,
                "registrationTags" => ["ENROLLED"],
            ];
                
            if (config('app.env') != 'local') {
                $data['postBack'] = [
                    "url" => 'https://' . StandaloneConfig::apiEndpoint() . '/v1/tenants/course/registration/postback',
                    "resultsFormat" => "full"
                ];
                
                $data['initialSettings'] = [
                    "settings" => [
                        [
                            "settingId" => "PlayerForceObjectiveCompletionSetByContent",
                            "value" => true,
                            "explicit" => false
                        ]
                    ]
                ];
                
            }
    
            $response = Http::withHeaders([
                    'Authorization' => "Bearer {$access_token}",
                    'Content-Type' => 'application/json',
                ])
                ->post('https://cloud.scorm.com/api/v2/registrations', $data);
                
            if ($response->successful()) {
                
                Log::error('Registration Created!: '.$response);

                $scorm = Scorm::where('scorm_cloud_course_id', $scorm_cloud_course_id)->first();

                ScormRegistration::create([
                    'scorm_id' => $scorm->id,
                    'scorm_cloud_course_id' => $scorm_cloud_course_id,
                    'user_id' => $user_id,
                    'registration_id' => $registration_id
                ]);

                return [
                    'result' => 'success',
                ];

            } else {
                Log::error('Registration error!'.$response);

                return [
                    'result' => 'error',
                    'message' => 'Generate registration!'.$response,
                ];
            }
        } else {
            return [
                'result' => 'error'
            ];
        }
        
    }

    public static function deleteRegistrationTags($registration_id)
    {
        $tokenData = self::requestAccessToken();

        if(isset($tokenData['access_token'])) {
            $access_token = $tokenData['access_token'];
        
            $tags = [
                "tags" => [
                  "ENROLLED"
                ]
            ];

            $response = Http::withHeaders([
                    'Authorization' => "Bearer {$access_token}",
                    'Content-Type' => 'application/json',
                ])
                ->delete('https://cloud.scorm.com/api/v2/registrations/'.$registration_id.'/tags', $tags);
                
            if ($response->successful()) {
                
                Log::error('Registration tag deleted!: '.$response);

                return [
                    'result' => 'success',
                ];

            } else {
                Log::error('Registration deletion tag error!'.$response);

                return [
                    'result' => 'error',
                    'message' => 'Registration deletion tag error!'.$response,
                ]; 
            }
        } else {
            return [
                'result' => 'error'
            ];
        }
        
    }

    public static function deleteRegistration($user_id, $scorm_cloud_course_id)
    {
        $tokenData = self::requestDeleteRegistrationAccessToken();

        if(isset($tokenData['access_token'])) {
            $access_token = $tokenData['access_token'];
        
            $registration_id = $scorm_cloud_course_id.'-'.$user_id;
    
            $response = Http::withHeaders([
                    'Authorization' => "Bearer {$access_token}",
                    'Content-Type' => 'application/json',
                ])
                ->delete('https://cloud.scorm.com/api/v2/registrations/'.$registration_id);
                
            if ($response->successful()) {
                
                Log::error('Registration deleted!: '.$response);

                $scorm = Scorm::where('scorm_cloud_course_id', $scorm_cloud_course_id)->first();

                $scorm_registration = ScormRegistration::where('scorm_id', $scorm->id)->where('scorm_cloud_course_id', $scorm_cloud_course_id)->where('user_id', $user_id)->where('registration_id', $registration_id)->first();

                $scorm_registration->scorm_progress()->delete();

                $scorm_registration->delete();

                return [
                    'result' => 'success',
                ];

            } else {
                Log::error('Registration deletion error!'.$response);

                return [
                    'result' => 'error',
                    'message' => 'Registration deletion error!'.$response,
                ];
            }
        } else {
            return [
                'result' => 'error'
            ];
        }
        
    }

    public static function resetRegistrationProgress($user_id, $scorm_cloud_course_id)
    {
        $tokenData = self::requestDeleteRegistrationAccessToken();

        if(isset($tokenData['access_token'])) {
            $access_token = $tokenData['access_token'];
            $user = User::find($user_id);
        
            $registration_id = $scorm_cloud_course_id.'-'.$user_id;
            
            $data = [
                "courseId" => $scorm_cloud_course_id,
                "learner" => [
                    "id" => $user->id,
                    "email" => $user->email,
                    "firstName" => $user->first_name,
                    "lastName" => $user->last_name
                ],
                "registrationId" => $registration_id,
                "registrationTags" => [
                  "enrolled"
                ],
            ];
                
            if (config('app.env') != 'local') {
                $data['postBack'] = [
                    "url" => 'https://' . StandaloneConfig::apiEndpoint() . '/v1/tenants/course/upload/callback',
                    "authType" => "UNDEFINED",
                ];
            }
    
            $response = Http::withHeaders([
                    'Authorization' => "Bearer {$access_token}",
                    'Content-Type' => 'application/json',
                ])
                ->post('https://cloud.scorm.com/api/v2/registrations', $data);
                
            if ($response->successful()) {
                
                Log::error('Registration Created!: '.$response);

                $scorm = Scorm::where('scorm_cloud_course_id', $scorm_cloud_course_id)->first();

                ScormRegistration::create([
                    'scorm_id' => $scorm->id,
                    'scorm_cloud_course_id' => $scorm_cloud_course_id,
                    'user_id' => $user_id,
                    'registration_id' => $registration_id
                ]);

                return [
                    'result' => 'success',
                ];

            } else {
                Log::error('Registration error!'.$response);

                return [
                    'result' => 'error',
                    'message' => 'Generate registration!'.$response,
                ];
            }
        } else {
            return [
                'result' => 'error'
            ];
        }
        
    }

    public static function createLaunchLink($user_id, $scorm_id, $scorm_cloud_course_id, $registration_id)
    {
        $tokenData = self::requestAccessToken();

        if(isset($tokenData['access_token'])) {
            $access_token = $tokenData['access_token'];
            
            $data = [
                "expiry" => 0,
                "redirectOnExitUrl" => "",
                "tracking" => true,
                "launchAuth" => [
                    "type" => "cookies",
                    "options" => [
                        "ipAddress" => true,
                        "fingerprint" => true,
                        "expiry" => 0,
                        "slidingExpiry" => 0
                    ]
                ]
            ];

            $response = Http::withHeaders([
                    'Authorization' => "Bearer {$access_token}",
                    'Content-Type' => 'application/json',
                ])
                ->post('https://cloud.scorm.com/api/v2/registrations/'.$registration_id.'/launchLink', $data);

                
            if ($response->successful()) {
                Log::info('Launch link generated! '.$response['launchLink']);

                ScormRegistration::updateOrCreate([
                    'scorm_id' => $scorm_id,
                    'scorm_cloud_course_id' => $scorm_cloud_course_id,
                    'user_id' => $user_id,
                    'registration_id' => $registration_id
                ], [
                    'launch_link' => $response['launchLink']
                ]);

                return [
                    'result' => 'success',
                ];

            } else {
                Log::error('Generate Launch link error!'.$response);

                return [
                    'result' => 'error',
                    'message' => 'Generate Launch link error!'.$response,
                ];
            }
        } else {

            return [
                'result' => 'error',
                'message' => 'Failed to retrieve access token',
            ];
        }

    }

    public static function deleteCourse($scorm_cloud_course_id)
    {
        
        try {

            $tokenData = self::requestDeleteCourseAccessToken(); // Call static method
                
            if(isset($tokenData['access_token'])) {
                $access_token = $tokenData['access_token'];
            
                $response = Http::withHeaders([
                        'Authorization' => "Bearer {$access_token}", // Bearer token for authorization
                        'Content-Type' => 'application/json', // Set content type to JSON
                    ])
                    ->delete('https://cloud.scorm.com/api/v2/courses/'.$scorm_cloud_course_id);

                if ($response->ok()) {

                    Log::error('Scorm course delete success: '.$response);

                    return [
                        'result' => 'success',
                        'course_title' => $response['title']
                    ];
                } else {
                    Log::error('Scorm course delete failed'. $response);

                    return [
                        'result' => 'error',
                        'message' => $response,
                    ];
                }
            } else {
                return [
                    'result' => 'error'
                ];
            }
        } catch (\Exception $e) {
            Log::error('Scorm course delete failed. Delete course error:'. $e);

            return [
                'result' => 'error',
                'message' => $e,
            ];
        }
    }

    public static function getScormRegistrationProgress($registration_id)
    {
        try {
            $tokenData = self::requestAccessToken(); // Call static method
            
            if(isset($tokenData['access_token'])) {
                $access_token = $tokenData['access_token'];
                
                // Make the POST request with multipart data.
                $response = Http::withHeaders([
                        'Authorization' => "Bearer {$access_token}",
                    ])
                    ->get('https://cloud.scorm.com/api/v2/registrations/'.$registration_id.'?includeInteractionsAndObjectives=true&includeRuntime=true&includeChildResults=true');

                if ($response->ok()) {

                    Log::info('Scorm registration progress' . $response);

                    return [
                        'result' => 'success',
                        'registration_progress' => $response->json()
                    ];

                } else {
                    Log::error('Scorm registration progress error' . $response);

                    return [
                        'result' => 'error',
                        'message' => $response,
                    ];
                }
            } else {
                return [
                    'result' => 'error'
                ];
            }
        } catch (\Exception $e) {
            Log::error('Scorm registration progress error. Failed to retrieve access token '. $e);

            return [
                'result' => 'error',
                'message' => 'Failed to retrieve access token',
            ];
        }
    }

    public static function getWidgetReportageLink($widget_name, $type, $scorm_cloud_course_id, $learner_id = null) {
       # &registrationTags=ENROLLED|_all

        try {
            $scormcloud_app_id = config('scormcloud.app_id');
            
            if ( $widget_name == 'summary/SummaryWidget' && $type == 'course' ) {
                $query_params = '&courseId='.$scorm_cloud_course_id.'&srt=singleCourse&showTitle=true&standalone=true&embedded=true&iframe=true';
            } else if ( $widget_name == 'summary/SummaryWidget' && $type == 'learner' ) {
                $query_params = '&courseId='.$scorm_cloud_course_id.'&learnerId='.$learner_id.'&srt=singleLearnerSingleCourse&embedded=true&showTitle=true&standalone=true&fixedSize=true&iframe=true';
            } else {
                $query_params = '&courseId='.$scorm_cloud_course_id.'&viewall=learners&standalone=true&showTitle=true&embedded=true&iframe=true';
            }

            $report_url = 'https://cloud.scorm.com/Reportage/scormreports/widgets/'. $widget_name .'.php?appId='. $scormcloud_app_id .'' . $query_params;
    
            $result = self::requestReportageAccessToken();
    
            if($result['result'] === 'success') {

                $response = Http::withHeaders([
                        'Authorization' => "Bearer {$result['tokenData']}",
                    ])
                    ->get('https://cloud.scorm.com/api/v2/reporting/reportageLink', [
                        'auth' => $result['queryString'],
                        'reportUrl' => $report_url,
                    ]);

                if ($response->ok()) {
                    Log::error('getWidgetReportageLink' . $response);

                    return [
                        'result' => 'success',
                        'reportageLink' => 'https://cloud.scorm.com' . $response['reportageLink']
                    ];

                } else {
                    Log::error('request reportage access token error' . $response);

                    return [
                        'result' => 'error',
                        'message' => $response,
                    ];
                }
    
            } else {
                Log::error('Get widget reportage link error. Failed to retrieve reportage access token ');

                return [
                    'result' => 'error',
                    'message' => 'Failed to retrieve reportage access token',
                ];
    
            }
        } catch (\Exception $e) {

            Log::error('Get widget reportage link error. Failed to retrieve access token '. $e);

            return [
                'result' => 'error',
                'message' => 'Failed to retrieve access token',
            ];

        }


    }
}