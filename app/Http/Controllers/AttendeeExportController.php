<?php

namespace App\Http\Controllers;

use App\Exports\RegistrantsExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AttendeeExportController extends Controller
{
    public function export(Request $request)
    {
        $filters = $request->only(['q', 'status', 'register_date']);

        $filename = 'Conference__Only__Register_' . now()->timezone('Asia/Bangkok')->format('Y-m-d') . '.xlsx';

        return Excel::download(new RegistrantsExport($filters), $filename);
    }
}
