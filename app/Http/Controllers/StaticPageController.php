<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\StaticPage;
use Illuminate\Http\Request;
use App\Services\DataValidator;
use App\Services\QueryService;
use App\Services\ResponseService;
use DB;

class StaticPageController extends Controller
{
    public function getAll(Request $request)
    {
        try {
            // Define the columns groups that want to select
            $allColumns = ['id', 'page_type', 'description', 'content'];

            // Define allowed filters, searchable columns for where condition
            $allowedFilters = ['page_type', 'description', 'content'];
            $searchColumns = ['page_type', 'description', 'content'];

            // Define allowed sorting columns for 'orderBy' method
            $allowedSortingColumns = ['id', 'page_type', 'description', 'content'];

            // Get filter JSON, search string, pagination parameters, etc. from the request
            $filterJson = $request->filters ?? [];
            $searchString = $request->search ?? '';
            $page = $request->page ?? 1;
            $limit = $request->limit ?? 10;
            $sortBy = $request->sort_by ?? 'id';
            $sortDir = $request->sort_dir ?? 'asc';

            // Build the base query for the base table
            $baseQuery = DB::table('static_pages');
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

            // Find the Static Page by its ID
            $staticPage = StaticPage::find($id);

            // Check if the Static Page was found
            if (!$staticPage) {
                // Return a not found response if the Static Page doesn't exist
                return ResponseService::response('NOT_FOUND', null, "Static Page not found.");
            }

            // Return a successful response with the Static Page data
            return ResponseService::response('SUCCESS', $staticPage);
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
                'page_type' => 'required|string|max:255|unique:static_pages,page_type,NULL,id',
                'description' => 'nullable|string|max:255',
            ];

            if (!$isCreating) {
                $rules['page_type'] = 'required|string|max:255|unique:static_pages,page_type,' . $id . ',id';
            }

            $validator = DataValidator::make($request->all(), $rules);

            // If validation fails, return a validation error response.
            if ($validator->fails()) {
                return ResponseService::response('VALIDATION_ERROR', $validator->errors(), "Validation Failed");
            }

            // Determine whether you are creating a new Static Page or updating an existing one

            DB::beginTransaction();
            // Create or update the Static Page based on the request data
            if ($isCreating) {
                // Create a new Static Page using the request data
                $staticPageData = $request->all();
                $staticPage = StaticPage::create($staticPageData);
                $message = "Static Page created successfully.";
            } else {
                // Update an existing Static Page using the request data
                $staticPage = StaticPage::find($id);
                if (!$staticPage) {
                    return ResponseService::response('NOT_FOUND', null, "Static Page not found.");
                }

                $staticPage->update($request->all());
                $message = "Static Page updated successfully.";
            }

            DB::commit();

            // Return a successful response with the Static Page data and a success message
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
            // Find the Static Page by its ID
            $staticPage = StaticPage::find($id);

            // Check if the Static Page was found
            if (!$staticPage) {
                // Return a not found response if the Static Page doesn't exist
                return ResponseService::response('NOT_FOUND', null, "Static Page not found.");
            }

            // Delete the Static Page
            $staticPage->delete();

            // Return a successful response indicating successful deletion
            return ResponseService::response('SUCCESS', null, "Static Page deleted successfully.");
        } catch (\Throwable $exception) {
            // Handle exceptions and return an error response
            return ResponseService::response('INTERNAL_SERVER_ERROR', $exception->getMessage());
        }
    }
}