<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\DataValidator;
use App\Services\QueryService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use App\Models\Cart;
use DB;

class CartController extends Controller
{
    public function getAll(Request $request)
    {
        try {
            
            // Define the columns groups that want to select
            $allColumns = ['id', 'user_id', 'product_id','quantity'];

            // Define allowed filters, searchable columns for where condition
            $allowedFilters = ['user_id', 'product_id','quantity'];
            $searchColumns = ['user_id', 'product_id','quantity'];

            // Define allowed sorting columns for 'orderBy' method
            $allowedSortingColumns = ['user_id', 'product_id','quantity'];

            // Get filter JSON, search string, pagination parameters, etc. from the request
            $filterJson = $request->filters ?? [];
            $searchString = $request->search ?? '';
            $page = $request->page ?? 1;
            $limit = $request->limit ?? 10;
            $sortBy = $request->sort_by ?? 'id';
            $sortDir = $request->sort_dir ?? 'asc';

            // Build the base query for the base table
            $baseQuery = DB::table('cart')->whereNull('deleted_at');
            // You can add your left join queries and additional where conditions here if needed

            // Apply filters, search, and conditions to the base query
            $conditionAppliedQuery = QueryService::addConditionQuery($baseQuery, $allowedFilters, $filterJson, $searchColumns, $searchString);

            // Select the specified columns from the query
            $dataQuery = $conditionAppliedQuery->select($allColumns);

            // Paginate the results based on the requested page and limit
            $data = QueryService::paginate($dataQuery, $page, $limit, $allowedSortingColumns, $sortBy, $sortDir);

            // Return a successful response with the data
            return ResponseService::response('SUCCESS', $data);
        } catch (\Throwable $exception) {
            // Handle exceptions and return an error response
            return ResponseService::response('INTERNAL_SERVER_ERROR', $exception->getMessage());
        }
    }

    public function getOne($id)
    {
        try {
            // Find the Cart by its ID
            $cart = Cart::find($id);

            // Check if the Cart was found
            if (!$cart) {
                // Return a not found response if the Cart doesn't exist
                return ResponseService::response('NOT_FOUND', null, "Cart not found.");
            }

            // Return a successful response with the Cart data
            return ResponseService::response('SUCCESS', $cart);
        } catch (\Throwable $exception) {
            // Handle exceptions and return an error response
            return ResponseService::response('INTERNAL_SERVER_ERROR', $exception->getMessage());
        }
    }

    public function getUserCart($userId)
    {
        try {
            // Find all Cart items by the user ID
            $cartItems = Cart::where('user_id', $userId)->with('product')->whereNull('deleted_at')->get();

            // Check if any Cart items were found
            if ($cartItems->isEmpty()) {
                // Return a not found response if no user exist
                return ResponseService::response('SUCCESS', null, "No Product Found.");
            }

            // Return a successful response with the Cart items data
            return ResponseService::response('SUCCESS', $cartItems);
        } catch (\Throwable $exception) {
            // Handle exceptions and return an error response
            return ResponseService::response('INTERNAL_SERVER_ERROR', $exception->getMessage());
        }
    }

    public function storeOrUpdateUserCart(Request $request, $id = null)
    {
        try {
            // Check if we are creating a new record (not updating an existing one)
            $isCreating = !isset($id);

            // Define validation rules for the form inputs
            $rules = [
                'user_id' => 'required|exists:users,id',
                'product_id' => 'required|exists:products,id,deleted_at,NULL',
                'quantity' => 'required|integer',
            ];

            $validator = DataValidator::make($request->all(), $rules);

            // If validation fails, return a validation error response.
            if ($validator->fails()) {
                return ResponseService::response('VALIDATION_ERROR', $validator->errors(), "Validation Failed");
            }

            DB::beginTransaction();
            // Create or update the User Cart based on the request data
            if ($isCreating) {
                  // Create a new User Cart entry
                  $userCartData = $request->all();
                  $userCart = Cart::create($userCartData);
                  $message = "Product added to cart successfully.";
            } else {
                // Check if the user already has this product in their cart
                $userCart = Cart::where('user_id', $request->user_id)
                                ->where('product_id', $request->product_id)
                                ->first();

                if (!$userCart) {
                    return ResponseService::response('NOT_FOUND', null, "Cart not found.");
                }

                $userCart->update($request->all());
                $message = "Cart updated successfully.";
            }

            DB::commit();

            // Return a successful response with the userCart data and a success message
            return ResponseService::response('SUCCESS', $userCart, $message);
        } catch (\Throwable $exception) {
            DB::rollBack();
            // Handle exceptions and return an error response
            return ResponseService::response('INTERNAL_SERVER_ERROR', $exception->getMessage());
        }
    }

    public function deleteUserCartProduct($userID, $cartId)
    {
        try {

            // Find the Cart by user_id, and cart_id
            $userCartProduct = Cart::where('user_id', $userID)
            ->where('id', $cartId)
            ->first();

            // Check if the Cart was found
            if (!$userCartProduct) {
            // Return a not found response if the Cart doesn't exist
            return ResponseService::response('NOT_FOUND', null, "Cart item not found.");
            }

            // Delete the Product from the cart
            $userCartProduct->delete();

            // Return a successful response indicating successful deletion
            return ResponseService::response('SUCCESS', "Product deleted from the cart successfully.");
        } catch (\Throwable $exception) {
            // Handle exceptions and return an error response
            return ResponseService::response('INTERNAL_SERVER_ERROR', $exception->getMessage());
        }
    }

    public function deleteUserCart($cartId)
    {
        try {

            // Find the Cart by cart_id
            $userCart = Cart::where('id', $cartId);

            // Check if the Cart was found
            if (!$userCart) {
            // Return a not found response if the Cart doesn't exist
            return ResponseService::response('NOT_FOUND', null, "Cart not found.");
            }

            // Delete the cart
            $userCart->delete();

            // Return a successful response indicating successful deletion
            return ResponseService::response('SUCCESS', "Cart deleted successfully.");
        } catch (\Throwable $exception) {
            // Handle exceptions and return an error response
            return ResponseService::response('INTERNAL_SERVER_ERROR', $exception->getMessage());
        }
    }

}