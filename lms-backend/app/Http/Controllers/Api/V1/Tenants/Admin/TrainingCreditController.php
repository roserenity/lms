<?php

namespace App\Http\Controllers\Api\V1\Tenants\Admin;

use App\Http\Controllers\Controller;

use App\Models\TrainingCredit;
use Illuminate\Http\Request;

class TrainingCreditController extends Controller
{
    public function __construct()
    {
      $this->middleware('auth.admin');
    }
  
    public function index()
    {

      $training_credits = TrainingCredit::
          when(request('search'), function ($query) {
            $query->where('name', 'LIKE', '%' . request('search') . '%');
          })->paginate(request('paginate'));
  
      return $this->sendResponse($training_credits, 'List of all training credits.', 200);
    
    }

    public function store(Request $request)
    {
      $request->validate([
        'name' => 'required|string'
      ]);

      TrainingCredit::create([
        'name' => $request->name,
        'uploader_id' => $this->authUser()->id
      ]);
      
      return $this->sendResponse([], 'You have successfully created a new training credit.', 201);
    }

    public function update($id, Request $request) 
    {
        $request->validate([
          'id' => 'exists:training_credits,id',
          'name' => 'required|string',
        ]);

        $training_credit = TrainingCredit::findOrFail($id);

        $training_credit->update([ 'name' =>  $request->name]);

        return $this->sendResponse([], 'Training credit Updated', 200);
      
    }

    public function destroy($id)
    {
      $training_credit = TrainingCredit::find($id);

      $training_credit->delete();
  
      return $this->sendResponse([], 'Training credit deleted.', 200);
    }
}
