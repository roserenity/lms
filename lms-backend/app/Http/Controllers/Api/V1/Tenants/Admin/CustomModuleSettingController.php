<?php

namespace App\Http\Controllers\Api\V1\Tenants\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\CustomModuleField;

use App\Http\Requests\Tenants\CustomModuleFieldsRequest;

class CustomModuleSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.admin');
    }

    public function getCustomModuleFields()
    {
        $custom_module_field = CustomModuleField::all();

        return $this->sendResponse($custom_module_field, 'List of all custom module setting.', 200);
    }

    public function addCustomModuleField(CustomModuleFieldsRequest $request)
    {
        $custom_module_field = CustomModuleField::create($request->validated());

        return $this->sendResponse($custom_module_field, 'You have successfully created a new module field.', 201);
    }

    public function updateCustomModuleField($id, CustomModuleFieldsRequest $request)
    {
        CustomModuleField::updateOrCreate(
            ['id' => $request->id],
            $request->validated()
        );

        return $this->sendResponse([], 'You have successfully updated a new module field.', 200);
    }

    public function deleteCustomModuleField($id)
    {
        $custom_module_field = CustomModuleField::find($id);
        $custom_module_field->delete();

        return $this->sendResponse([], 'Module field deleted.', 200);

    }

}
