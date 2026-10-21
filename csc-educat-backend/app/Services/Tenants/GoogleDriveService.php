<?php

namespace App\Services\Tenants;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Google\Service\Docs;
use Google\Service\Sheets;
use Google\Service\Slides;
use Log;
use Storage;

class GoogleDriveService
{
    protected $client;
    protected $driveService;
    protected $docsService;
    protected $sheetsService;
    protected $slidesService;

    public function __construct()
    {

        try {
            $this->client = new \Google_Client();

            if (config('app.env') == 'prod') {
                $json = Storage::disk('s3')->get(env('GOOGLE_DRIVE_SERVICE_ACCOUNT_KEY_PATH'));
                $this->client->setAuthConfig(json_decode($json, true));                
            } else {
                $path_to_key = storage_path('key/google-service-account.json');
                $this->client->setAuthConfig($path_to_key);
            }
            
            $this->client->addScope(Drive::DRIVE); // Full drive access
            $this->client->setAccessType('offline');
    
            $this->driveService = new Drive($this->client);
            $this->docsService = new Docs($this->client);
            $this->sheetsService = new Sheets($this->client);
            $this->slidesService = new Slides($this->client);

            Log::info('Google Drive service initialized.');

        } catch (\Exception $e) {
            Log::info('error '.$e);
        }

    }

    // False when no service account key was found, e.g. in local Docker setups.
    public function isConfigured()
    {
        return $this->driveService !== null;
    }

    public function createFolderHierarchy($tenantName, $courseName, $activityName, $email = null)
    {
        $tenantFolderId = $this->createFolder($tenantName, null, $email);

        $courseFolderId = $this->createFolder($courseName, $tenantFolderId, $email);

        $activityFolderId = $this->createFolder($activityName, $courseFolderId, $email);

        return [
            'tenant_folder_id' => $tenantFolderId,
            'course_folder_id' => $courseFolderId,
            'activity_folder_id' => $activityFolderId,
        ];
    }


    public function createFolder($name, $parentId = null, $email = null)
    {

        $safeName = str_replace("'", "\\'", $name);

        $query = sprintf(
            "name = '%s' and mimeType = 'application/vnd.google-apps.folder'%s",
            $safeName,
            $parentId ? " and '{$parentId}' in parents" : ''
        );

        $files = $this->driveService->files->listFiles([
            'q' => $query,
            'fields' => 'files(id, name)',
        ]);

        if (count($files->files) > 0) {
            $folder = $files->files[0];
            Log::info("Found existing folder: {$name} (ID: {$folder->id})");
            return $folder->id;
        }

        $fileMetadata = new \Google\Service\Drive\DriveFile([
            'name' => $name,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents' => $parentId ? [$parentId] : [],
        ]);

        $folder = $this->driveService->files->create($fileMetadata, [
            'fields' => 'id',
        ]);

        Log::info("Created folder: {$name} (ID: {$folder->id})");

        if ($email) {
            $this->shareFolder($folder->id, $email);
            Log::info("Shared folder '{$name}' with {$email}");
        }

        return $folder->id;
    }



    public function shareFolder($folderId, $email)
    {
        // Step 3: Set permissions to share the folder
        $permission = new \Google\Service\Drive\Permission([
            'type' => 'user', // Can be 'user', 'group', 'domain', or 'anyone'
            'role' => 'writer', // 'reader', 'writer', or 'owner'
            'emailAddress' => $email, // The email address to share with
        ]);

        // Apply the permissions to the folder
        $this->driveService->permissions->create($folderId, $permission);
    }

    public function deleteAllFolders()
    {
        try {
            $files = $this->driveService->files->listFiles([
                'q' => "mimeType = 'application/vnd.google-apps.folder'",
                'fields' => 'files(id)',
            ]);

            foreach ($files->files as $folder) {
                $this->driveService->files->delete($folder->id);
                echo "Folder with ID {$folder->id} deleted successfully!<br>";
            }

            echo "All folders deleted successfully!";
        } catch (\Google\Service\Exception $e) {
            echo 'Error deleting folders: ', $e->getMessage();
        }
    }

