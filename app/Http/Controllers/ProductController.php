<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\DataValidator;
use App\Services\EntityService;
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
            $allColumns = ['products.id','name','products.description','price','stock_count','images','categories.title AS product_category','offers.offer_value AS offer','specifications'];
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
            $product-> category;
            $product->entity;
           
            // Return a successful response with the Product data
            return ResponseService::response('SUCCESS', $product);
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
               'description' => 'nullable|string|max:255',
               'price' => 'required|numeric',  
               'stock_count' => 'nullable|numeric', 
               'images' => 'nullable|array',  
               'category_id' => 'required|exists:product_categories,id',
               'specifications' => 'nullable' 
           ];
   
           $validator = DataValidator::make($request->all(), $rules);
   
           // If validation fails, return a validation error response.
           if ($validator->fails()) {
               return ResponseService::response('VALIDATION_ERROR', $validator->errors(), "Validation Failed");
           }
   
           // Transform arrays into JSON strings
           $productData = $request->all();
           if (isset($productData['images'])) {
               $productData['images'] = json_encode($productData['images']);
           }
           if (isset($productData['specifications'])) {
               $productData['specifications'] = json_encode($productData['specifications']);
           }
   
           DB::beginTransaction();
   
           if ($isCreating) {
               // Create a new Product using the request data
               $entity = EntityService::store($productData);
               // Add $entity->id to the request data
               $productData['entity_id'] = $entity->id;
   
               $product = Product::create($productData);
               $message = "Product created successfully.";
           } else {
               // Update an existing Product using the request data
               $product = Product::find($id);
               if (!$product) {
                   return ResponseService::response('NOT_FOUND', null, "Product not found.");
               }
   
               EntityService::update($product->entity_id, $productData);
               $product->update($productData);
               $message = "Product updated successfully.";
           }
   
           DB::commit();
   
           // Return a successful response with the Product data and a success message
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
            // Find the Product by its ID
            $product = Product::find($id);

            // Check if the Product was found
            if (!$product) {
                // Return a not found response if the Product doesn't exist
                return ResponseService::response('NOT_FOUND', null, "Product not found.");
            }

            $usedCount = Product::where('product_id', $product->id)->count();

            if ($usedCount > 0) {
                return ResponseService::response('CONFLICT', null, "Unable to delete Product. It's referenced in Product. Update records or resolve conflicts.");
            }

            // Delete the Product
            $product->delete();

            // Return a successful response indicating successful deletion
            return ResponseService::response('SUCCESS', null, "Product deleted successfully.");
        } catch (\Throwable $exception) {
            // Handle exceptions and return an error response
            return ResponseService::response('INTERNAL_SERVER_ERROR', $exception->getMessage());
        }
    }
    
}
