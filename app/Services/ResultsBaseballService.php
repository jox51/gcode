<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use App\Models\BaseballData;
use App\Models\Result;
use App\Models\ResultsSummary;
use App\Models\ResultsDetail;
use App\Models\ResultsDetails;

class ResultsBaseballService {

  public function grabResults() {

    // Fetch yesterdays games results and saves to database
    $this->fetchYesterdaysGames();

    $resultsSummary = $this->compareWinners();
  }

  public function fetchYesterdaysGames() {

    // Fetch yesterdays games from database
    $yesterday = Carbon::yesterday('America/New_York')->format('Y-m-d');
    $baseballGames = BaseballData::whereDate('date', '=', $yesterday)->where('algo_rank', '!=', 'J')->get();


    foreach ($baseballGames as $game) {
      // Decode the teams JSON to get matchId
      $teamsData = json_decode($game['teams'], true);
      $matchId = $teamsData['matchId'];

      // Make the API call for each matchId
      $response = $this->fetchMatchDetails($matchId);

      $gameWinner = $response['event']['winnerCode'];

      $teamsData['gameWinner'] = $gameWinner;

      if (isset($response['event']['winnerCode'])) {
        $gameWinner = $response['event']['winnerCode'];

        // Update teamsData with gameWinner
        $teamsData['gameWinner'] = $gameWinner;

        // Update the game model with the new teamsData
        $game->teams = json_encode($teamsData);

        // Save changes back to the database
        $game->save();
      }
    }
  }

  private function fetchMatchDetails($matchId) {
    $response = Http::withHeaders([
      'X-RapidAPI-Key' => env('RAPIDAPI_KEY'),
      'X-RapidAPI-Host' => 'baseballapi.p.rapidapi.com'
    ])->get("https://baseballapi.p.rapidapi.com/api/baseball/match/$matchId");

    if ($response->successful()) {
      return $response->json();
    } else {
      // Handle error or return an empty array/error message
      return ['error' => 'Failed to fetch match details for matchId ' . $matchId];
    }
  }

  public function compareWinners() {
    $yesterday = Carbon::yesterday('America/New_York')->format('Y-m-d');
    $baseballGames = BaseballData::whereDate('date', '=', $yesterday)
      ->where('algo_rank', '!=', 'J')
      ->get();

    $algoRankCounts = $this->initializeAlgoRankCounts();

    foreach ($baseballGames as $game) {
      $this->updateAlgoRankCountsBasedOnGame($game, $algoRankCounts);
    }

    $this->saveResults($algoRankCounts, 'Baseball');
  }


  protected function initializeAlgoRankCounts() {
    // Initialize algo rank counts with available ranks and set their counts to 0
    return array_fill_keys(['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'], ['correct' => 0, 'total' => 0]);
  }

  protected function updateAlgoRankCountsBasedOnGame($game, &$algoRankCounts) {
    $teamsData = json_decode($game->teams, true);
    $gameWinner = $teamsData['gameWinner'];
    $proposedWinner = $game->to_win === 'away' ? 2 : 1; // Assuming 'away' corresponds to 2 and 'home' corresponds to 1

    $algoRankCounts[$game->algo_rank]['total']++;

    if ($proposedWinner == $gameWinner) {
      $algoRankCounts[$game->algo_rank]['correct']++;
    }
  }

  protected function saveResults($algoRankCounts, $sport) {
    // Here, instead of creating a new summary for each day, consider accumulating monthly data.
    // For simplicity, let's assume we're creating new records daily as a placeholder.

    $resultsData = [];
    foreach ($algoRankCounts as $rank => $counts) {
      if ($counts['total'] > 0) {
        $percentage = round(($counts['correct'] / $counts['total']) * 100);
      } else {
        $percentage = 0; // Avoid division by zero
      }

      $resultsData[strtolower($rank) . '_results'] = json_encode([
        'correct' => $counts['correct'],
        'total' => $counts['total'],
        'percentage' => $percentage,
      ]);
    }

    $overallCounts = array_reduce($algoRankCounts, function ($carry, $item) {
      $carry['correct'] += $item['correct'];
      $carry['total'] += $item['total'];
      return $carry;
    }, ['correct' => 0, 'total' => 0]);

    if ($overallCounts['total'] > 0) {
      $overallPercentage = round(($overallCounts['correct'] / $overallCounts['total']) * 100);
    } else {
      $overallPercentage = 0;
    }

    $resultsData['total_results'] = json_encode([
      'correct' => $overallCounts['correct'],
      'total' => $overallCounts['total'],
      'percentage' => $overallPercentage,
    ]);
    $resultsData['sport'] = $sport;
    $resultsData['date'] = Carbon::yesterday('America/New_York')->format('Y-m-d');



    // Assuming you have a model named Result corresponding to the results table
    Result::create($resultsData);
  }
}
