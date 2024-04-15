<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\QueryService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use DB;

class ProductController extends Controller
{
    public function getAll(Request $request)
    {
        try {
            // Define the columns groups that want to select
            $allColumns = ['products.id','name','products.description','price','stock_count','images','categories.title AS category','offers.offer_value AS offer','specifications'];
            $minColumns = ['products.id','name', 'images', 'price', 'category_id'];

            // Define allowed filters, searchable columns for where condition
            $allowedFilters = ['name', 'price', 'categories.title','offers.offer_value '];
            $searchColumns = ['name','description','price','stock_count','categories.title','offers.offer_value','specifications'];

            // Define allowed sorting columns for 'orderBy' method
            $allowedSortingColumns = ['products.id','name','description','price','stock','categories.title','offers.offer_value','specifications'];

            // Get filter JSON, search string, pagination parameters, etc. from the request
            $selectColumns = $request->fields == "min" ? $minColumns : $allColumns;
            $filterJson = $request->filters ?? [];
            $searchString = $request->search ?? '';
            $page = $request->page ?? 1;
            $limit = $request->limit ?? 10;
            $sortBy = $request->sort_by ?? 'id';
            $sortDir = $request->sort_dir ?? 'asc';

            // Build the base query for the base table
            $baseQuery = DB::table('products')
                ->whereNull('products.deleted_at')
                ->leftJoin('product_categories as categories', 'products.category_id', '=', 'categories.id')
                ->leftJoin('offers', 'products.offer_id', '=', 'offers.id');
            // You can add your left join queries and additional where conditions here if needed

            // Apply filters, search, and conditions to the base query
            $conditionAppliedQuery = QueryService::addConditionQuery($baseQuery, $allowedFilters, $filterJson, $searchColumns, $searchString);

            // Select the specified columns from the query
            $dataQuery = $conditionAppliedQuery->select($selectColumns);

            // Paginate the results based on the requested page and limit
            $data = QueryService::paginate($dataQuery, $page, $limit, $allowedSortingColumns, $sortBy, $sortDir);
            
            // Process the data to convert images and specifications to arrays
            foreach ($data['data'] as &$product) {
                $product->images = json_decode($product->images);
                $product->specifications = json_decode($product->specifications);
            }

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
            // Find the Product by its ID
            $product = Product::find($id);

            // Check if the Product was found
            if (!$product) {
                // Return a not found response if the Product doesn't exist
                return ResponseService::response('NOT_FOUND', null, "Product not found.");
            }

            $product->images = json_decode($product->images);
            $product->specifications = json_decode($product->specifications);
           
            // Return a successful response with the Product data
            return ResponseService::response('SUCCESS', $product);
        } catch (\Throwable $exception) {
            // Handle exceptions and return an error response
            return ResponseService::response('INTERNAL_SERVER_ERROR', $exception->getMessage());
        }
    }

    
}
