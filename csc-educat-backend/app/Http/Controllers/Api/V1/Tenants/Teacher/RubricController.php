<?php

namespace App\Http\Controllers\Api\V1\Tenants\Teacher;

use App\Http\Controllers\Controller;

use App\Http\Requests\Tenants\RubricRequest;
use App\Models\CriteriaIndicator;
use App\Models\Rubric;
use App\Models\RubricCriteria;
use App\Models\RubricRating;
use App\Models\ShareRubric;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class RubricController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth.instructor');
  }

  public function index()
  {
    if(request('paginate')) {
      $rubrics = Rubric::when(request('search'), function($query){
        $query->where('title', 'LIKE', '%' . request('search') . '%');
      })
      ->where('uploader_id', $this->authUser()->id)
      ->latest()
      ->paginate(request('paginate'));
    } else {
      $rubrics = Rubric::where('uploader_id', $this->authUser()->id)->get();
    }

    return $this->sendResponse($rubrics, 'List of all my rubrics.', 200);
  }

  public function destroy($rubric_id)
  {
    $rubric = Rubric::findOrFail($rubric_id);
    $rubric->delete();
    $rubric->criterias()->delete();
    $rubric->ratings()->delete();
    $rubric->shared_rubrics()->delete();
    return $this->sendResponse([], 'You have successfully deleted a rubric.', 200);
  }

  public function deleteRubricCriteria($rubric_criteria_id)
  {
    $rubric_criteria = RubricCriteria::findOrFail($rubric_criteria_id);
    $rubric_criteria->delete();
    $rubric_criteria->indicators()->delete();
    return $this->sendResponse([], 'You have successfully deleted a rubric criteria.', 200);
  }

  public function deleteRubricRatings($id)
  {
    $rubric_rating = RubricRating::findOrFail($id);
    $rubric_rating->delete();
    $rubric_rating->indicators()->delete();
    return $this->sendResponse([], 'You have successfully deleted a rubric ratings.', 200);
  }

  public function store(RubricRequest $request)
  {
    $rubric = Rubric::create($request->safe()->only([
      'title',
      'type_of_rubric',
      'description',
      'uploader_id'
    ]));

    foreach ($request->ratings as $value) {
      RubricRating::create(['rubric_id' => $rubric->id,'rate' => $value['rate']]);
    }

    foreach ($request->criterias as $value) {
      $criteria = RubricCriteria::create([
        'rubric_id' => $rubric->id,
        'title' => $value['title'],
        'score' => $value['score']
      ]);
        $ratings = $rubric->ratings->pluck('id');
        foreach ($value['indicators'] as $key => $indicator) {
          CriteriaIndicator::create([
            'rubric_criteria_id' => $criteria->id,
            'rubric_rating_id' => $ratings[$key],
            'body' => $indicator['body'],
            // 'sort' => $ key
          ]);
        }
    }
    return $this->sendResponse([], 'You have successfully added a rubric.', 201);
  }

  public function show($id)
  {
    $rubric = Rubric::where('id', $id);

    if (! $rubric) {
      return $this->sendResponse([], 'No rubric found.', 404);
    }

    return $this->sendResponse($rubric->with(['criterias','ratings'])->first(), 'Rubric details.', 200);
  }

  public function update(RubricRequest $request, $id)
  {
    $rubric = Rubric::find($id);

    if (! $rubric) {
      return $this->sendResponse([], 'No rubric found.', 404);
    }

    $rubric->update([
      'title' => $request->title,
      'description' => $request->description
    ]);

    return $this->sendResponse([], 'You have successfully updated a rubric.', 200);
  }

  public function updateCriteria(Request $request, $id)
  {

    $criteria = RubricCriteria::find($id);

    if (! $criteria) {
      return $this->sendResponse([], 'No criteria found.', 404);
    }

    $criteria->update([
      'title' => $request->title,
      'score' => $request->score
    ]);

    return $this->sendResponse([], 'You have successfully updated a criteria.', 200);
  }

  public function updateIndicator(Request $request, $id)
  {

    $indicator = CriteriaIndicator::find($id);

    if (! $indicator) {
      return $this->sendResponse([], 'No indicator found.', 404);
    }

    $indicator->update(['body' => $request->body,]);

    return $this->sendResponse([], 'You have successfully updated a indicator.', 200);
  }

  public function addCriteria($id)
  {
    $rubric = Rubric::find($id);

    $ratings = $rubric->ratings->pluck('id');

    $criteria = RubricCriteria::create([
      'rubric_id' => $rubric->id,
      'title' => 'New Criterion',
      'score' => 0
    ]);

    foreach ($ratings as $rating) {
      CriteriaIndicator::create([
        'rubric_criteria_id' => $criteria->id,
        'rubric_rating_id' => $rating,
        'body' => 'New indicator for criterion'
      ]);
    }

    return $this->sendResponse([], 'You have successfully added a criteria.', 200);
  }

  public function updateRating(Request $request, $id)
  {

    $rating = RubricRating::find($id);

    if (! $rating) {
      return $this->sendResponse([], 'No rating found.', 404);
    }

    $rating->update(['rate' => $request->rate,]);

    return $this->sendResponse([], 'You have successfully updated a ratings.', 200);
  }

  public function addRating($id)
  {
    $rubric = Rubric::find($id);

    $rating = RubricRating::create([
      'rubric_id' => $rubric->id,
      'rate' => $rubric->ratings->max('rate') + 1,
    ]);

    $criterias = $rubric->criterias->pluck('id');

    foreach($criterias as $criteria) {
      CriteriaIndicator::create([
          'rubric_criteria_id' => $criteria,
          'rubric_rating_id' => $rating->id,
          'body' => 'New indicator for criterion'
        ]);
    }

    return $this->sendResponse([], 'You have successfully added a rating.', 200);
  }

  public function shareRubric(Request $request, $rubric_id)
  {
    $request->validate([
      'shared_to_user_id' => 'required|exists:users,id',
    ]);

    $rubric = Rubric::find($rubric_id);

    if (! $rubric) {
      return $this->sendResponse([], 'No rubric found.', 404);
    }

    $shared_rubric_exist = ShareRubric::where('rubric_id', $rubric_id)
    ->where('shared_by_user_id', $this->authUser()->id)
    ->where('shared_to_user_id', $request->shared_to_user_id)
    ->first();

    if ($shared_rubric_exist) {
      return $this->sendResponse([], 'You already shared the rubric to this user.', 200);
    }

    ShareRubric::create([
      'rubric_id' => $rubric_id,
      'shared_by_user_id' => $this->authUser()->id,
      'shared_to_user_id' => $request->shared_to_user_id
    ]);

    return $this->sendResponse([], 'You successfully shared a rubric.', 201);
  }

  public function showSharedWithMeRubrics()
  {
    if(request('paginate')) {
      $shared_rubrics = ShareRubric::
        with(['rubric','shared_by_user'])
        ->when(request('search'), function ($query) {
          $query->whereHas('rubric', function ($innerQuery) {
            $innerQuery->where('title', 'LIKE', '%' . request('search') . '%');
          });
        })
        ->where('shared_to_user_id', $this->authUser()->id)
        ->where('status', 'pending')
        ->whereHas('shared_by_user')
        ->paginate(request('paginate'));
    } else {
      $shared_rubrics = ShareRubric::with(['rubric','shared_by_user'])
        ->where('shared_to_user_id', $this->authUser()->id)
        ->whereHas('shared_by_user')
        ->where('status', 'pending')->get();
    }

    return $this->sendResponse($shared_rubrics, 'List of rubrics shared with me.', 200);
  }

  public function showSharedToOtherRubrics()
  { 
    if(request('paginate')) {
      $shared_rubrics = Rubric::
        when(request('search'), function($query){
          $query->where('title', 'LIKE', '%' . request('search') . '%');
        })
        ->whereHas('shared_rubrics', function(Builder $query) {
          $query->where('shared_by_user_id', $this->authUser()->id);
        })
        ->with([
          'shared_rubrics' => function($shared_rubrics) {
            $shared_rubrics->with('shared_to_user')->where('shared_by_user_id', $this->authUser()->id)->whereHas('shared_to_user');
          }
        ])
        ->where('uploader_id', $this->authUser()->id)
        ->paginate(request('paginate'));
    } else {
      $shared_rubrics = Rubric::whereHas('shared_rubrics', function(Builder $query) {
        $query->where('shared_by_user_id', $this->authUser()->id);
      })
      ->with([
        'shared_rubrics' => function($shared_rubrics) {
          $shared_rubrics->with('shared_to_user')->where('shared_by_user_id', $this->authUser()->id)->whereHas('shared_to_user');
        }
      ])
      ->where('uploader_id', $this->authUser()->id)
      ->get();
    }

    return $this->sendResponse($shared_rubrics, 'List of rubrics you shared to others.', 200);
  }

  public function showShareToInstructors()
  {
    $instructors = User::select(['id', 'role', 'first_name', 'last_name', 'suffix', 'email'])->whereIn('role', [User::ROLE_INSTRUCTOR, User::ROLE_ADMIN])->get()->except($this->authUser()->id);

    return $this->sendResponse($instructors, 'List of all instructors to share rubric.', 200);
  }

  public function moveSharedWithMeRubric(Request $request)
  {
    $request->validate([
      'rubric_id' => 'required|exists:rubrics,id',
      'description' => 'required',
      'title' => 'required|max:255',
    ]);

    $rubric = Rubric::find($request->rubric_id);

    if (! $rubric) {
      return $this->sendResponse([], 'No rubric found.', 404);
    }

    $new_rubric = $rubric->replicate([
      'uploader_id'
    ])
    ->fill([
      'title' => $request->title,
      'description' => $request->description,
      'uploader_id' => $this->authUser()->id
    ]);

    $new_rubric->save();

    foreach ($rubric->criterias as $key => $criteria) {
      $new_rubric->criterias()->create($criteria->toArray());
    }

    foreach ($rubric->ratings as $key => $rating) {
      $new_rubric->ratings()->create($rating->toArray());
    }

    $shared_rubric = ShareRubric::where('rubric_id', $request->rubric_id)
    ->where('shared_to_user_id', $this->authUser()->id)
    ->update(['status' => 'accepted']);

    return $this->sendResponse($new_rubric->id, 'This rubric has been moved.', 201);
  }

}
