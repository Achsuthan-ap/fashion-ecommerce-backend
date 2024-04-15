<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\DataValidator;
use App\Services\QueryService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\EntityService;

class ProductCategoryController extends Controller
{
    public function getAll(Request $request)
    {
        try {
            // Define the columns groups that want to select
            $allColumns = ['id', 'title', 'description'];

            // Define allowed filters, searchable columns for where condition
            $allowedFilters = ['title', 'description'];
            $searchColumns = ['title', 'description'];

            // Define allowed sorting columns for 'orderBy' method
            $allowedSortingColumns = ['id', 'title', 'description'];

            // Get filter JSON, search string, pagination parameters, etc. from the request
            $filterJson = $request->filters ?? [];
            $searchString = $request->search ?? '';
            $page = $request->page ?? 1;
            $limit = $request->limit ?? 10;
            $sortBy = $request->sort_by ?? 'id';
            $sortDir = $request->sort_dir ?? 'asc';

            // Build the base query for the base table
            $baseQuery = DB::table('product_categories')
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

            // Find the Product Category by its ID
            $productCategory = ProductCategory::find($id);

            // Check if the Product Category was found
            if (!$productCategory) {
                // Return a not found response if the Product Category doesn't exist
                return ResponseService::response('NOT_FOUND', null, "Product Category not found.");
            }

            $productCategory->entity; //for getting flexfield values

            // Return a successful response with the Product Category data
            return ResponseService::response('SUCCESS', $productCategory);
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
                'title' => 'required|string|max:255|unique:product_categories,title,NULL,id,deleted_at,NULL',
                'description' => 'nullable|string|max:255',
            ];

            if (!$isCreating) {
                $rules['title'] = 'required|string|max:255|unique:product_categories,title,' . $id . ',id,deleted_at,NULL';
            }

            $validator = DataValidator::make($request->all(), $rules);

            // If validation fails, return a validation error response.
            if ($validator->fails()) {
                return ResponseService::response('VALIDATION_ERROR', $validator->errors(), "Validation Failed");
            }

            // Determine whether you are creating a new Product Category or updating an existing one

            DB::beginTransaction();
            // Create or update the Product Category based on the request data
            if ($isCreating) {
                // Create a new Product Category using the request data
                $productCategoryData = $request->all();
                $entity = EntityService::store($request->all());
                // Add $entity->id to the request data
                $productCategoryData['entity_id'] = $entity->id;

                $productCategory = ProductCategory::create($productCategoryData);
                $message = "Product Category created successfully.";
            } else {
                // Update an existing Product Category using the request data
                $productCategory = ProductCategory::find($id);
                if (!$productCategory) {
                    return ResponseService::response('NOT_FOUND', null, "Product Category not found.");
                }

                EntityService::update($productCategory->entity_id, $request->all());
                $productCategory->update($request->all());
                $message = "Product Category updated successfully.";
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
            $productCategory = ProductCategory::find($id);

            // Check if the Product Category was found
            if (!$productCategory) {
                // Return a not found response if the Product Category doesn't exist
                return ResponseService::response('NOT_FOUND', null, "Product Category not found.");
            }

            $usedCount = Product::where('category_id', $productCategory->id)->count();

            if ($usedCount > 0) {
                return ResponseService::response('CONFLICT', null, "Unable to delete Product Category. It's referenced in Product. Update records or resolve conflicts.");
            }

            // Delete the Product Category
            $productCategory->delete();

            // Return a successful response indicating successful deletion
            return ResponseService::response('SUCCESS', null, "Product Category deleted successfully.");
        } catch (\Throwable $exception) {
            // Handle exceptions and return an error response
            return ResponseService::response('INTERNAL_SERVER_ERROR', $exception->getMessage());
        }
    }
}
