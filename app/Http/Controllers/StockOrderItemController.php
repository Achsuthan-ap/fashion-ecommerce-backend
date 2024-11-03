<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\StockOrderItem;
use App\Services\DataValidator;
use App\Services\QueryService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use DB;

class StockOrderItemController extends Controller
{
    public function getAll(Request $request)
    {
        try {
            
            // Define the columns groups that want to select
            $allColumns = ['order_item.id', 'stock_order_id', 'product_id','products.name as product_name','quantity', 'price','subtotal'];

            // Define allowed filters, searchable columns for where condition
            $allowedFilters = ['stock_order_id', 'product_id','quantity', 'price','subtotal'];
            $searchColumns = ['stock_order_id', 'product_id','quantity', 'price','subtotal'];

            // Define allowed sorting columns for 'orderBy' method
            $allowedSortingColumns = ['stock_order_id', 'product_id','quantity', 'price','subtotal'];

            // Get filter JSON, search string, pagination parameters, etc. from the request
            $filterJson = $request->filters ?? [];
            $searchString = $request->search ?? '';
            $page = $request->page ?? 1;
            $limit = $request->limit ?? 10;
            $sortBy = $request->sort_by ?? 'id';
            $sortDir = $request->sort_dir ?? 'asc';

            // Build the base query for the base table
            $baseQuery = DB::table('stock_order_item as order_item')->whereNull('order_item.deleted_at')
            ->leftJoin('stock_orders', 'order_item.stock_order_id', '=', 'stock_orders.id')
            ->leftJoin('products', 'order_item.product_id', '=', 'products.id');
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
            $order = StockOrderItem::find($id);

            // Check if the order was found
            if (!$order) {
                // Return a not found response if the order doesn't exist
                return ResponseService::response('NOT_FOUND', null, "order item not found.");
            }

            $order->stockOrder;
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
                'stock_order_id' => 'required|exists:stock_orders,id',
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer',
                'price' => 'required|string|max:255',
                'subtotal'=> 'required|string|max:255'
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

                $order = StockOrderItem::create($orderData);
                $message = "Order item created successfully.";
            } else {
                // Update an existing Order using the request data
                $order = StockOrderItem::find($id);
                if (!$order) {
                    return ResponseService::response('NOT_FOUND', null, "Order not found.");
                }

                $order->update($request->all());
                $message = "Order item updated successfully.";
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
            $order = StockOrderItem::find($id);

            // Check if the Order was found
            if (!$order) {
                // Return a not found response if the Order doesn't exist
                return ResponseService::response('NOT_FOUND', null, "Order item not found.");
            }

            // Delete the Order
            $order->delete();

            // Return a successful response indicating successful deletion
            return ResponseService::response('SUCCESS', "Order item deleted successfully.");
        } catch (\Throwable $exception) {
            // Handle exceptions and return an error response
            return ResponseService::response('INTERNAL_SERVER_ERROR', $exception->getMessage());
        }
    }
}
