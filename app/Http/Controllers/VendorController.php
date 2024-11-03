<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Services\DataValidator;
use App\Services\EntityService;
use App\Services\QueryService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use DB;

class VendorController extends Controller
{
    public function getAll(Request $request)
    {
        try {
            
            // Define the columns groups that want to select
            $allColumns = ['id', 'name', 'contact_person','email', 'phone_number', 'address','country','status'];

            // Define allowed filters, searchable columns for where condition
            $allowedFilters = ['id', 'name', 'contact_person','email', 'phone_number', 'address','country','status'];
            $searchColumns = ['id', 'name', 'contact_person','email', 'phone_number', 'address','country','status'];

            // Define allowed sorting columns for 'orderBy' method
            $allowedSortingColumns = ['id', 'name', 'contact_person','email', 'phone_number', 'address','country','status'];

            // Get filter JSON, search string, pagination parameters, etc. from the request
            $filterJson = $request->filters ?? [];
            $searchString = $request->search ?? '';
            $page = $request->page ?? 1;
            $limit = $request->limit ?? 10;
            $sortBy = $request->sort_by ?? 'id';
            $sortDir = $request->sort_dir ?? 'asc';

            // Build the base query for the base table
            $baseQuery = DB::table('vendors');
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
            // Find the vendor by its ID
            $vendor = Vendor::find($id);

            // Check if the Vendor was found
            if (!$vendor) {
                // Return a not found response if the vendor doesn't exist
                return ResponseService::response('NOT_FOUND', null, "vendor not found.");
            }

            // Return a successful response with the vendor data
            return ResponseService::response('SUCCESS', $vendor);
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
                'name' => 'required|string|max:255',
                'contact_person' => 'nullable|string|max:255',
                'phone_number' => 'required|string|max:255',
                'email' => 'nullable|email',
                'address' => 'required|string',
                'country'=> 'required|string'
            ];


            $validator = DataValidator::make($request->all(), $rules);

            // If validation fails, return a validation error response.
            if ($validator->fails()) {
                return ResponseService::response('VALIDATION_ERROR', $validator->errors(), "Validation Failed");
            }

            // Determine whether you are creating a new vendor or updating an existing one

            DB::beginTransaction();
            // Create or update the vendor based on the request data
            if ($isCreating) {
                // Create a new vendor using the request data
                $vendorData = $request->all();
                $entity = EntityService::store($request->all());
                // Add $entity->id to the request data
                $vendorData['entity_id'] = $entity->id;

                $vendor = Vendor::create($vendorData);
                $message = "vendor created successfully.";
            } else {
                // Update an existing vendor using the request data
                $vendor = Vendor::find($id);
                if (!$vendor) {
                    return ResponseService::response('NOT_FOUND', null, "vendor not found.");
                }

                EntityService::update($vendor->entity_id, $request->all());
                $vendor->update($request->all());
                $message = "vendor updated successfully.";
            }

            DB::commit();

            // Return a successful response with the vendor data and a success message
            return ResponseService::response('SUCCESS', $vendor, $message);
        } catch (\Throwable $exception) {
            DB::rollBack();
            // Handle exceptions and return an error response
            return ResponseService::response('INTERNAL_SERVER_ERROR', $exception->getMessage());
        }
    }

    public function delete($id)
    {
        try {

            // Find the vendor by its ID
            $vendor = Vendor::find($id);

            // Check if the vendor was found
            if (!$vendor) {
                // Return a not found response if the vendor doesn't exist
                return ResponseService::response('NOT_FOUND', null, "vendor not found.");
            }

            // Delete the vendor
            $vendor->delete();

            // Return a successful response indicating successful deletion
            return ResponseService::response('SUCCESS', "vendor deleted successfully.");
        } catch (\Throwable $exception) {
            // Handle exceptions and return an error response
            return ResponseService::response('INTERNAL_SERVER_ERROR', $exception->getMessage());
        }
    }
}
