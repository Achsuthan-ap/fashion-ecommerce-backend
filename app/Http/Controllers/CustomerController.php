<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\DataValidator;
use App\Services\EntityService;
use App\Services\QueryService;
use Illuminate\Http\Request;
use App\Services\ResponseService;
use DB;
use User;

class CustomerController extends Controller
{
    public function getAll(Request $request)
    {
        try {
            
            // Define the columns groups that want to select
            $allColumns = ['id', 'first_name', 'last_name','email', 'phone', 'address','user_id'];

            // Define allowed filters, searchable columns for where condition
            $allowedFilters = ['first_name', 'last_name','email', 'phone', 'address'];
            $searchColumns = ['first_name', 'last_name','email', 'phone', 'address'];

            // Define allowed sorting columns for 'orderBy' method
            $allowedSortingColumns = ['first_name', 'last_name','email', 'phone', 'address'];

            // Get filter JSON, search string, pagination parameters, etc. from the request
            $filterJson = $request->filters ?? [];
            $searchString = $request->search ?? '';
            $page = $request->page ?? 1;
            $limit = $request->limit ?? 10;
            $sortBy = $request->sort_by ?? 'id';
            $sortDir = $request->sort_dir ?? 'asc';

            // Build the base query for the base table
            $baseQuery = DB::table('customers');
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
            // Find the Customer by its ID
            $customer = Customer::find($id);

            // Check if the Customer was found
            if (!$customer) {
                // Return a not found response if the Customer doesn't exist
                return ResponseService::response('NOT_FOUND', null, "Customer not found.");
            }

            // Return a successful response with the Customer data
            return ResponseService::response('SUCCESS', $customer);
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
                'first_name' => 'required|string|max:255',
                'last_name' => 'nullable|string|max:255',
                'phone' => 'required|string|max:255',
                'email' => 'nullable|email',
                'address' => 'required|string',
            ];


            $validator = DataValidator::make($request->all(), $rules);

            // If validation fails, return a validation error response.
            if ($validator->fails()) {
                return ResponseService::response('VALIDATION_ERROR', $validator->errors(), "Validation Failed");
            }

            // Determine whether you are creating a new Customer or updating an existing one

            DB::beginTransaction();
            // Create or update the Customer based on the request data
            if ($isCreating) {
                // Create a new Customer using the request data
                $customerData = $request->all();
                $entity = EntityService::store($request->all());
                // Add $entity->id to the request data
                $customerData['entity_id'] = $entity->id;

                $customer = Customer::create($customerData);
                $message = "Customer created successfully.";
            } else {
                // Update an existing Customer using the request data
                $customer = Customer::find($id);
                if (!$customer) {
                    return ResponseService::response('NOT_FOUND', null, "Customer not found.");
                }

                EntityService::update($customer->entity_id, $request->all());
                $customer->update($request->all());
                $message = "Customer updated successfully.";
            }

            DB::commit();

            // Return a successful response with the Customer data and a success message
            return ResponseService::response('SUCCESS', $customer, $message);
        } catch (\Throwable $exception) {
            DB::rollBack();
            // Handle exceptions and return an error response
            return ResponseService::response('INTERNAL_SERVER_ERROR', $exception->getMessage());
        }
    }

    public function delete($id)
    {
        try {

            // Find the Customer by its ID
            $customer = Customer::find($id);

            // Check if the Customer was found
            if (!$customer) {
                // Return a not found response if the Customer doesn't exist
                return ResponseService::response('NOT_FOUND', null, "Customer not found.");
            }

            // Delete the Customer
            $customer->delete();

            // Return a successful response indicating successful deletion
            return ResponseService::response('SUCCESS', "Customer deleted successfully.");
        } catch (\Throwable $exception) {
            // Handle exceptions and return an error response
            return ResponseService::response('INTERNAL_SERVER_ERROR', $exception->getMessage());
        }
    }
}
