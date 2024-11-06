<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use App\Services\DataValidator;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use DB;

class PromotionController extends Controller
{
    public function index()
    {
        $promotions = Promotion::all();
        return response()->json($promotions);
    }

    // Store new promotional image
    public function store(Request $request)
    {
        try{
            $rules = [
                'image_url' => 'required',
            ];
    
            $validator = DataValidator::make($request->all(), $rules);
            if ($validator->fails()) {
                return ResponseService::response('VALIDATION_ERROR', $validator->errors(), "Validation Failed");
            }
            $productData = $request->all();
            if (isset($productData['image_url'])) {
                $productData['image_url'] = json_encode($productData['image_url']);
            }
    
            DB::beginTransaction();
    
            $product = Promotion::create($productData);
            $message = "Image created successfully.";
            DB::commit();
    
            // Return a successful response with the Product data and a success message
            return ResponseService::response('SUCCESS', null, $message);
        } catch (\Throwable $exception) {
            DB::rollBack();
            // Handle exceptions and return an error response
            return ResponseService::response('INTERNAL_SERVER_ERROR', $exception->getMessage());
        }

    }

    // Delete promotional image
    public function destroy($id)
    {
        try{

            $promotion = Promotion::findOrFail($id);

            if (!$promotion) {
                // Return a not found response if the Product doesn't exist
                return ResponseService::response('NOT_FOUND', null, "Product not found.");
            }
    
            $promotion->delete();
    
            // Return a successful response indicating successful deletion
            return ResponseService::response('SUCCESS', null, "Product deleted successfully.");
        } catch (\Throwable $exception) {
            // Handle exceptions and return an error response
            return ResponseService::response('INTERNAL_SERVER_ERROR', $exception->getMessage());
        }
    }
}
