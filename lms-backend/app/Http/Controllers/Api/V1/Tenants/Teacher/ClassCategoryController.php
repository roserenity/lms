<?php

namespace App\Http\Controllers\Api\V1\Tenants\Teacher;

use App\Http\Controllers\Controller;

use App\Models\ClassCategory;

class ClassCategoryController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth.instructor');
  }

  public function index()
  {
    $class_categories = ClassCategory::all();

    return $this->sendResponse($class_categories, 'List of all class category.', 200);
  }
}
