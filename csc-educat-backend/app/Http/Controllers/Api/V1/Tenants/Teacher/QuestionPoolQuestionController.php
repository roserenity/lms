<?php

namespace App\Http\Controllers\Api\V1\Tenants\Teacher;

use App\Http\Controllers\Controller;
use App\Models\QuestionPoolQuestion;

use App\Http\Requests\Tenants\QuestionPoolQuestionRequest;
use App\Models\Image;
use App\Models\QuestionPool;
use App\Models\QuestionPoolQuestionAnswer;
use App\Services\Tenants\ImageService;
use Illuminate\Http\Request;

class QuestionPoolQuestionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.instructor');
    }

    //

    public function store(QuestionPoolQuestionRequest $request) {

        $question_pool = QuestionPool::findOrFail($request->question_pool_id);

        $question_pool->update([
            'name' => $request->name,
            'type_of_question_pool' => $request->type_of_question_pool,
        ]);

        $question = QuestionPoolQuestion::create($request->validated());

        QuestionPoolQuestionAnswer::create([
            'question_pool_question_id' => $question->id,
            'choices' => $request->choices,
            'correct_answer' => $request->correct_answer,
        ]);

        return $this->sendResponse($question, 'You have successfully added a new question.', 201);
        
    }

    public function update(QuestionPoolQuestionRequest $request, $id)
    {
        
        $question_pool = QuestionPool::findOrFail($request->question_pool_id);

        $question_pool->update([
            'name' => $request->name,
            'type_of_question_pool' => $request->type_of_question_pool,
        ]);

        $question = QuestionPoolQuestion::find($id);

        if (! $question) {
          return $this->sendResponse([], 'No question found.', 404);
        }
    
        $question->update($request->safe()->only(['question','type_of_question','score','require_file_attachment','require_support_answer', 'rubric_id', 'is_require_to_answer', 'data']));
    
        $question->answer()->update(['choices' => json_encode($request->choices), 'correct_answer' => $request->correct_answer]);
    

        return $this->sendResponse($question, 'You have successfully updated a question.', 201);
    }

    public function destroy($id)
    {
        $question = QuestionPoolQuestion::find($id);
        
        if (!$question) {
            return $this->sendResponse([], 'No question found.', 404);
        }

        $question->delete();

        return $this->sendResponse([], 'You have successfully deleted a question.', 200);
    }

    public function uploadQuestionImage(Request $request, $question_id)
    {
        $request->validate([
            'image' => 'nullable|mimes:png,jpg,jpeg'
        ]);

        $question = QuestionPoolQuestion::find($question_id);

        if (!$question) {
            return $this->sendResponse([], 'No question pool question found.', 404);
        }

        if ($request->hasFile('image')) {
            ImageService::uploadQuestionPoolQuestionImage([
                'model_id' => $question_id,
                'model_type' => 'App\\Models\\QuestionPoolQuestion',
                'image' => $request->file('image')
            ]);
        }
    }

    public function getQuestionImages($question_id)
    {
        $question = QuestionPoolQuestion::where('id', $question_id);

        if (! $question) {
            return $this->sendResponse([], 'No question pool question found.', 404);
        }

        return $this->sendResponse($question->with('image')->get(), 'Question images.', 200);
    }

    public function deleteQuestionImage($question_id)
    {
        Image::find($question_id)->delete();

        return $this->sendResponse([], 'Question image deleted.', 200);
    }

}
