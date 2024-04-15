<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Services\DataValidator;
use App\Services\QueryService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use DB;

class OfferController extends Controller
{
    public function getAll(Request $request)
    {
        try {
            // Define the columns groups that want to select
            $allColumns = ['id', 'title', 'offer_type', "offer_value"];

            // Define allowed filters, searchable columns for where condition
            $allowedFilters = ['title', 'description', "offer_type", "offer_value"];
            $searchColumns = ['title', 'description', "offer_type", "offer_value"];

            // Define allowed sorting columns for 'orderBy' method
            $allowedSortingColumns = ['title', 'description', "offer_type", "offer_value"];

            // Get filter JSON, search string, pagination parameters, etc. from the request
            $filterJson = $request->filters ?? [];
            $searchString = $request->search ?? '';
            $page = $request->page ?? 1;
            $limit = $request->limit ?? 10;
            $sortBy = $request->sort_by ?? 'id';
            $sortDir = $request->sort_dir ?? 'asc';

            // Build the base query for the base table
            $baseQuery = DB::table('offers')
                ->whereNull('deleted_at');
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

            // Find the Offer by its ID
            $offer = Offer::find($id);

            // Check if the Offer was found
            if (!$offer) {
                // Return a not found response if the Offer doesn't exist
                return ResponseService::response('NOT_FOUND', null, "Offer not found.");
            }

            $offer->entity; //for getting flexfield values

            // Return a successful response with the Offer data
            return ResponseService::response('SUCCESS', $offer);
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
                'title' => 'required|string|max:255|unique:offers,title,NULL,id,deleted_at,NULL',
                'description' => 'nullable|string|max:255',
                'offer_type'=> 'required|string',
                'offer_value'=> 'required|integer'
            ];

            if (!$isCreating) {
                $rules['title'] = 'required|string|max:255|unique:offers,title' . $id . ',id,deleted_at,NULL';
            }

            $validator = DataValidator::make($request->all(), $rules);

            // If validation fails, return a validation error response.
            if ($validator->fails()) {
                return ResponseService::response('VALIDATION_ERROR', $validator->errors(), "Validation Failed");
            }

            // Determine whether you are creating a new Offer or updating an existing one

            DB::beginTransaction();
            // Create or update the Offer based on the request data
            if ($isCreating) {
                // Create a new Offer using the request data
                $offerData = $request->all();
                $offer = Offer::create($offerData);
                $message = "Offer created successfully.";
            } else {
                // Update an existing Offer using the request data
                $offer = Offer::find($id);
                if (!$offer) {
                    return ResponseService::response('NOT_FOUND', null, "Offer not found.");
                }

                $offer->update($request->all());
                $message = "Offer updated successfully.";
            }

            DB::commit();

            // Return a successful response with the Offer data and a success message
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
            // Find the Offer by its ID
            $offer = Offer::find($id);

            // Check if the Offer was found
            if (!$offer) {
                // Return a not found response if the Offer doesn't exist
                return ResponseService::response('NOT_FOUND', null, "Offer not found.");
            }

            // Delete the Offer
            $offer->delete();

            // Return a successful response indicating successful deletion
            return ResponseService::response('SUCCESS', null, "Offer deleted successfully.");
        } catch (\Throwable $exception) {
            // Handle exceptions and return an error response
            return ResponseService::response('INTERNAL_SERVER_ERROR', $exception->getMessage());
        }
    }
}
