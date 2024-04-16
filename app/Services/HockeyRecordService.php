<?php

namespace App\Services;

use App\Models\HockeyData;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class HockeyRecordService {

  public function fetchAndStoreHockeyData() {


    $this->getHockeyData();

    // Calculate numerology for today's games and store it in the database
    $hockeyNumerology = new HockeyNumerologyService();
    $hockeyNumerology->calculateGameData();
  }



  public function getHockeyData() {

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


    // Merge starting goalie data with game data and store in db
    $updatedGameData = $this->mergeStartingGoalies($gameData, $lineupDataIntegration);
    $this->storeHockeyData($updatedGameData);
  }

  public function fetchRecords($year, $month, $day) {

    $api_key = env('RAPIDAPI_KEY'); // Replace with your actual API key

    $response = Http::withHeaders([
      'X-RapidAPI-Key' => $api_key, // Replace with your actual API key
      'X-RapidAPI-Host' => 'sports-information.p.rapidapi.com',
    ])->get('https://sports-information.p.rapidapi.com/nhl/schedule', [
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

  // SECOND API CALL


  public function fetchStartingLineupData() {
    $todayDate = Carbon::now('America/New_York')->format('Ymd');

    $response = Http::withHeaders([
      'X-RapidAPI-Key' => env('RAPIDAPI_KEY'),
      'X-RapidAPI-Host' => 'tank01-nhl-live-in-game-real-time-statistics-nhl.p.rapidapi.com',
    ])->get("https://tank01-nhl-live-in-game-real-time-statistics-nhl.p.rapidapi.com/getNHLGamesForDate", [
      'gameDate' => $todayDate,
    ]);

    if ($response->successful()) {
      return $response->json();
    } else {
      // Handle error or return an empty array/error message
      return ['error' => 'Failed to fetch additional hockey data'];
    }
  }

  public function filterEvents($responseData) {
    $filteredEvents = [];
    $today = Carbon::now('America/New_York')->startOfDay();

    if (isset($responseData['body'])) {
      foreach ($responseData['body'] as $event) {

        if (isset($event['gameStatus']) && $event['gameStatus'] === "Scheduled") {


          // Convert the startTimestamp to a Carbon instance
          $eventDate = Carbon::createFromTimestamp($event['gameTime_epoch'])->setTimezone('America/New_York')->startOfDay();

          // Check if the event date is equal to today's date
          if ($eventDate->equalTo($today)) {
            $filteredEvents[] = $event;
          }
        }
      }
    }

    return $filteredEvents;
  }

  public function integrateLineupData($filteredEvents) {
    $dataWithLineups = [];
    foreach ($filteredEvents as $event) {

      $lineupDataHome = $this->fetchLineupPlayerData($event['teamIDHome']);
      $lineupDataAway = $this->fetchLineupPlayerData($event['teamIDAway']);

      if (!isset($lineupDataHome['error']) && !isset($lineupDataAway['error'])) {
        $startingGoalieHome = $this->findStartingGoalie($lineupDataHome);
        $startingGoalieAway = $this->findStartingGoalie($lineupDataAway);
        $event['matchupId'] = $event['gameID'];

        $nhlTeams = $this->getTeamFullNames($event['teamIDHome'], $event['teamIDAway']);

        // Directly update the $event array
        if ($startingGoalieHome) {
          $event['homeTeam']['startingGoalie'] = $startingGoalieHome;
          $event['homeTeam']['startingGoalie']['name'] = $nhlTeams['homeTeam'];
        }

        if ($startingGoalieAway) {
          $event['awayTeam']['startingGoalie'] = $startingGoalieAway;
          $event['awayTeam']['startingGoalie']['name'] = $nhlTeams['awayTeam'];
        }
      }

      $dataWithLineups[] = $event;
    }



    return $dataWithLineups;
  }

  public function fetchLineupPlayerData($teamId) {
    $response = Http::withHeaders([
      'X-RapidAPI-Key' => env('RAPIDAPI_KEY'), // Your actual RapidAPI key
      'X-RapidAPI-Host' => 'tank01-nhl-live-in-game-real-time-statistics-nhl.p.rapidapi.com',
    ])->get("https://tank01-nhl-live-in-game-real-time-statistics-nhl.p.rapidapi.com/getNHLTeamRoster", [
      'teamID' => $teamId,
    ]);


    if ($response->successful()) {

      return $response->json();
    } else {
      // Handle error
      return ['error' => 'Failed to fetch lineup data for game ' . $teamId];
    }
  }


  private function findStartingGoalie($lineupData) {

    foreach ($lineupData['body']['roster'] as $player) {

      if ($player['pos'] == "G") {
        return $player;
      }
    }
    // If no goalie found after the loop
    return ['error' => 'No starting goalie found'];
  }

  private function getTeamFullNames($teamIdHome, $teamIdAway) {

    $teams = [];

    $response = Http::withHeaders([
      'X-RapidAPI-Key' => env('RAPIDAPI_KEY'), // Your actual RapidAPI key
      'X-RapidAPI-Host' => 'tank01-nhl-live-in-game-real-time-statistics-nhl.p.rapidapi.com',
    ])->get("https://tank01-nhl-live-in-game-real-time-statistics-nhl.p.rapidapi.com/getNHLTeams");

    if ($response->successful()) {

      $teamNames = $response->json();
      foreach ($teamNames['body'] as $team) {
        if ($team['teamID'] == $teamIdHome) {
          $homeTeamName = $team['teamCity'] . ' ' . $team['teamName'];
          $teams['homeTeam'] = $homeTeamName;
        }
        if ($team['teamID'] == $teamIdAway) {
          $awayTeamName = $team['teamCity'] . ' ' . $team['teamName'];
          $teams['awayTeam'] = $awayTeamName;
        }
      }
      return $teams;
    } else {
      // Handle error
      return ['error' => 'Failed to fetch full team names'];
    }
  }



  public function mergeStartingGoalies($gameData, $lineupDataIntegration) {
    // Loop through each game in $gameData
    foreach ($gameData as &$game) {
      // Find corresponding game in $lineupDataIntegration
      foreach ($lineupDataIntegration as $lineupGame) {

        if ($game['teams']['homeTeam']['name'] === $lineupGame['homeTeam']['startingGoalie']['name']) {
          // Merge starting pitcher data for home team


          $game['teams']['matchId'] = $lineupGame['gameID'];


          if (isset($lineupGame['homeTeam']['startingGoalie'])) {
            $game['teams']['homeTeam']['startingGoalie'] = $lineupGame['homeTeam']['startingGoalie'];
          }
        }
        if ($game['teams']['awayTeam']['name'] === $lineupGame['awayTeam']['startingGoalie']['name']) {
          // Merge starting pitcher data for away team
          if (isset($lineupGame['awayTeam']['startingGoalie'])) {
            $game['teams']['awayTeam']['startingGoalie'] = $lineupGame['awayTeam']['startingGoalie'];
          }
        }
      }
    }
    return $gameData;
  }

  public function storeHockeyData($updatedGameData) {
    foreach ($updatedGameData as $gameData) {
      $game = new HockeyData();
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
