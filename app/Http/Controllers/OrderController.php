<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\DataValidator;
use App\Services\EntityService;
use App\Services\QueryService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use DB;

class OrderController extends Controller
{
    public function getAll(Request $request)
    {
        try {
            
            // Define the columns groups that want to select
            $allColumns = ['orders.id', 'customer_id', 'customers.first_name as customer_name','customers.phone as customer_phone','customers.address as customer_address', 'product_id','products.name as product_name','quantity', 'status','payment_method'];

            // Define allowed filters, searchable columns for where condition
            $allowedFilters = ['customer_id', 'product_id','quantity', 'status','payment_method'];
            $searchColumns = ['customer_id', 'product_id','quantity', 'status','payment_method'];

            // Define allowed sorting columns for 'orderBy' method
            $allowedSortingColumns = ['customer_id', 'product_id','quantity', 'status','payment_method'];

            // Get filter JSON, search string, pagination parameters, etc. from the request
            $filterJson = $request->filters ?? [];
            $searchString = $request->search ?? '';
            $page = $request->page ?? 1;
            $limit = $request->limit ?? 10;
            $sortBy = $request->sort_by ?? 'id';
            $sortDir = $request->sort_dir ?? 'asc';

            // Build the base query for the base table
            $baseQuery = DB::table('orders')->whereNull('orders.deleted_at')
            ->leftJoin('customers', 'orders.customer_id', '=', 'customers.id')
            ->leftJoin('products', 'orders.product_id', '=', 'products.id');
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
            // Find the Order by its ID
            $order = Order::find($id);

            // Check if the order was found
            if (!$order) {
                // Return a not found response if the order doesn't exist
                return ResponseService::response('NOT_FOUND', null, "order not found.");
            }

            $order->customer;
            $order->product;
            // Return a successful response with the order data
            return ResponseService::response('SUCCESS', $order);
        } catch (\Throwable $exception) {
            // Handle exceptions and return an error response
            return ResponseService::response('INTERNAL_SERVER_ERROR', $exception->getMessage());
        }
    }

    public function storeOrUpdate(Request $request, $id = null)
    {
        try {
            // Check if we are creating a new record (not updating an existing one)
            $isCreating = !isset($id);

            // Define validation rules for the form inputs
            $rules = [
                'customer_id' => 'required|exists:customers,id',
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer',
                'status' => 'required|string|max:255',
                'payment_method'=> 'required|string|max:255'
            ];

            $validator = DataValidator::make($request->all(), $rules);

            // If validation fails, return a validation error response.
            if ($validator->fails()) {
                return ResponseService::response('VALIDATION_ERROR', $validator->errors(), "Validation Failed");
            }

            // Determine whether you are creating a new Order or updating an existing one

            DB::beginTransaction();
            // Create or update the Order based on the request data
            if ($isCreating) {
                // Create a new Order using the request data
                $orderData = $request->all();

                $order = Order::create($orderData);
                $message = "Order created successfully.";
            } else {
                // Update an existing Order using the request data
                $order = Order::find($id);
                if (!$order) {
                    return ResponseService::response('NOT_FOUND', null, "Order not found.");
                }

                $order->update($request->all());
                $message = "Order updated successfully.";
            }

            DB::commit();

            // Return a successful response with the Order data and a success message
            return ResponseService::response('SUCCESS', $order, $message);
        } catch (\Throwable $exception) {
            DB::rollBack();
            // Handle exceptions and return an error response
            return ResponseService::response('INTERNAL_SERVER_ERROR', $exception->getMessage());
        }
    }

    public function delete($id)
    {
        try {

            // Find the Order by its ID
            $order = Order::find($id);

            // Check if the Order was found
            if (!$order) {
                // Return a not found response if the Order doesn't exist
                return ResponseService::response('NOT_FOUND', null, "Order not found.");
            }

            // Delete the Order
            $order->delete();

            // Return a successful response indicating successful deletion
            return ResponseService::response('SUCCESS', "Order deleted successfully.");
        } catch (\Throwable $exception) {
            // Handle exceptions and return an error response
            return ResponseService::response('INTERNAL_SERVER_ERROR', $exception->getMessage());
        }
    }
}
