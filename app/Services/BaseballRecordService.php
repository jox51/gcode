<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use App\Models\BaseballData;

class BaseballRecordService {

  public function fetchAndStoreBaseballData() {
    // Fetches data from both APIs and stores it in the database
    // $baseballRecords = new BaseballRecordService();
    // $baseballRecords->getBaseballData();

    $this->getBaseballData();

    // Calculate numerology for today's games and store it in the database
    $baseballNumerology = new BaseballNumerologyService();
    $baseballNumerology->calculateGameData();
  }

  public function getBaseballData() {


    $year = Carbon::now('America/New_York')->year;
    $month = Carbon::now('America/New_York')->month;
    $day = Carbon::now('America/New_York')->day;


    //  First API call to fetch records (Total, Home, Away)
    $response = $this->fetchRecords($year, $month, $day);
    $responseCheck = $this->checkResponseFetchRecords($response);
    $gameData = $this->extractGamesInfo($responseCheck);

    // Second API call to fetch starting lineup data
    $additionalData = $this->fetchStartingLineupData($year, $month, $day);
    $filteredEvents = $this->filterEvents($additionalData);
    $lineupDataIntegration = $this->integrateLineupData($filteredEvents);

    // Merge starting pitcher data with game data and store in db
    $updatedGameData = $this->mergeStartingPitchers($gameData, $lineupDataIntegration);
    $this->storeBaseballData($updatedGameData);
  }

  public function fetchRecords($year, $month, $day) {

    $api_key = env('RAPIDAPI_KEY'); // Replace with your actual API key

    $response = Http::withHeaders([
      'X-RapidAPI-Key' => $api_key, // Replace with your actual API key
      'X-RapidAPI-Host' => 'sports-information.p.rapidapi.com',
    ])->get('https://sports-information.p.rapidapi.com/mlb/schedule', [
      'year' => $year,
      'month' => $month,
      'day' => $day,
    ]);



    if ($response->successful()) {
      $data = $response->json();

      return $data;
    } else {

      return ['error' => 'Failed to fetch records'];
    }
  }

  public function checkResponseFetchRecords($response) {

    $year = Carbon::now('America/New_York')->year;
    $month = Carbon::now('America/New_York')->month;
    $day = Carbon::now('America/New_York')->day;

    $dateKey = sprintf('%d%02d%02d', $year, $month, $day);

    if (isset($response[$dateKey])) {
      $dataForCurrentDate = $response[$dateKey];

      return $dataForCurrentDate;
    } else {
      // Handle the case where no data is found for the current date
      return ['error' => "No records found for $dateKey"];
    }
  }

  public function extractGamesInfo($gamesData) {
    $extractedGamesInfo = [];

    foreach ($gamesData['games'] as $game) {
      $teams = [
        'homeTeam' => null,
        'awayTeam' => null,
      ];

      // Assume odds are the same for all competitions in a game, so we take the first one.
      $odds = !empty($game['competitions'][0]['odds']) ? $game['competitions'][0]['odds'] : [];

      foreach ($game['competitions'][0]['competitors'] as $competitor) {
        $teamType = $competitor['homeAway'] == 'home' ? 'homeTeam' : 'awayTeam';
        $total = '';
        $home = '';
        $away = '';
        foreach ($competitor['records'] as $record) {
          if ($record['type'] == 'total') {
            $total = $record['summary'];
          }
          if ($record['type'] == 'home') {
            $home = $record['summary'];
          }
          if ($record['type'] == 'road') {
            $away = $record['summary'];
          }
        }

        $teams[$teamType] = [
          'name' => $competitor['team']['displayName'],
          'logo' => $competitor['team']['logo'],
          'records' => [
            'total' => $competitor['records'][0]['summary'],
            'home' => $competitor['records'][1]['summary'],
            'away' => $competitor['records'][2]['summary'],
          ],
        ];
      }

      // Convert the ISO date to MM/DD/YYYY format
      $startDate = Carbon::createFromFormat('Y-m-d\TH:i\Z', $game['date'], 'UTC')
        ->timezone('America/New_York')->format('m/d/Y');

      $extractedGamesInfo[] = [
        'name' => $game['name'],
        'date' => $game['date'],
        'startDate' => $startDate,
        'teams' => $teams,
        'odds' => $odds,
      ];
    }

    return $extractedGamesInfo;
  }

  public function fetchStartingLineupData($year, $month, $day) {
    $response = Http::withHeaders([
      'X-RapidAPI-Key' => env('RAPIDAPI_KEY'), // Use your actual RapidAPI key here, stored in your .env for security
      'X-RapidAPI-Host' => 'baseballapi.p.rapidapi.com',
    ])->get("https://baseballapi.p.rapidapi.com/api/baseball/matches/$day/$month/$year");

    if ($response->successful()) {
      return $response->json();
    } else {
      // Handle error or return an empty array/error message
      return ['error' => 'Failed to fetch additional baseball data'];
    }
  }