    public function deleteFolder($folderId)
    {
        try {
            $this->driveService->files->delete($folderId);
            echo "Folder deleted successfully!";
        } catch (\Google\Service\Exception $e) {
            echo 'Error deleting folder: ', $e->getMessage();
        }
    }

    public function deleteFile($fileId)
        {
            if (!$this->isConfigured()) {
                return;
            }

            try {
                $this->driveService->files->delete($fileId);
                Log::info("File with ID {$fileId} deleted successfully.");
            } catch (\Google\Service\Exception $e) {
                Log::info('Error deleting file: ' . $e->getMessage());
            }
        }


    public function getGoogleDocById($documentId)
    {
        try {
            $file = $this->driveService->files->get($documentId, [
                'fields' => 'id, name'
            ]);

            return [
                'id' => $file->getId(),
                'link' => 'https://docs.google.com/document/d/' . $file->getId(),
            ];
            
        } catch (\Google\Service\Exception $e) {
            Log::info('Error fetching Google Doc: ' . $e->getMessage());
            return null;
        }
    }

    public function createGoogleDoc($folderId = null, $docName="New Google Doc", $documentId=null, $shareWithEmail = null)
    {
        Log::info('createGoogleDoc');
        try {

            if($documentId) {
                $document = $this->getGoogleDocById($documentId);
                
                if ($document) {
                    return $document;        
                }
                
            }
            
            $fileMetadata = new \Google\Service\Drive\DriveFile([
                'name' => $docName,
                'mimeType' => 'application/vnd.google-apps.document',
            ]);

            if ($folderId) {
                $fileMetadata->setParents([$folderId]);
            }
            
            $file = $this->driveService->files->create($fileMetadata, [
                'fields' => 'id'
            ]);

            $document_id = $file->id;

            if ($shareWithEmail) {
                $permission = new \Google\Service\Drive\Permission([
                    'type' => 'user',
                    'role' => 'writer',
                    'emailAddress' => $shareWithEmail,
                ]);

                $this->driveService->permissions->create($document_id, $permission, ['sendNotificationEmail' => false]);
            } else {
                $permission = new \Google\Service\Drive\Permission([
                    'type' => 'anyone',
                    'role' => 'writer',
                ]);
                $this->driveService->permissions->create($document_id, $permission, ['sendNotificationEmail' => false]);
            }

            Log::info([
                'id' => $document_id,
                'link' => 'https://docs.google.com/document/d/' . $document_id,
            ]);

            return [
                'id' => $document_id,
                'link' => 'https://docs.google.com/document/d/' . $document_id,
            ];

        } catch (\Google\Service\Exception $e) {
            Log::info('Error creating Google Doc: '. $e->getMessage());
        }
    }

    public function getGoogleSheetById($sheetId)
    {
        try {
            $file = $this->driveService->files->get($sheetId, [
                'fields' => 'id'
            ]);

            return [
                'id' => $file->getId(),
                'link' => 'https://docs.google.com/spreadsheets/d/' . $file->getId(),
            ];
            
        } catch (\Google\Service\Exception $e) {
            Log::info('Error fetching Google Sheet: ' . $e->getMessage());
            return null;
        }
    }

