<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;


class QueryService
{
    /**
     * Add filter conditions and search criteria to a database query.
     *
     * @param  Illuminate\Database\Query\Builder  $query
     * @param  array  $allowedFilters
     * @param  string  $filterJson
     * @param  array  $searchColumns
     * @param  string  $searchString
     * @return Illuminate\Database\Query\Builder
     */
    public static function addConditionQuery($query, $allowedFilters, $filterJson, $searchColumns, $searchString)
    {
        // Check if $filterJson is a string and decode it into an associative array if needed
        $filters = gettype($filterJson) == 'string'
            ? json_decode($filterJson, true)  // Convert JSON string to an associative array
            : $filterJson;  // Use $filterJson directly if it's already an array

        // Now $filters contains the decoded JSON data or the original array
        $likeFilters = [];

        // Apply filters
        foreach ($allowedFilters as $filter) {
            if (isset($filters[$filter])) {
                // Extract filter values, operators, and value types from the filter JSON array.
                $filterValue = $filters[$filter]['v'];  // Value to filter on
                $operator = $filters[$filter]['o'];     // Comparison operator (e.g., '=', '>', '<', '<>', 'LIKE')
                $valueType = $filters[$filter]['t'];    // Value type ('A' for array, 'T' for single value)

                if ($valueType === 'A' && is_array($filterValue)) {
                    // Apply the specified operator for a single value filter with 'LIKE'
                    $query->where($filter, 'like', '%' . $filterValue . '%');
                } elseif ($valueType === 'T') {
                    if ($operator == 'LIKE') {
                        array_push($likeFilters, $filter); // Store the filter for later use
                    } else {
                        // Apply the specified operator for a single value filter
                        $query->where($filter, $operator, $filterValue);
                    }
                }
            }
        }

        // Apply 'LIKE' filters
        if (count($likeFilters) > 0) {
            $query->where(function ($query) use ($likeFilters,$filters) {
                foreach ($likeFilters as $filter) {
                    // Use 'like' operator for each 'LIKE' filter
                    $query->orWhere($filter, 'like', '%' . $filters[$filter]['v'] . '%');
                }
            });
        }

        // Apply search
        if (!empty($searchString)) {
            $query->where(function ($query) use ($searchColumns, $searchString) {
                foreach ($searchColumns as $column) {
                    $query->orWhere($column, 'like', '%' . $searchString . '%');
                }
            });
        }

        return $query;
    }

    /**
     * Paginate the results of a database query.
     *
     * @param  Illuminate\Database\Query\Builder  $query
     * @param  int  $page
     * @param  int  $limit
     * @param  array  $allowedSortingColumns
     * @param  string|null  $sortBy
     * @param  string|null  $sortDir
     * @param  bool  $needPaginationDetail
     * @return Illuminate\Pagination\LengthAwarePaginator|array
     */
    public static function paginate($query, $page, $limit, $allowedSortingColumns, $sortBy, $sortDir, $needPaginationDetail = false)
    {
        // Check if the specified sorting column is allowed
        if (in_array($sortBy, $allowedSortingColumns)) {
            // Validate and set the sorting direction to 'asc' or 'desc'
            $sortDir = in_array(strtolower($sortDir), ['asc', 'desc']) ? $sortDir : 'asc';

            // Apply sorting to the query
            $query->orderBy($sortBy, $sortDir);
        }

        if ($needPaginationDetail) {
            // Return the full paginator with pagination details
            return $query->paginate($limit, ['*'], 'page', $page);
        } else {
            $dataCount = $query->count();

            // Return an array containing paginated items and total count
            return [
                'data' => $query->limit($limit)->offSet(($page - 1) * $limit)->get(),
                'total' => $dataCount,
            ];
        }
    }
}
