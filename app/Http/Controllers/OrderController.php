<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Services\DataValidator;
use App\Services\EntityService;
use App\Services\QueryService;
use App\Services\ResponseService;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use DB;

class OrderController extends Controller
{
    public function getAll(Request $request)
    {
        try {
            
            // Define the columns groups that want to select
            $allColumns = ['orders.id', 'customer_id', 'customers.first_name as customer_name','customers.phone as customer_phone','customers.address as customer_address', 'product_id','products.name as product_name','quantity', 'orders.status','orders.payment_method'];

            // Define allowed filters, searchable columns for where condition
            $allowedFilters = ['customer_id', 'product_id','quantity', 'orders.status','payment_method'];
            $searchColumns = ['customer_id', 'product_id','quantity', 'orders.status','payment_method'];

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
            $isCreating = !isset($id);

            $rules = [
                'customer_id' => 'required|exists:customers,id',
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer',
                'status' => 'required|string|max:255',
                'payment_method' => 'required|string|max:255'
            ];

            $validator = DataValidator::make($request->all(), $rules);

            if ($validator->fails()) {
                return ResponseService::response('VALIDATION_ERROR', $validator->errors(), "Validation Failed");
            }

            DB::beginTransaction();

            if ($isCreating) {
                $orderData = $request->all();
                $order = Order::create($orderData);
                $message = "Order created successfully.";
            } else {
                $order = Order::find($id);
                if (!$order) {
                    return ResponseService::response('NOT_FOUND', null, "Order not found.");
                }
                $order->update($request->all());
                $message = "Order updated successfully.";
            }

            DB::commit();

            // Fetch customer phone number (assuming 'Customer' has a 'phone' field)
            $customer = Customer::find($request->input('customer_id'));

            // if ($customer && $isCreating) {
            //     $this->sendOrderConfirmationSMS($customer->phone, $order->id);
            // }

            return ResponseService::response('SUCCESS', $order, $message);
        } catch (\Throwable $exception) {
            DB::rollBack();
            return ResponseService::response('INTERNAL_SERVER_ERROR', $exception->getMessage());
        }
    }

    private function sendOrderConfirmationSMS($phoneNumber, $orderId)
    {
        $apiKey = '1e78a1633a00cdee1db197ca99773bf0-f5ae94b8-7980-4348-bfe2-f6b29355d54a';
        $baseUrl = env('INFOBIP_BASE_URL');

        $client = new Client(['base_uri' => $baseUrl]);

        $response = $client->post('https://api.infobip.com/sms/2/text/advanced', [
            'headers' => [
                'Authorization' => 'App ' . $apiKey,
                'Content-Type' => 'application/json'
            ],
            'json' => [
                'messages' => [
                    [
                        'from' => '447491163443', // sender ID
                        'destinations' => [
                            ['to' => $phoneNumber]
                        ],
                        'text' => "Your order #$orderId has been successfully placed. Thank you for shopping with us!"
                    ]
                ]
            ]
        ]);

        // Handle the response if needed
        if ($response->getStatusCode() != 200) {
            \Log::error("Failed to send SMS: " . $response->getBody());
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
