<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ProductOffer;
use App\Services\DataValidator;
use App\Services\EntityService;
use App\Services\QueryService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use DB;

class ProductOfferController extends Controller
{
    public function getAll(Request $request)
    {
        try {
            // Define the columns groups that want to select
            $allColumns = ['product_offers.id', 'product_offers.product_category_id', 'categories.title AS product_category','product_offers.offer_id', 'offers.title AS offer', 'offers.offer_type', 'offers.offer_value'];

            // Define allowed filters, searchable columns for where condition
            $allowedFilters = ['product_category_id', 'offer_id'];
            $searchColumns = ['product_category_id', 'offer_id'];

            // Define allowed sorting columns for 'orderBy' method
            $allowedSortingColumns = ['product_offers.id', 'product_category_id', 'offer_id'];

            // Get filter JSON, search string, pagination parameters, etc. from the request
            $filterJson = $request->filters ?? [];
            $searchString = $request->search ?? '';
            $page = $request->page ?? 1;
            $limit = $request->limit ?? 10;
            $sortBy = $request->sort_by ?? 'id';
            $sortDir = $request->sort_dir ?? 'asc';

            // Build the base query for the base table
            $baseQuery = DB::table('product_offers')
            ->leftJoin('product_categories as categories', 'product_offers.product_category_id', '=', 'categories.id')
            ->leftJoin('offers', 'product_offers.offer_id', '=', 'offers.id');
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

            // Find the Product Offer by its ID
            $productOffer = ProductOffer::find($id);

            // Check if the Product Offer was found
            if (!$productOffer) {
                // Return a not found response if the Product Offer doesn't exist
                return ResponseService::response('NOT_FOUND', null, "Product Offer not found.");
            }

            $productOffer->entity; //for getting flexfield values

            // Return a successful response with the Product Offer data
            return ResponseService::response('SUCCESS', $productOffer);
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
                'product_category_id' => 'required|unique:product_offers,product_category_id,NULL,id',
                'offer_id' => 'required',
            ];


            $validator = DataValidator::make($request->all(), $rules);

            // If validation fails, return a validation error response.
            if ($validator->fails()) {
                return ResponseService::response('VALIDATION_ERROR', $validator->errors(), "Validation Failed");
            }

            // Determine whether you are creating a new Product Offer or updating an existing one

            DB::beginTransaction();
            // Create or update the Product Offer based on the request data
            if ($isCreating) {
                // Create a new Product Offer using the request data
                $productOfferData = $request->all();
                $entity = EntityService::store($request->all());
                // Add $entity->id to the request data
                $productOfferData['entity_id'] = $entity->id;

                $productOffer = productOffer::create($productOfferData);
                $message = "Product Offer created successfully.";
            } else {
                // Update an existing Product Offer using the request data
                $productOffer = productOffer::find($id);
                if (!$productOffer) {
                    return ResponseService::response('NOT_FOUND', null, "Product Offer not found.");
                }

                EntityService::update($productOffer->entity_id, $request->all());
                $productOffer->update($request->all());
                $message = "Product Offer updated successfully.";
            }

            DB::commit();

            // Return a successful response with the Product Offer data and a success message
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
            // Find the Product Offer by its ID
            $productOffer = productOffer::find($id);

            // Check if the Product Offer was found
            if (!$productOffer) {
                // Return a not found response if the Product Offer doesn't exist
                return ResponseService::response('NOT_FOUND', null, "Product Offer not found.");
            }
            // Delete the Product Offer
            $productOffer->delete();

            // Return a successful response indicating successful deletion
            return ResponseService::response('SUCCESS', null, "Product Offer deleted successfully.");
        } catch (\Throwable $exception) {
            // Handle exceptions and return an error response
            return ResponseService::response('INTERNAL_SERVER_ERROR', $exception->getMessage());
        }
    }
    
}
