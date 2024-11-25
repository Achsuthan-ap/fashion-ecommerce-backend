<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\ProductArrivalNotification;
use App\Models\Subscription;
use App\Services\QueryService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Mail;

class CustomerNotificationController extends Controller
{
    public function getAll(Request $request)
    {
        try {
            // Define the columns groups that want to select
            $allColumns = ['subscriptions.id', 'product_id','products.name as product_name', 'subscriptions.email', 'subscriptions.status'];

            // Define allowed filters, searchable columns for where condition
            $allowedFilters = ['product_id', 'subscriptions.email', 'subscriptions.status'];
            $searchColumns = ['product_id', 'email', 'status'];

            // Define allowed sorting columns for 'orderBy' method
            $allowedSortingColumns = ['product_id', 'email', 'status'];

            // Get filter JSON, search string, pagination parameters, etc. from the request
            $filterJson = $request->filters ?? [];
            $searchString = $request->search ?? '';
            $page = $request->page ?? 1;
            $limit = $request->limit ?? 10;
            $sortBy = $request->sort_by ?? 'id';
            $sortDir = $request->sort_dir ?? 'asc';

            // Build the base query for the base table
            $baseQuery = DB::table('subscriptions')
            ->leftJoin('products', 'subscriptions.product_id', '=', 'products.id');;
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

            // Find the Product Category by its ID
            $notification = Subscription::find($id);

            // Check if the Product Category was found
            if (!$notification) {
                // Return a not found response if the Product Category doesn't exist
                return ResponseService::response('NOT_FOUND', null, "Notification not found.");
            }
            $notification->product;

            // Return a successful response with the Product Category data
            return ResponseService::response('SUCCESS', $notification);
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

            DB::beginTransaction();
            // Create or update the Product Category based on the request data
            if ($isCreating) {
                // Create a new Product Category using the request data
                $notificationData = $request->all();

                $notification = Subscription::create($notificationData);
                $message = "Notification created successfully.";
            } else {
                // Update an existing Product Category using the request data
                $notification = Subscription::find($id);
                if (!$notification) {
                    return ResponseService::response('NOT_FOUND', null, "Product Category not found.");
                }

                $notification->update($request->all());
                $message = "Notification updated successfully.";
            }

            DB::commit();

            // Return a successful response with the Product Category data and a success message
            return ResponseService::response('SUCCESS', null, $message);
        } catch (\Throwable $exception) {
            DB::rollBack();
            // Handle exceptions and return an error response
            return ResponseService::response('INTERNAL_SERVER_ERROR', $exception->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            // Find the Product Category by its ID
            $notification = Subscription::find($id);

            // Check if the Product Category was found
            if (!$notification) {
                // Return a not found response if the Product Category doesn't exist
                return ResponseService::response('NOT_FOUND', null, "Product Category not found.");
            }

            // Delete the Product Category
            $notification->delete();

            // Return a successful response indicating successful deletion
            return ResponseService::response('SUCCESS', null, "Product Category deleted successfully.");
        } catch (\Throwable $exception) {
            // Handle exceptions and return an error response
            return ResponseService::response('INTERNAL_SERVER_ERROR', $exception->getMessage());
        }
    }

    public function sendNotification($id)
    {
        try {
            $subscription = Subscription::findOrFail($id);
            
            // Send email notification
            Mail::to($subscription->email)->send(new ProductArrivalNotification($subscription));

            return response()->json([
                'is_success' => true,
                'message' => 'Notification sent successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'is_success' => false,
                'message' => 'Failed to send notification.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
}