  public function filterEvents($responseData) {
    $filteredEvents = [];
    $today = Carbon::now('America/New_York')->startOfDay(); // Reset time part to 00:00:00 for comparison


    if (isset($responseData['events'])) {
      foreach ($responseData['events'] as $event) {
        if (
          isset($event['season']['name'], $event['status']['code'], $event['startTimestamp']) &&
          $event['season']['name'] === "MLB 2024" &&
          $event['status']['code'] === 0
        ) {


          // Convert the startTimestamp to a Carbon instance
          $eventDate = Carbon::createFromTimestamp($event['startTimestamp'])->setTimezone('America/New_York')->startOfDay();


          // Check if the event date is equal to today's date
          if ($eventDate->equalTo($today)) {
            $filteredEvents[] = $event;
          }
        }
      }
    }

    return $filteredEvents;
  }

  public function fetchLineupPlayerData($gameId) {
    $response = Http::withHeaders([
      'X-RapidAPI-Key' => env('RAPIDAPI_KEY'), // Your actual RapidAPI key
      'X-RapidAPI-Host' => 'baseballapi.p.rapidapi.com',
    ])->get("https://baseballapi.p.rapidapi.com/api/baseball/match/$gameId/lineups");

    if ($response->successful() && $response->status() === 200) {
      return $response->json();
    } else {
      // Handle error
      return ['error' => 'Failed to fetch lineup data for game ' . $gameId];
    }
  }

  public function integrateLineupData($filteredEvents) {

    $dataWithLineups = [];
    foreach ($filteredEvents as $event) {
      $lineupData = $this->fetchLineupPlayerData($event['id']);
      if (!isset($lineupData['error'])) {
        $startingPitcherHome = $this->findStartingPitcher($lineupData['home']);
        $startingPitcherAway = $this->findStartingPitcher($lineupData['away']);
        $event['matchupId'] = $event['id'];

        // Directly update the $event array
        if ($startingPitcherHome) {
          $event['homeTeam']['startingPitcher'] = $startingPitcherHome;
        }

        if ($startingPitcherAway) {
          $event['awayTeam']['startingPitcher'] = $startingPitcherAway;
        }
      }

      $dataWithLineups[] = $event;
    }



    return $dataWithLineups;
  }

  private function findStartingPitcher($lineupData) {

    foreach ($lineupData['players'] as $player) {


      if (isset($player['position']) && $player['position'] === 'P') {
        return $player;
      } else {
        return ['error' => 'No starting pitcher found'];
      }
    }
  }

  public function mergeStartingPitchers($gameData, $lineupDataIntegration) {
    // Loop through each game in $gameData
    foreach ($gameData as &$game) {
      // Find corresponding game in $lineupDataIntegration
      foreach ($lineupDataIntegration as $lineupGame) {
        if ($game['teams']['homeTeam']['name'] === $lineupGame['homeTeam']['name']) {
          // Merge starting pitcher data for home team

          $game['teams']['matchId'] = $lineupGame['id'];

          if (isset($lineupGame['homeTeam']['startingPitcher'])) {
            $game['teams']['homeTeam']['startingPitcher'] = $lineupGame['homeTeam']['startingPitcher'];
          }
        }
        if ($game['teams']['awayTeam']['name'] === $lineupGame['awayTeam']['name']) {
          // Merge starting pitcher data for away team
          if (isset($lineupGame['awayTeam']['startingPitcher'])) {
            $game['teams']['awayTeam']['startingPitcher'] = $lineupGame['awayTeam']['startingPitcher'];
          }
        }
      }
    }
    return $gameData;
  }

  public function storeBaseballData($updatedGameData) {
    foreach ($updatedGameData as $gameData) {


      if (!isset($gameData['teams']['homeTeam']['startingPitcher']) || !isset($gameData['teams']['awayTeam']['startingPitcher'])) {
        continue;
      }
      $game = new BaseballData();
      $game->name = $gameData['name'];

      // Convert UTC to EST before saving
      $utcDate = Carbon::createFromFormat('Y-m-d\TH:i\Z', $gameData['date'], 'UTC');
      $estDate = $utcDate->setTimezone('America/New_York');

      $game->date = $estDate;
      $game->start_date = $gameData['startDate']; // Assuming you have a start_date_string column
      $game->teams = json_encode($gameData['teams']);
      $game->odds = json_encode($gameData['odds']);
      $game->save();
    }
  }
}
