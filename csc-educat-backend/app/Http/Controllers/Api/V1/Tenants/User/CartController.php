<?php

namespace App\Http\Controllers\Api\V1\Tenants\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Models\CartItem;
use App\Models\Course;

class CartController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.user');
    }

    public function addToCartItems(Request $request)
    {
        if($request->page) {
            $items = $this->authUser()->cart_item_courses()->paginate(5);
        } else {
            $items = $this->authUser()->cart_item_courses;
        }

        return $this->sendResponse($items, 'List of add to cart items.', 200);
    }

    public function addItemToCart(Request $request)
    {
        try {
            $request->validate([
                'course_id.*' => 'required|exists:courses,id',
            ]);

            $courseIds = is_array($request->course_id) ? $request->course_id : [$request->course_id];

            $user = $this->authUser();

            // Sync courses to cart without detaching existing ones
            $user->cart_item_courses()->syncWithoutDetaching($courseIds);

            return $this->sendResponse([], 'Added to cart.', 201);
        } catch (\Illuminate\Database\QueryException $e) {

            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                return $this->sendResponse([], 'Item already in cart.', 400);
            }

            return $this->sendResponse([], 'An error occurred while adding the item to the cart.', 500);

        } catch (\Exception $e) {

            Log::error('Unexpected error: ' . $e->getMessage());
            return $this->sendResponse([], 'An unexpected error occurred.', 500);

        }
    }



    public function removeItemFromCart(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id'
        ]);

        $this->authUser()->cart_item_courses()->detach($request->course_id);

        return $this->sendResponse([], 'Item removed from cart', 200);
    }

    public function emptyCart(Request $request)
    {
        $request->validate([
            'course_id.*' => 'required|exists:courses,id'
        ]);

        $this->authUser()->cart_item_courses()->detach($request->course_id);

        return $this->sendResponse([], 'Empty cart.', 200);
    }
}
