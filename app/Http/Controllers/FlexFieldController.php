<?php

namespace App\Http\Controllers;

use App\Models\Core\FlexField;
use App\Models\Core\FlexFieldOption;
use App\Services\DataValidator;
use App\Services\QueryService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FlexFieldController extends Controller
{
    public function getAll(Request $request)
    {
        try {
            
            // Define the columns groups that want to select
            $allColumns = ['id', 'field_code', 'entity_type','field_label', 'data_type', 'default_value', 'is_mandatory', 'is_enabled', 'is_permanent'];
            $minColumns = ['id', 'field_label'];

            // Define allowed filters, searchable columns for where condition
            $allowedFilters = ['field_label','flexFileds.data_type'];
            $searchColumns = ['field_label', 'data_type', 'entity_type'];

            // Define allowed sorting columns for 'orderBy' method
            $allowedSortingColumns = ['id', 'field_code', 'field_label', 'data_type', 'default_value', 'is_mandatory', 'is_enabled', 'is_permanent'];

            // Get filter JSON, search string, pagination parameters, etc. from the request
            $selectColumns = $request->fields == "min" ? $minColumns : $allColumns;
            $filterJson = $request->filters ?? [];
            $searchString = $request->search ?? '';
            $page = $request->page ?? 1;
            $limit = $request->limit ?? 10;
            $sortBy = $request->sort_by ?? 'id';
            $sortDir = $request->sort_dir ?? 'asc';

            // Build the base query for the base table
            $baseQuery = DB::table('core_flex_fields as flexFileds')
                ->whereNull('deleted_at');
            // You can add your left join queries and additional where conditions here if needed

            // Apply filters, search, and conditions to the base query
            $conditionAppliedQuery = QueryService::addConditionQuery($baseQuery, $allowedFilters, $filterJson, $searchColumns, $searchString);

            // Select the specified columns from the query
            $dataQuery = $conditionAppliedQuery->select($selectColumns);

            // Paginate the results based on the requested page and limit
            $data = QueryService::paginate($dataQuery, $page, $limit, $allowedSortingColumns, $sortBy, $sortDir);

            // Return a successful response with the data
            return ResponseService::response('SUCCESS', $data);
        } catch (\Throwable $exception) {
            // Handle exceptions and return an error response
            return ResponseService::response('INTERNAL_SERVER_ERROR', $exception->getMessage());
        }
    }

    public function getAllOfEntity($entity)
    {
        try {
            $data = FlexField::where('entity_type',$entity)->where('is_enabled', true)->with('valueSet')->get();

            // Return a successful response with the Flex Field data
            return ResponseService::response('SUCCESS', $data);
        } catch (\Throwable $exception) {
            // Handle exceptions and return an error response
            return ResponseService::response('INTERNAL_SERVER_ERROR', $exception->getMessage());
        }
    }

    public function getOne($id)
    {
        try {
            // Find the Flex Field by its ID
            $flexField = FlexField::find($id);

            // Check if the Flex Field was found
            if (!$flexField) {
                // Return a not found response if the Flex Field doesn't exist
                return ResponseService::response('NOT_FOUND', null, "Flex Field not found.");
            }

            // Return a successful response with the Flex Field data
            return ResponseService::response('SUCCESS', $flexField);
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
                'entity_type' => 'required|string|max:255',
                'field_code' => 'required|string|max:255',
                'field_label' => 'required|string|max:255|unique:core_flex_fields,field_label,NULL,id,deleted_at,NULL',
                'data_type' => 'required|string|max:255',
                'value_set' => 'nullable|string',
                'default_value' => 'nullable|string|max:255',
                'is_mandatory' => 'nullable|boolean',
                'is_enabled' => 'nullable|boolean',
                'is_permanent' => 'nullable|boolean',
            ];

            if (!$isCreating) {
                $rules['field_label'] = 'required|string|max:255|unique:core_flex_fields,field_label,' . $id . ',id,deleted_at,NULL';
            }

            // Define custom attribute names for the columns
            $customAttributes = [
                'entity_type' => 'Entity Type',
                'field_code' => 'Field Code',
                'field_label' => 'Field Label',
                'data_type' => 'Data Type',
                'value_set' => 'Value Set',
                'default_value' => 'Default Value',
            ];

            $validator = DataValidator::make($request->all(), $rules, $customAttributes);

            // If validation fails, return a validation error response.
            if ($validator->fails()) {
                return ResponseService::response('VALIDATION_ERROR', $validator->errors(), "Validation Failed");
            }

            // Determine whether you are creating a new Flex Field or updating an existing one

            DB::beginTransaction();
            // Create or update the Flex Field based on the request data
            if ($isCreating) {
                // Create a new Flex Field using the request data
                $flexField = FlexField::create($request->all());
                if ($request->input('data_type') == 'DROPDOWN') {
                    foreach (json_decode($request->input('value_set'),false) as $key => $value) {
                        $flexFieldOptions = new FlexFieldOption();
                        $flexFieldOptions->value = $value;
                        $flexFieldOptions->flex_field_id = $flexField->id;
                        $flexFieldOptions->save();
                    }
                }
                $message = "Flex Field created successfully.";
            } else {
                // Update an existing Flex Field using the request data
                $flexField = FlexField::find($id);
                if (!$flexField) {
                    return ResponseService::response('NOT_FOUND', null, "Flex field not found.");
                }

                $flexField->update($request->all());
                $message = "Flex Field updated successfully.";
            }

            DB::commit();

            // Return a successful response with the Flex Field data and a success message
            return ResponseService::response('SUCCESS', $flexField, $message);
        } catch (\Throwable $exception) {
            DB::rollBack();
            // Handle exceptions and return an error response
            return ResponseService::response('INTERNAL_SERVER_ERROR', $exception->getMessage());
        }
    }

    public function delete($id)
    {
        try {

            // Find the Flex Field by its ID
            $flexField = FlexField::find($id);

            // Check if the Flex Field was found
            if (!$flexField) {
                // Return a not found response if the Flex Field doesn't exist
                return ResponseService::response('NOT_FOUND', null, "Flex Field not found.");
            }

            // Delete the Flex Field
            $flexField->delete();

            // Return a successful response indicating successful deletion
            return ResponseService::response('SUCCESS', "Flex Field deleted successfully.");
        } catch (\Throwable $exception) {
            // Handle exceptions and return an error response
            return ResponseService::response('INTERNAL_SERVER_ERROR', $exception->getMessage());
        }
    }
}
