<?php

namespace App\Http\Controllers\Api\V1\Tenants\Admin;

use App\Http\Controllers\Controller;

use App\Http\Requests\Tenants\ClassCategoryRequest;

use Illuminate\Database\Eloquent\Builder;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\ClassCategory;

class ClassCategoryController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth.admin');
  }

  public function index(Request $request)
  {
    if($request->paginate) {
      $query = ClassCategory::with([
        'courses' => function ($courses) {
          return $courses->with('uploader');
        }
      ])
      ->when(!empty(request('search')), function ($query) {
        $query->where('class_code', 'LIKE', '%' . request('search'). '%')
          ->orWhere('class_name', 'LIKE', '%' . request('search'). '%');
      });
  
      $class_categories = $query->paginate($request->paginate);
    } else {
      $class_categories = ClassCategory::with([
        'courses' => function ($courses) {
          return $courses->with('uploader');
        }
      ])
      ->get();
    }

    foreach ($class_categories as $class_category) {
      foreach($class_category->courses as $course){
        $class_category->registered_users = Enrollment::where('course_id', $course->id)->count();
      }
    }

    return $this->sendResponse($class_categories, 'List of all class category.', 200);
  }

  public function create()
  {
    //
  }

  public function store(ClassCategoryRequest $request)
  {
    $class_category = ClassCategory::create($request->validated());

    return $this->sendResponse($class_category, 'You have successfully added a new class.', 201);
  }

  public function show($id)
  {
    //
  }

  public function edit($id)
  {
    //
  }

  public function update(ClassCategoryRequest $request, $id)
  {
    $class_category = ClassCategory::find($id);

    if (!$class_category) {
      return $this->sendResponse([], 'No class found.', 404);
    }

    $class_category->update($request->validated());

    return $this->sendResponse($class_category, 'Class category has been updated.', 200);
  }

  public function destroy($id)
  {
    $class_category = ClassCategory::find($id);
    $class_category->delete();
    $class_category->courses()->detach();

    return $this->sendResponse([], 'Class category deleted.', 200);
  }
}
