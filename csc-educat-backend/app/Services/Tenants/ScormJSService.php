<?php

namespace App\Services\Tenants;

use Illuminate\Support\Facades\Storage;

class ScormJSService
{
    public static function uploadIframeJs($path)
    {
        $targetUrl = config('app.target_url');
        $jsContent = <<<JS

            window.INTERACTION_RESULT_CORRECT = 'correct';
            window.INTERACTION_RESULT_WRONG = 'wrong';
            window.LESSON_STATUS_PASSED = 'passed';

            function sendToParent(message) {
                window.parent.postMessage(message, '{$targetUrl}');
            }

            window.addEventListener("message", (event) => {

                if (event.origin !== '{$targetUrl}') return;

                if(event.data.user_id) {
                    window.localStorage.setItem('user_id', event.data.user_id)
                }

                if(event.data.course_id) {
                    window.localStorage.setItem('course_id', event.data.course_id)
                }
                
                if(event.data.type) {
                    window.localStorage.setItem('type', event.data.type)
                }
                
                if(event.data.model_id) {
                    window.localStorage.setItem('model_id', event.data.model_id)
                }
                
                if(event.data.scorm_course_progress) {
                    window.localStorage.setItem('scorm_course_progress', event.data.scorm_course_progress)
                }
                console.log('from top: ', event)
            });

            function CommitData() {
                sendToParent('CommitData');
            }

            function ConcedeControl() {
                sendToParent('ConcedeControl');
            }

            function CreateResponseIdentifier() {
                sendToParent('CreateResponseIdentifier');
            }

            function Finish() {
                sendToParent('Finish');
            }

            function GetDataChunk() {
                sendToParent('GetDataChunk');
            }

            function GetStatus() {
                sendToParent('GetStatus');
            }

            function MatchingResponse() {
                sendToParent('MatchingResponse');
            }

            function RecordFillInInteraction() {
                sendToParent('RecordFillInInteraction');
            }

            function RecordMatchingInteraction() {
                sendToParent('RecordMatchingInteraction');
            }

            function RecordMultipleChoiceInteraction() {
                sendToParent('RecordMultipleChoiceInteraction');
            }

            function ResetStatus() {
                sendToParent('ResetStatus');
            }

            function SetBookmark() {
                sendToParent('SetBookmark');
            }

            function SetDataChunk(data) {
                sendToParent('SetDataChunk');
            }

            function SetFailed() {
                sendToParent('SetFailed');
            }

            function SetPassed() {
                sendToParent('SetPassed');
            }

            function SetReachedEnd() {
                sendToParent('SetReachedEnd');
            }

            function SetScore() {
                sendToParent('SetScore');
            }

            function WriteToDebug() {
                sendToParent('WriteToDebug');
            }
            JS;

            $filename = $path . 'LMSscript.js';

            if (config('app.env') == 'local') {
                Storage::disk('public')->put($filename, $jsContent);
            } else {
                Storage::disk('s3')->put($filename, $jsContent);
            }

        return $path;
    }
}