    public function createGoogleSheet($folderId = null, $sheetName="New Google Sheet", $documentId=null, $shareWithEmail = null)
    {
        Log::info('createGoogleSheet');

        try {

            if($documentId) {
                $document = $this->getGoogleSheetById($documentId);
                
                if ($document) {
                    return $document;        
                }
                
            }
            
            $fileMetadata = new \Google\Service\Drive\DriveFile([
                'name' => $sheetName,
                'mimeType' => 'application/vnd.google-apps.spreadsheet',
            ]);

            if ($folderId) {
                $fileMetadata->setParents([$folderId]);
            }

            $file = $this->driveService->files->create($fileMetadata, [
                'fields' => 'id'
            ]);

            $spreadsheet_id = $file->id;


            $body = new \Google\Service\Sheets\ValueRange([
                'values' => []
            ]);

            $this->sheetsService->spreadsheets_values->update(
                $spreadsheet_id,
                'Sheet1!A1',
                $body,
                ['valueInputOption' => 'RAW']
            );

            Log::info('Sheets Service:', [(string) get_class($this->sheetsService)]);

            if ($shareWithEmail) {
                $permission = new \Google\Service\Drive\Permission([
                    'type' => 'user',
                    'role' => 'writer',
                    'emailAddress' => $shareWithEmail,
                ]);

                $this->driveService->permissions->create(
                    $spreadsheet_id,
                    $permission,
                    ['sendNotificationEmail' => false]
                );
            } else {
                $permission = new \Google\Service\Drive\Permission([
                    'type' => 'anyone',
                    'role' => 'writer',
                ]);

                $this->driveService->permissions->create(
                    $spreadsheet_id,
                    $permission,
                    ['sendNotificationEmail' => false]
                );
            }

            Log::info([
                'id' => $spreadsheet_id,
                'link' => 'https://docs.google.com/spreadsheets/d/' . $spreadsheet_id,
            ]);

            return [
                'id' => $spreadsheet_id,
                'link' => 'https://docs.google.com/spreadsheets/d/' . $spreadsheet_id,
            ];

        } catch (\Google\Service\Exception $e) {
            Log::info('Error creating Google Sheet: ' . $e->getMessage());
        }
    }

    public function getGoogleSlideById($sheetId)
    {
        try {
            $file = $this->driveService->files->get($sheetId, [
                'fields' => 'id'
            ]);

            return [
                'id' => $file->getId(),
                'link' => 'https://docs.google.com/presentation/d/' . $file->getId(),
            ];
            
        } catch (\Google\Service\Exception $e) {
            Log::info('Error fetching Google Slide: ' . $e->getMessage());
            return null;
        }
    }

    public function createGoogleSlide($folderId = null, $slideName="New Google Slide", $documentId=null, $shareWithEmail = null)
    {
        try {

            if($documentId) {
                $document = $this->getGoogleSlideById($documentId);
                
                if ($document) {
                    return $document;        
                }
                
            }

            $fileMetadata = new \Google\Service\Drive\DriveFile([
                'name' => $slideName,
                'mimeType' => 'application/vnd.google-apps.presentation',
            ]);

            if ($folderId) {
                $fileMetadata->setParents([$folderId]);
            }

            $file = $this->driveService->files->create($fileMetadata, [
                'fields' => 'id'
            ]);

            $presentation_id = $file->id;

            if ($shareWithEmail) {
                $permission = new \Google\Service\Drive\Permission([
                    'type' => 'user',
                    'role' => 'writer',
                    'emailAddress' => $shareWithEmail,
                ]);

                $this->driveService->permissions->create(
                    $presentation_id,
                    $permission,
                    ['sendNotificationEmail' => false]
                );
            } else {
                $permission = new \Google\Service\Drive\Permission([
                    'type' => 'anyone',
                    'role' => 'writer',
                ]);
                
                $this->driveService->permissions->create(
                    $presentation_id,
                    $permission,
                    ['sendNotificationEmail' => false]
                );
            }

            Log::info([
                'id' => $presentation_id,
                'link' => 'https://docs.google.com/presentation/d/' . $presentation_id,
            ]);

            return [
                'id' => $presentation_id,
                'link' => 'https://docs.google.com/presentation/d/' . $presentation_id,
            ];

        } catch (\Google\Service\Exception $e) {
            Log::info('Error creating Google Slides: ' . $e->getMessage());
        }
    }


}
