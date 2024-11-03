<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function generateReport(Request $request)
    {
        $filters = $request->input('filters', []);
        $query = DB::table('products'); // Adjust to your table

        // Apply filters to the query
        foreach ($filters as $field => $filter) {
            if (!empty($filter['v'])) {
                $operator = $filter['o'] ?? 'LIKE';
                $value = $filter['v'];

                // Ensure safe filtering
                $query->where($field, $operator, ($operator === 'LIKE') ? "%$value%" : $value);
            }
        }

        // Fetch data
        $data = $query->get();

        // Generate CSV as a streamed response
        $response = new StreamedResponse(function () use ($data) {
            $handle = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($handle, ['Column1', 'Column2', 'Column3']); // Customize column headers

            // Add data rows
            foreach ($data as $row) {
                // Ensure to handle each row correctly
                fputcsv($handle, (array) $row);
            }

            fclose($handle);
        });

        // Set headers for the CSV download
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="report.csv"');

        return $response;
    }
}
