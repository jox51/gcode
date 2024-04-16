<?php

namespace App\Services;

use App\Models\HockeyData;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use App\Models\TennisRecord;
use App\Services\TennisNumerologyService;

class TennisRecordService {

  public function fetchAndStoreTennisData() {

    $this->getTennisData();

    // Calculate numerology for today's games and store it in the database
    $tennisNumerology = new TennisNumerologyService();
    $tennisNumerology->fetchTodaysGames();
  }



  public function getTennisData() {

    // FIRST API CALL TO FETCH TODAYS MATCHES
    $atpType = 'atp';
    $wtaType = 'wta';
    $todaysFixturesATP = $this->getFixtures($atpType);
    $todaysFixturesWTA = $this->getFixtures($wtaType);

    $updatedATPFixtures = $this->fetchPlayerRecords($todaysFixturesATP, $atpType);
    $updatedWTAFixtures = $this->fetchPlayerRecords($todaysFixturesWTA, $wtaType);

    // Merge ATP and WTA data
    $mergedFixtures = array_merge($updatedATPFixtures, $updatedWTAFixtures);

    // Store data to the database
    $this->storeTennisData($mergedFixtures);


    // SECOND API CALL TO RETRIEVE BIRTHDAYS
    $this->fetchTennisDataForToday();
  }

  public function getFixtures($type = 'atp') {

    $api_key = env('RAPIDAPI_KEY');
    $today = Carbon::now('America/New_York')->format('Y-m-d');

    $response = Http::withHeaders([
      'X-RapidAPI-Key' => $api_key,
      'X-RapidAPI-Host' => 'tennis-api-atp-wta-itf.p.rapidapi.com',
    ])->get("https://tennis-api-atp-wta-itf.p.rapidapi.com/tennis/v2/$type/fixtures/$today?pageSize=100&pageNo=1");

    if ($response->successful()) {
      $data = $response->json();

      // Filter out doubles matches
      $filteredData = array_filter($data['data'], function ($fixture) {
        return !strpos($fixture['player1']['name'], '/') && !strpos($fixture['player2']['name'], '/');
      });

      return array_values($filteredData);
    } else {
      return ['error' => 'Failed to fetch records'];
    }
  }

  public function fetchPlayerRecords($fixtures, $type) {
    $year = Carbon::now('America/New_York')->format('Y');


    foreach ($fixtures as &$fixture) {
      $player1Id = $fixture['player1Id'];
      $player2Id = $fixture['player2Id'];

      $player1Data = $this->fetchRecord($player1Id, $type);
      $player2Data = $this->fetchRecord($player2Id, $type);

      if (isset($player1Data['data'][$year]['level']['total'])) {
        $fixture['player1Record'] = $player1Data['data'][$year]['level']['total'];
        $fixture['type'] = $type;
      }
      if (isset($player2Data['data'][$year]['level']['total'])) {
        $fixture['player2Record'] = $player2Data['data'][$year]['level']['total'];
        $fixture['type'] = $type;
      }
    }
    return $fixtures;
  }

  public function fetchRecord($playerId, $type = 'atp') {
    $api_key = env('RAPIDAPI_KEY');

    $response = Http::withHeaders([
      'X-RapidAPI-Key' => $api_key,
      'X-RapidAPI-Host' => 'tennis-api-atp-wta-itf.p.rapidapi.com',
    ])->get("https://tennis-api-atp-wta-itf.p.rapidapi.com/tennis/v2/$type/player/perf-breakdown/$playerId");

    if ($response->successful()) {
      return $response->json();
    } else {
      return ['error' => 'Failed to fetch player record'];
    }
  }

