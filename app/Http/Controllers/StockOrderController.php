<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\StockOrder;
use App\Services\DataValidator;
use App\Services\QueryService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use DB;

class StockOrderController extends Controller
{
    public function getAll(Request $request)
    {
        try {
            
            // Define the columns groups that want to select
            $allColumns = ['orders.id', 'vendor_id','vendors.name as vendor_name', 'order_number','delivery_date','delivery_address', 'orders.status','orders.total_amount'];

            // Define allowed filters, searchable columns for where condition
            $allowedFilters = ['vendor_id', 'order_number','delivery_date', 'status','total_amount','delivery_address'];
            $searchColumns = ['vendor_id', 'order_number','delivery_date', 'status','total_amount','delivery_address'];

            // Define allowed sorting columns for 'orderBy' method
            $allowedSortingColumns = ['vendor_id', 'order_number','delivery_date', 'status','total_amount','delivery_address'];

            // Get filter JSON, search string, pagination parameters, etc. from the request
            $filterJson = $request->filters ?? [];
            $searchString = $request->search ?? '';
            $page = $request->page ?? 1;
            $limit = $request->limit ?? 10;
            $sortBy = $request->sort_by ?? 'id';
            $sortDir = $request->sort_dir ?? 'asc';

            // Build the base query for the base table
            $baseQuery = DB::table('stock_orders as orders')
            ->leftJoin('vendors', 'orders.vendor_id', '=', 'vendors.id');
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
            $order = StockOrder::find($id);

            // Check if the order was found
            if (!$order) {
                // Return a not found response if the order doesn't exist
                return ResponseService::response('NOT_FOUND', null, "order not found.");
            }

            $order->vendor;
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
                'vendor_id' => 'required|exists:vendors,id',
                'order_number' => 'required|string',
                'delivery_date' => 'required|date',
                'delivery_address' => 'required|string',
                'status' => 'required|string|max:255',
                'total_amount'=> 'required|string|max:255'
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

                $order = StockOrder::create($orderData);
                $message = "Order created successfully.";
            } else {
                // Update an existing Order using the request data
                $order = StockOrder::find($id);
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
            $order = StockOrder::find($id);

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
