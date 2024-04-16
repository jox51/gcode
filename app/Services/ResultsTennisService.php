<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use App\Models\TennisRecord;
use App\Models\Result;


class ResultsTennisService {
  public function grabTennisResults() {

    $this->fetchYesterdaysGames();
    $resultsSummary = $this->compareWinners();
  }

  public function fetchYesterdaysGames() {

    // Fetch yesterdays games from database
    $startOfDay = Carbon::yesterday('America/New_York')->startOfDay();
    $endOfDay = Carbon::yesterday('America/New_York')->endOfDay();

    // Query to get records matching today's date
    $tennisGames = TennisRecord::whereBetween('date', [$startOfDay, $endOfDay])->get();


    foreach ($tennisGames as $game) {

      $player1Data = json_decode($game['player1'], true);
      $player2Data = json_decode($game['player2'], true);

      $player1Id = $game['player1_id'];
      $player2Id = $game['player2_id'];
      $type = $game['type'];

      // Make the API call for each matchId
      $response = $this->fetchMatchDetails($player1Id, $player2Id, $type);

      // $gameWinner = $response['event']['winnerCode'];
      $gameWinner = '';

      if (!isset($response['data'][0]['match_winner'])) {
        TennisRecord::destroy($game['id']);
        continue;
      }

      if ($response['data'][0]['match_winner'] === $player1Id) {
        $gameWinner = 'away';
        $player1Data['gameWinner'] = $gameWinner;
        $player2Data['gameWinner'] = null;
        // Update the game model with the new teamsData
        $game->player1 = json_encode($player1Data);
        $game->player2 = json_encode($player2Data);

        // Save changes back to the database
        $game->save();
      }
      if ($response['data'][0]['match_winner'] === $player2Id) {
        $gameWinner = 'home';
        $player2Data['gameWinner'] = $gameWinner;
        $player1Data['gameWinner'] = null;
        $game->player2 = json_encode($player2Data);
        $game->player1 = json_encode($player1Data);

        // Save changes back to the database
        $game->save();
      }
    }
  }

  private function fetchMatchDetails($player1Id, $player2Id, $type) {
    $response = Http::withHeaders([
      'X-RapidAPI-Key' => env('RAPIDAPI_KEY'),
      'X-RapidAPI-Host' => 'tennis-api-atp-wta-itf.p.rapidapi.com'
    ])->get("https://tennis-api-atp-wta-itf.p.rapidapi.com/tennis/v2/$type/h2h/matches/$player1Id/$player2Id/");

    if ($response->successful()) {
      return $response->json();
    } else {
      // Handle error or return an empty array/error message
      return ['error' => 'Failed to fetch match details for playerid ' . $player1Id . ' and ' . $player2Id];
    }
  }

  public function compareWinners() {
    // Fetch yesterdays games from database
    $startOfDay = Carbon::yesterday('America/New_York')->startOfDay();
    $endOfDay = Carbon::yesterday('America/New_York')->endOfDay();

    // Query to get records matching today's date
    $tennisGames = TennisRecord::whereBetween('date', [$startOfDay, $endOfDay])->where('algo_rank', '!=', 'J')
      ->get();;

    $algoRankCounts = $this->initializeAlgoRankCounts();

    foreach ($tennisGames as $game) {
      $this->updateAlgoRankCountsBasedOnGame($game, $algoRankCounts);
    }

    $this->saveResults($algoRankCounts, 'Tennis'); // Check correct sport
  }


  protected function initializeAlgoRankCounts() {
    // Initialize algo rank counts with available ranks and set their counts to 0
    return array_fill_keys(['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'], ['correct' => 0, 'total' => 0]);
  }

  protected function updateAlgoRankCountsBasedOnGame($game, &$algoRankCounts) {

    $player1 = json_decode($game['player1'], true);
    $player2 = json_decode($game['player2'], true);

    $gameWinner = $player1['gameWinner'] !== null ? $player1['gameWinner'] : $player2['gameWinner'];
    $proposedWinner = $game->to_win === 'away' ? 'away' : 'home';
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
