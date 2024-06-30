<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
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
            $baseQuery = DB::table('cart');
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
            $cartItems = Cart::where('user_id', $userId)->with('product')->get();

            // Check if any Cart items were found
            if ($cartItems->isEmpty()) {
                // Return a not found response if no user exist
                return ResponseService::response('NOT_FOUND', null, "User not found.");
            }

            // Return a successful response with the Cart items data
            return ResponseService::response('SUCCESS', $cartItems);
        } catch (\Throwable $exception) {
            // Handle exceptions and return an error response
            return ResponseService::response('INTERNAL_SERVER_ERROR', $exception->getMessage());
        }
    }


}