  public function storeTennisData($fixtures) {
    foreach ($fixtures as $fixture) {
      // Convert ISO 8601 date format to MySQL datetime format
      $formattedDate = new Carbon($fixture['date']);
      $mysqlFormattedDate = $formattedDate->format('Y-m-d H:i:s');

      if (!isset($fixture['player1Record']) || !isset($fixture['player2Record']) || !isset($fixture['type'])) {
        continue;
      }


      TennisRecord::create([
        'fixture_id' => $fixture['id'],
        'date' =>   $mysqlFormattedDate,
        'player1' => json_encode($fixture['player1']),
        'player2' => json_encode($fixture['player2']),
        'player1_id' => $fixture['player1Id'],
        'player2_id' => $fixture['player2Id'],
        'tournament_id' => $fixture['tournamentId'],
        'player1_record' => json_encode($fixture['player1Record'] ?? []),
        'player2_record' => json_encode($fixture['player2Record'] ?? []),
        'type' => $fixture['type'], // Assuming type (ATP/WTA) is determined and stored in the fixture array
      ]);
    }
  }

  public function fetchTennisDataForToday() {
    $today = Carbon::now('America/New_York')->format('Y-m-d');

    // Fetch records from the database that match today's date
    $tennisMatchesToday = TennisRecord::whereDate('date', '=', $today)->get();


    foreach ($tennisMatchesToday as $match) {
      // For each match, make an API call to get additional data
      $this->fetchAdditionalTennisData($match);
    }
  }

  private function fetchAdditionalTennisData($match) {
    $apiKey = env('RAPIDAPI_KEY');
    // Fetch and process data for player1
    $player1 = json_decode($match->player1);
    $player1Name = $player1->name;
    $this->fetchAndProcessPlayerData($player1Name, $apiKey, $match, 'player1');

    // Fetch and process data for player2
    $player2 = json_decode($match->player2);
    $player2Name = $player2->name;
    $this->fetchAndProcessPlayerData($player2Name, $apiKey, $match, 'player2');
  }

  private function fetchAndProcessPlayerData($playerName, $apiKey, $match, $playerKey) {
    $response = Http::withHeaders([
      'X-RapidAPI-Key' => $apiKey,
      'X-RapidAPI-Host' => 'tennisapi1.p.rapidapi.com'
    ])->get("https://tennisapi1.p.rapidapi.com/api/tennis/search/$playerName");

    if ($response->successful()) {
      $data = $response->json();
      // Process the data as needed
      $this->processPlayerData($data, $match, $playerKey);
    } else {
      // Handle errors or log them as needed
      logger()->error("Failed to fetch tennis data for player: {$playerName}");
    }
  }

  private function processPlayerData($data, $match, $playerKey) {
    $apiKey = env('RAPIDAPI_KEY');

    // Some error handling since some of the ids were missing
    if (!isset($data['results'][0]['entity']['id'])) {
      TennisRecord::destroy($match->id);
      logger()->error("No player id available for {$playerKey} with ID: {$match->{$playerKey . '_id'}}");
      return;
    }
    $playerId = $data['results'][0]['entity']['id'];


    $response = Http::withHeaders([
      'X-RapidAPI-Key' => $apiKey,
      'X-RapidAPI-Host' => 'tennisapi1.p.rapidapi.com'
    ])->get("https://tennisapi1.p.rapidapi.com/api/tennis/team/$playerId");

    // Handle unsuccessful response early
    if (!$response->successful()) {
      logger()->error("Failed to fetch player data for player ID: {$playerId}");
      return ['error' => 'Failed to fetch player data'];
    }

    $playerData = $response->json();

    // Check for the presence of required data early
    if (!isset($playerData['team']['playerTeamInfo'])) {
      logger()->error("No player team info available for {$playerKey} with ID: {$match->{$playerKey . '_id'}}");
      return;
    }

    // Merge with existing data
    $existingPlayerData = json_decode($match->{$playerKey}, true) ?? [];
    $playerTeamInfo = $playerData['team']['playerTeamInfo'];
    $updatedPlayerData = array_merge($existingPlayerData, $playerTeamInfo);

    // Save the updated data back to the match
    $match->{$playerKey} = json_encode($updatedPlayerData);
    $match->save();
  }
}
