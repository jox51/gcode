<?php

namespace App\Services;

use App\Models\MonthlyResult;
use Carbon\Carbon;
use App\Models\Result;

class ResultsMonthlyService {

  public function grabResults() {
    $last30DaysSummaries = Result::where('date', '>=', Carbon::now()->subDays(30))->get();

    $monthlyData = $this->calculateMonthlyTotals($last30DaysSummaries);
    $this->saveMonthlyResults($monthlyData);
  }

  private function calculateMonthlyTotals($results) {
    $monthlyTotals = [];

    foreach ($results as $result) {
      $sport = $result->sport;

      if (!isset($monthlyTotals[$sport])) {
        $monthlyTotals[$sport] = $this->initializeSportData();
        $monthlyTotals[$sport]['sport'] = $sport; // Add sport key here
      }

      foreach (['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i'] as $rank) {
        $rankResults = json_decode($result->{$rank . '_results'}, true);
        $monthlyTotals[$sport][$rank]['total'] += $rankResults['total'];
        $monthlyTotals[$sport][$rank]['correct'] += $rankResults['correct'];
      }

      $totalResults = json_decode($result->total_results, true);
      $monthlyTotals[$sport]['total']['total'] += $totalResults['total'];
      $monthlyTotals[$sport]['total']['correct'] += $totalResults['correct'];
    }

    // Calculate percentages
    foreach ($monthlyTotals as &$sportData) {
      foreach ($sportData as $rank => &$details) {

        if (is_array($details) && $details['total'] > 0) {
          $details['percentage'] = round(($details['correct'] / $details['total']) * 100);
        } else if (is_array($details)) {
          $details['percentage'] = 0;
        }
      }
    }

    return $monthlyTotals;
  }


  private function initializeSportData() {
    $ranks = array_fill_keys(['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i'], ['total' => 0, 'correct' => 0, 'percentage' => 0]);
    $ranks['total'] = ['total' => 0, 'correct' => 0, 'percentage' => 0];
    return $ranks;
  }

  public function saveMonthlyResults($monthlyTotals) {
    $month = Carbon::now()->startOfMonth(); // Get the start of the current month

    // Create or update the monthly results
    $monthlyResult = MonthlyResult::updateOrCreate(
      ['result_month' => $month],
      [
        'baseball' => $monthlyTotals['Baseball'] ?? null,
        'hockey' => $monthlyTotals['Hockey'] ?? null,
        'tennis' => $monthlyTotals['Tennis'] ?? null,
        // Add other sports similarly
      ]
    );

    return $monthlyResult;
  }
}
