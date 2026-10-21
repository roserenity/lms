<?php

namespace App\Http\Controllers\Api\V1\Tenants\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Requests\Tenants\Admin\RegistrationFieldRequest;
use App\Http\Requests\Tenants\Admin\RegistrationPageRequest;
use App\Jobs\Tenants\SendProfileFillOutNotification;
use App\Helpers\StandaloneConfig;
use App\Models\CustomRegistrationPage;
use App\Models\CustomRegistrationField;
use App\Models\User;
use App\Notifications\ProfileFilloutNotification;
use Log;

use Illuminate\Support\Facades\Notification;

class CustomRegistrationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.admin');
    }

    public function index()
    {
        $registration_page = CustomRegistrationPage::all();

        return $this->sendResponse($registration_page, 'List of all registration page.', 200);
    }

    public function createRegistrationPage(RegistrationPageRequest $request)
    {
        $registration_page = CustomRegistrationPage::create($request->validated());

        return $this->sendResponse($registration_page, 'You have successfully added a new registration page.', 201);
    }

    public function updateRegistrationPage($custom_registration_page_id, RegistrationPageRequest $request)
    {
        $custom_registration_page = CustomRegistrationPage::find($custom_registration_page_id);

        if (!$custom_registration_page) {
            return $this->sendResponse([], 'No custom registration page found.', 404);
        }

        $custom_registration_page->update($request->safe()->except('sort_no'));

        return $this->sendResponse($custom_registration_page, 'Custom registration page has been updated.', 200);
    }

    public function deleteRegistrationPage($custom_registration_page_id)
    {
        $custom_registration_page = CustomRegistrationPage::find($custom_registration_page_id);

        if (!$custom_registration_page) {
            return $this->sendResponse([], 'No custom registration page found.', 404);
        }

        $custom_registration_page->delete();

        $custom_registration_page->custom_registration_fields()->delete();

        return $this->sendResponse([], 'Custom registration field has been deleted.', 200);
    }

    public function sortRegistrationPage(Request $request)
    {
        $request->validate([
            'custom_registration_page_id' => 'required|array'
        ]);

        foreach ($request->custom_registration_page_id as $key => $id) {
            CustomRegistrationPage::where('id', $id)->update(['sort_no' => $key]);
        }

        return $this->sendResponse([], 'You have successfully sort registration page.', 200);
    }

    public function createRegistrationField(RegistrationFieldRequest $request)
    {
        $custom_field = CustomRegistrationField::create($request->validated());
        
        SendProfileFillOutNotification::dispatch(StandaloneConfig::tenantId());

        return $this->sendResponse($custom_field, 'You have successfully added a new custom registration field.', 201);
    }

    public function updateRegistrationField($custom_registration_field_id, RegistrationFieldRequest $request)
    {
        $custom_registration_field = CustomRegistrationField::find($custom_registration_field_id);

        if (!$custom_registration_field) {
            return $this->sendResponse([], 'No custom registration field found.', 404);
        }

        $custom_registration_field->update($request->safe()->except([
            'custom_registration_page_id',
            'sort_no'
        ]));
        
        SendProfileFillOutNotification::dispatch(StandaloneConfig::tenantId());

        return $this->sendResponse($custom_registration_field, 'Custom registration field has been updated.', 200);
    }

    public function deleteRegistrationField($custom_registration_field_id)
    {
        $custom_registration_field = CustomRegistrationField::find($custom_registration_field_id);

        if (!$custom_registration_field) {
            return $this->sendResponse([], 'No custom registration field found.', 404);
        }

        $custom_registration_field->delete();

        return $this->sendResponse([], 'Custom registration field has been deleted.', 200);
    }

    public function sortRegistrationField(Request $request)
    {
        $request->validate([
            'custom_registration_field_id' => 'required|array'
        ]);

        foreach ($request->custom_registration_field_id as $key => $id) {
            CustomRegistrationField::where('id', $id)->update(['sort_no' => $key]);
        }

        return $this->sendResponse([], 'You have successfully sort registration field.', 200);
    }
}
