<?php

namespace App\Http\Controllers\Api\V1\Tenants\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenants\Admin\BannedWordRequest;
use App\Models\BannedWord;

class BannedWordController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth.admin');
    }

    public function index()
    {
        if(request('paginate')) {
            $banned_words = BannedWord::orderBy('created_at', 'ASC')->paginate(request('paginate'));
        } else {
           $banned_words = BannedWord::orderBy('created_at', 'ASC')->get();
        }

        return $this->sendResponse($banned_words, 'List of banned words', 200);
    }

    public function store(BannedWordRequest $request)
    {
        $banned_word = BannedWord::create($request->validated());

        return $this->sendResponse($banned_word, 'List of banned words', 200);
    }

    public function update(Request $request, $id)
    {
        $banned_word = BannedWord::find($id);

        if (!$banned_word) {
            return $this->sendResponse([], 'No banned word found.', 404);
        }

        $banned_word->update(['word' => $request->word]);

        return $this->sendResponse([], 'You have successfully updated banned word.', 200);
    }

    public function destroy($id)
    {
        $banned_word = BannedWord::find($id);

        if (!$banned_word) {
            return $this->sendResponse([], 'No banned_word found.', 404);
        }

        $banned_word->delete();

        return $this->sendResponse([], 'You have successfully deleted banned word.', 200);
    }

    public function updateBannedWords(Request $request)
    {
        
        foreach($request->words as $banned_word) {
            BannedWord::updateOrCreate([
                'id' => $banned_word['id'],
            ],[
                'word' =>$banned_word['word']
            ]);
        }

        return $this->sendResponse([], 'You have successfully updated banned words.', 200);
    }
}
