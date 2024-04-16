<?php

namespace App\Http\Controllers;

use App\Models\MonthlyResult;
use App\Models\Result;
use App\Services\ResultsMonthlyService;
use Carbon\Carbon;
use Inertia\Inertia;

class ResultsMonthlyController extends Controller {
  public function create() {


    // $monthlyResults = new ResultsMonthlyService();
    // $totalResults = $monthlyResults->grabResults();
    // dd($totalResults);
    // Get the first day of the current month
    $startOfMonth = Carbon::now()->startOfMonth();

    // Get the last day of the current month
    $endOfMonth = Carbon::now()->endOfMonth();

    // Query the MonthlyResult model for entries within the current month
    $monthlyResults = MonthlyResult::where('result_month', '>=', $startOfMonth)
      ->where('result_month', '<=', $endOfMonth)
      ->get()->toArray();


    return Inertia::render('ResultsMonthly', [
      'monthlyResults' => $monthlyResults,
    ]);
  }
}
