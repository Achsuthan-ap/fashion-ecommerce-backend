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
     * @param  array  $filterJson
     * @param  array  $searchColumns
     * @param  string  $searchString
     * @return Illuminate\Database\Query\Builder
     */
    public static function addConditionQuery($query, $allowedFilters, $filterJson, $searchColumns, $searchString)
    {
        // Apply filters
        foreach ($allowedFilters as $filter) {
            if (isset($filterJson[$filter])) {
                // Extract filter values, operators, and value types from the filter JSON array.
                $filterValue = $filterJson[$filter]['v'];  // Value to filter on
                $operator = $filterJson[$filter]['o'];     // Comparison operator (e.g., '=', '>', '<')
                $valueType = $filterJson[$filter]['t'];    // Value type ('A' for array, 'T' for single value)

                if ($valueType === 'A' && is_array($filterValue)) {
                    // Apply 'whereIn' filter for an array value
                    $query->whereIn($filter, $filterValue);
                } elseif ($valueType === 'T') {
                    // Apply the specified operator for a single value filter
                    $query->where($filter, $operator, $filterValue);
                }
            }
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
    public static function paginate($query, $page, $limit, $allowedSortingColumns, $sortBy , $sortDir, $needPaginationDetail = false)
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
            // Return an array containing paginated items and total count
            return [
                'data' => $query->limit($limit)->offSet(($page - 1) * $limit)->get(),
                'total' => $query->count(),
            ];
        }
    }
}
