<?php

namespace App\Services;

use App\Http\Utils\CalculationUtils;
use App\Http\Utils\Constants;
use App\Http\Utils\ZodiacUtils;
use App\Models\HockeyData;
use App\Models\SoulMate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use App\Models\TennisRecord;

class TennisNumerologyService {
  public function fetchTodaysGames() {

    $startOfDay = Carbon::today('America/New_York')->startOfDay();
    $endOfDay = Carbon::today('America/New_York')->endOfDay();

    // Query to get records matching today's date
    $tennisData = TennisRecord::whereBetween('date', [$startOfDay, $endOfDay])->get()->toArray();

    $processedData = $this->calculateEventNumerologyCalcs($tennisData);
    $tennisRecordsData = $this->calculateTeamRecordsNumerology($processedData);
    $tennisPlayersNumerologyData = $this->calculatePlayersNumerology($tennisRecordsData);

    $tennisDataWithRanks = $this->calculateRankings($tennisPlayersNumerologyData);

    $this->determineAlgoRank($tennisDataWithRanks);
  }

  public function calculateEventNumerologyCalcs($tennisData) {

    foreach ($tennisData as &$game) {
      $game['date'] = Carbon::createFromFormat('Y-m-d H:i:s', $game['date'])->format('Y-m-d');


      $gameBirthdayParts = explode('-', $game['date']);
      if (count($gameBirthdayParts) == 3) {
        list($eventYear, $eventMonth, $eventDay) = $gameBirthdayParts;


        //Event day chinese zodiac
        $game['event_data']['event_chin_zodiac'] = ZodiacUtils::getJustChinZodiac($eventYear, $eventMonth, $eventDay);

        $game['event_data']['event_day_calculations'] = CalculationUtils::birthdaySumCalculators($eventMonth, $eventDay, $eventYear);

        $eventCalculations = $game['event_data']['event_day_calculations'];
        $gameRawLifepath = $eventCalculations['sumMonth'] + $eventCalculations['sumDay'] + $eventCalculations['sumYear'];

        $eventResLP = CalculationUtils::calculateTotal($gameRawLifepath);
        $eventResBday = CalculationUtils::calculateTotal($eventCalculations['sumDay']);

        $game['event_data']['event_lp'] = $eventResLP;
        $game['event_data']['event_bday'] = $eventResBday;
      } else {
        $game['event_data']['event_day_calculations'] = 'Invalid event day format';
      }
    }

    return $tennisData;
  }

  public function calculateTeamRecordsNumerology($gameData) {
    // $homeResLP = CalculationUtils::calculateTotal($homeRawLifepath);

    foreach ($gameData as &$game) {

      $player1 = json_decode($game['player1_record'], true);
      $player2 = json_decode($game['player2_record'], true);



      // Away team
      // $atAwayRecord = $player1['awayTeam']['records']['away'];
      // $atHomeRecord = $player1['awayTeam']['records']['home'];
      // $atTotalRecord = $player1['awayTeam']['records']['total'];

      // Home Team
      // $htAwayRecord = $player2['homeTeam']['records']['away'];
      // $htHomeRecord = $player2['homeTeam']['records']['home'];
      // $htTotalRecord = $player2['homeTeam']['records']['total'];

      // Break down the records into individual parts (AWAY TEAM)
      // $atAwayRecordParts = explode('-', $atAwayRecord);
      // $atHomeRecordParts = explode('-', $atHomeRecord);
      // $atTotalRecordParts = explode('-', $atTotalRecord);


      // list($atAwayWins, $awayLosses) = $atAwayRecordParts;
      // list($atHomeWins, $homeLosses) = $atHomeRecordParts;
      // list($atTotalWins, $totalLosses) = $atTotalRecordParts;


      // Break down the records into individual parts (HOME TEAM)
      // $htAwayRecordParts = explode('-', $htAwayRecord);
      // $htHomeRecordParts = explode('-', $htHomeRecord);
      // $htTotalRecordParts = explode('-', $htTotalRecord);


      // list($htAwayWins, $awayLosses) = $htAwayRecordParts;
      // list($htHomeWins, $homeLosses) = $htHomeRecordParts;
      // list($htTotalWins, $totalLosses) = $htTotalRecordParts;

      if (!isset($player1['aw']) || !isset($player2['aw'])) {
        continue;
      }
      $htTotalWins = $player2['aw'];
      $atTotalWins = $player1['aw'];


      // Calculate the life path for the records
      $htTotalWinsLP = CalculationUtils::calculateTotal($htTotalWins);
      // $htHomeWinsLP = CalculationUtils::calculateTotal($htHomeWins);
      // $htAwayWinsLP = CalculationUtils::calculateTotal($htAwayWins);

      $atTotalWinsLP = CalculationUtils::calculateTotal($atTotalWins);
      // $atHomeWinsLP = CalculationUtils::calculateTotal($atHomeWins);
      // $atAwayWinsLP = CalculationUtils::calculateTotal($atAwayWins);


      $game['homeTeam_records_lp'] = [
        'total' => $htTotalWinsLP,
        // 'home' => $htHomeWinsLP,
        // 'away' => $htAwayWinsLP,
      ];

      $game['awayTeam_records_lp'] = [
        'total' => $atTotalWinsLP,
        // 'home' => $atHomeWinsLP,
        // 'away' => $atAwayWinsLP,
      ];
    }

    return $gameData;
  }

  public function calculatePlayersNumerology($gameData) {

    // $filteredGameData = array_filter($gameData, function ($game) {
    //   $player1 = json_decode($game['player1'], true);
    //   $player2 = json_decode($game['player2'], true);
    //   return isset($player1['birthDateTimestamp']) && isset($player2['birthDateTimestamp']);
    // });

    foreach ($gameData as &$game) {

      // Extract the last two digits of the year from start_date
      $yearLastTwoDigits = substr($game['date'], -8, 2);


      foreach (['player2', 'player1'] as $teamType) {
        $team = json_decode($game[$teamType], true);

        // Check if startingPitcher and dateOfBirthTimestamp exist
        if (isset($team['birthDateTimestamp'])) {
          $timestamp = $team['birthDateTimestamp'];

          // Convert Unix timestamp to Carbon instance and format to MM/DD/YYYY
          $dateOfBirth = Carbon::createFromTimestamp($timestamp)->format('m-d-Y');
          $birthDay = Carbon::createFromTimestamp($timestamp)->format('d'); // Extract the day

          // Compare the pitcher's birth day with the last two digits of the start_date year
          $isBorndaySameLastTwo = $birthDay == $yearLastTwoDigits;

          // Add the comparison result to the startingPitcher['player'] array
          $team['isBorndaySameLastTwo'] = $isBorndaySameLastTwo;

          $playerBirthdayParts = explode('-', $dateOfBirth);
          list($birthMonth, $birthDay, $birthYear) = $playerBirthdayParts;

          $calculationUtilsPlayer = new CalculationUtils("$birthMonth-$birthDay");
          // Store only the hasBirthdayPassed value
          $team['hasBirthdayPassed'] = $calculationUtilsPlayer->hasBirthdayPassed;


          //Pitcher chinese zodiac
          $playerZodiac = ZodiacUtils::getJustChinZodiac($birthYear, $birthMonth, $birthDay);
          $team['zodiac'] = $playerZodiac;

          // Pitcher Enemy/Friendly Years
          $playerFriendlyEnemyYears = CalculationUtils::calculateFriendlyEnemyYears($birthYear, $birthMonth, $birthDay);

          // Check enemy signs
          // Determine zodiac relationship
          $team['zodiacRelationship'] = self::getZodiacRelationship($playerZodiac, $game['event_data']['event_chin_zodiac'], Constants::chinAstroEnemySigns());

          // Calculate soulmate days
          // Check soulmate day for home participant
          $playerSoulmateInfo = self::checkSoulmateDay($game['date'], $dateOfBirth);
          $team['isSoulmateDay'] = $playerSoulmateInfo['isSoulmateDay'];

          $team['soulmateDate'] = $playerSoulmateInfo['soulmateDate'];

          $team['playerFriendlyEnemyYears'] = $playerFriendlyEnemyYears;

          $team['playerBirthdayCalculations'] = CalculationUtils::birthdaySumCalculators($birthMonth, $birthDay, $birthYear);

          $playerCalculations = $team['playerBirthdayCalculations'];
          $playerRawLifepath = $playerCalculations['sumMonth'] + $playerCalculations['sumDay'] + $playerCalculations['sumYear'];


          // Calculate personal year
          $playerPersonalYear = CalculationUtils::calculatePersonalYear($playerCalculations['sumMonth'], $playerCalculations['sumDay'], $calculationUtilsPlayer->hasBirthdayPassed);

          $team['personalYear'] = $playerPersonalYear;


          $playerResLP = CalculationUtils::calculateTotal($playerRawLifepath);
          $playerResBday = CalculationUtils::calculateTotal($playerCalculations['sumDay']);

          $team['player_lp'] = $playerResLP;
          $team['player_bday'] = $playerResBday;


          $team['dateOfBirth'] = $dateOfBirth;
          $game[$teamType] = json_encode($team);
        }
      }
    }

    return $gameData;
  }

  protected static function checkSoulmateDay($eventDate, $participantBirthday) {
    $birthday = Carbon::createFromFormat('m-d-Y', $participantBirthday)->format('F j');
    $eventDateFormatted = Carbon::createFromFormat('Y-m-d', $eventDate)->format('F j');



    $soulmateEntry = SoulMate::where('date', $birthday)->first();


    if ($soulmateEntry && !empty($soulmateEntry->soul_mates)) {
      $soulmateDates = json_decode($soulmateEntry->soul_mates);

      if (in_array($eventDateFormatted, $soulmateDates)) {
        return ['isSoulmateDay' => true, 'soulmateDate' => $eventDateFormatted]; // Return true and the matching soulmate date
      }
    }

    return ['isSoulmateDay' => false, 'soulmateDate' => null]; // Return false and null if no soulmate date is found
  }

  protected static function getZodiacRelationship($participantSign, $eventSign, $enemySigns) {
    if ($participantSign == $eventSign) {
      return 'friendly';
    } elseif (isset($enemySigns[$participantSign]) && $enemySigns[$participantSign] == $eventSign) {
      return 'enemy';
    } else {
      return 'neutral';
    }
  }

  public function calculateRankings($gameData) {
    foreach ($gameData as $key => &$game) {

      // Default to false
      $game['ranking_parameters']['home']['totalWinsVsEventDayLP'] = false;
      $game['ranking_parameters']['away']['totalWinsVsEventDayLP'] = false;
      // $game['ranking_parameters']['home']['homeWinsVsEventDayLP'] = false;
      // $game['ranking_parameters']['away']['awayWinsVsEventDayLP'] = false;
      $game['ranking_parameters']['home']['oneUnderTotalWinsVsEventDayLP'] = false;
      $game['ranking_parameters']['away']['oneUnderTotalWinsVsEventDayLP'] = false;
      // $game['ranking_parameters']['home']['oneUnderHomeWinsVsEventDayLP'] = false;
      // $game['ranking_parameters']['away']['oneUnderAwayWinsVsEventDayLP'] = false;
      $game['ranking_parameters']['home']['isPlayerEnemyYr'] = false;
      $game['ranking_parameters']['away']['isPlayerEnemyYr'] = false;
      $game['ranking_parameters']['home']['bothPlayersEnemyYr'] = false;
      $game['ranking_parameters']['away']['bothPlayersEnemyYr'] = false;
      $game['ranking_parameters']['home']['isHomeFriendlyAwayEnemy'] = false;
      $game['ranking_parameters']['away']['isAwayEnemyHomeFriendly'] = false;
      $game['ranking_parameters']['home']['isHomeEnemyAwayFriendly'] = false;
      $game['ranking_parameters']['away']['isAwayFriendlyHomeEnemy'] = false;
      $game['ranking_parameters']['home']['isPlayerSevenYear'] = false;
      $game['ranking_parameters']['away']['isPlayerSevenYear'] = false;
      $game['ranking_parameters']['home']['isPlayerOneDayNine'] = false;
      $game['ranking_parameters']['away']['isPlayerOneDayNine'] = false;
      $game['ranking_parameters']['home']['isPlayerNineDayOne'] = false;
      $game['ranking_parameters']['away']['isPlayerNineDayOne'] = false;
      $game['ranking_parameters']['home']['isPlayerBDLastTwo'] = false;
      $game['ranking_parameters']['away']['isPlayerBDLastTwo'] = false;

      if (!isset($game['player2']) || !isset($game['player1'])) {
        if (isset($game['id'])) {
          TennisRecord::destroy($game['id']);
        }
        // Remove from the current data set
        unset($gameData[$key]);
        continue;
      }

      // json decode the player data
      $game['player2'] = json_decode($game['player2'], true);
      $game['player1'] = json_decode($game['player1'], true);

      if (!isset($game['player2']['zodiacRelationship']) || !isset($game['player1']['zodiacRelationship']) || !isset($game['homeTeam_records_lp']) || !isset($game['awayTeam_records_lp'])) {

        if (isset($game['id'])) {
          TennisRecord::destroy($game['id']);
        }
        // Remove from the current data set
        unset($gameData[$key]);
        continue;
      }

      // Compare Total Wins vs Event LP values
      if ($game['homeTeam_records_lp']['total'] == $game['event_data']['event_lp']) {
        $game['ranking_parameters']['home']['totalWinsVsEventDayLP'] = true;
      }
      if ($game['awayTeam_records_lp']['total'] == $game['event_data']['event_lp']) {
        $game['ranking_parameters']['away']['totalWinsVsEventDayLP'] = true;
      }

      // Compare Home/Away Wins vs Event LP values
      // if ($game['homeTeam_records_lp']['home'] == $game['event_data']['event_lp']) {
      //   $game['ranking_parameters']['home']['homeWinsVsEventDayLP'] = true;
      // }
      // if ($game['awayTeam_records_lp']['away'] == $game['event_data']['event_lp']) {
      //   $game['ranking_parameters']['away']['awayWinsVsEventDayLP'] = true;
      // }

      // Total Record One Under vs Event LP values
      if ($game['homeTeam_records_lp']['total'] == ($game['event_data']['event_lp'] - 1)) {
        $game['ranking_parameters']['home']['oneUnderTotalWinsVsEventDayLP'] = true;
      }
      if ($game['awayTeam_records_lp']['total'] == ($game['event_data']['event_lp'] - 1)) {
        $game['ranking_parameters']['away']['oneUnderTotalWinsVsEventDayLP'] = true;
      }

      //  Home/Away Wins One Under vs Event LP values
      // if ($game['homeTeam_records_lp']['home'] == ($game['event_data']['event_lp'] - 1)) {
      //   $game['ranking_parameters']['home']['oneUnderHomeWinsVsEventDayLP'] = true;
      // }
      // if ($game['awayTeam_records_lp']['away'] == ($game['event_data']['event_lp'] - 1)) {
      //   $game['ranking_parameters']['away']['oneUnderAwayWinsVsEventDayLP'] = true;
      // }

      // Pitcher Enemy Year
      if ($game['player2']['zodiacRelationship'] == 'enemy') {
        $game['ranking_parameters']['home']['isPlayerEnemyYr'] = true;
      }
      if ($game['player1']['zodiacRelationship'] == 'enemy') {
        $game['ranking_parameters']['away']['isPlayerEnemyYr'] = true;
      }



      // Both Pitchers Enemy Year
      if ($game['player2']['zodiacRelationship'] == 'enemy' && $game['player2']['zodiacRelationship'] == 'enemy') {
        $game['ranking_parameters']['home']['bothPlayersEnemyYr'] = true;
        $game['ranking_parameters']['away']['bothPlayersEnemyYr'] = true;
      }

      // Home Pitcher Friendly/Away Pitcher Enemy
      if ($game['player2']['zodiacRelationship'] == 'friendly' && $game['player1']['zodiacRelationship'] == 'enemy') {
        $game['ranking_parameters']['home']['isHomeFriendlyAwayEnemy'] = true;
        $game['ranking_parameters']['away']['isAwayEnemyHomeFriendly'] = true;
      }

      // Home Pitcher Enemy/Away Pitcher Friendly
      if ($game['player2']['zodiacRelationship'] == 'enemy' && $game['player1']['zodiacRelationship'] == 'friendly') {
        $game['ranking_parameters']['home']['isHomeEnemyAwayFriendly'] = true;
        $game['ranking_parameters']['away']['isAwayFriendlyHomeEnemy'] = true;
      }

      // Pitcher 7 year cycle
      if ($game['player2']['personalYear']['currPersonalYear'] == 7) {
        $game['ranking_parameters']['home']['isPlayerSevenYear'] = true;
      }
      if ($game['player1']['personalYear']['currPersonalYear'] == 7) {
        $game['ranking_parameters']['away']['isPlayerSevenYear'] = true;
      }

      // Is it a 9 day and 1 pitcher

      if ($game['player2']['player_lp']  === 1 && ($game['event_data']['event_lp'] === 9)) {
        $game['ranking_parameters']['home']['isPlayerOneDayNine'] = true;
      }
      if ($game['player1']['player_lp']  === 1 && ($game['event_data']['event_lp'] === 9)) {
        $game['ranking_parameters']['away']['isPlayerOneDayNine'] = true;
      }

      // Is it a 1 day and 9 player

      if ($game['player2']['player_lp']  === 9 && ($game['event_data']['event_lp'] === 1)) {
        $game['ranking_parameters']['home']['isPlayerNineDayOne'] = true;
      }
      if ($game['player1']['player_lp']  === 9 && ($game['event_data']['event_lp'] === 1)) {
        $game['ranking_parameters']['away']['isPlayerNineDayOne'] = true;
      }

      // Is bornday same as last two digits of year
      if ($game['player2']['isBorndaySameLastTwo']) {
        $game['ranking_parameters']['home']['isPlayerBDLastTwo'] = true;
      }
      if ($game['player1']['isBorndaySameLastTwo']) {
        $game['ranking_parameters']['away']['isPlayerBDLastTwo'] = true;
      }
    }




    return $gameData;
  }

  public function determineAlgoRank($gameData) {

    foreach ($gameData as &$game) {

      $game['algo_rank'] = 'J'; // Default to J
      $game['to_win'] = ''; // default to empty string
      $game['auto_over'] = false; // default to false

      // parameters for A rank
      // Total Wins equal to Event LP, other team to win
      if ($game['ranking_parameters']['home']['totalWinsVsEventDayLP'] && !$game['ranking_parameters']['away']['totalWinsVsEventDayLP']) {
        $game['algo_rank'] = 'A';
        $game['to_win'] = 'away';
      }

      // Total Wins equal to Event LP, home team to win
      if ($game['ranking_parameters']['away']['totalWinsVsEventDayLP'] && !$game['ranking_parameters']['home']['totalWinsVsEventDayLP']) {
        $game['algo_rank'] = 'A';
        $game['to_win'] = 'home';
      }

      // Home Wins equal to Event LP, other team to win, rank B
      // if ($game['ranking_parameters']['home']['homeWinsVsEventDayLP'] && !$game['ranking_parameters']['away']['awayWinsVsEventDayLP']) {
      //   $game['algo_rank'] = 'B';
      //   $game['to_win'] = 'away';
      // }

      // Away Wins equal to Event LP, home team to win, rank B
      // if ($game['ranking_parameters']['away']['awayWinsVsEventDayLP'] && !$game['ranking_parameters']['home']['homeWinsVsEventDayLP']) {
      //   $game['algo_rank'] = 'B';
      //   $game['to_win'] = 'home';
      // }

      // Total Wins one under Event LP, same team to win, rank C
      if ($game['ranking_parameters']['home']['oneUnderTotalWinsVsEventDayLP'] && !$game['ranking_parameters']['away']['oneUnderTotalWinsVsEventDayLP']) {
        $game['algo_rank'] = 'B';
        $game['to_win'] = 'home';
      }

      // Total Wins one under Event LP, same team to win, rank C
      if ($game['ranking_parameters']['away']['oneUnderTotalWinsVsEventDayLP'] && !$game['ranking_parameters']['home']['oneUnderTotalWinsVsEventDayLP']) {
        $game['algo_rank'] = 'B';
        $game['to_win'] = 'away';
      }

      // Is pitcher in 7 year cycle, other team to win, rank D
      if ($game['ranking_parameters']['home']['isPlayerSevenYear'] && !$game['ranking_parameters']['away']['isPlayerSevenYear']) {
        $game['algo_rank'] = 'C';
        $game['to_win'] = 'away';
      }

      // Is pitcher in 7 year cycle, home team to win, rank D
      if ($game['ranking_parameters']['away']['isPlayerSevenYear'] && !$game['ranking_parameters']['home']['isPlayerSevenYear']) {
        $game['algo_rank'] = 'C';
        $game['to_win'] = 'home';
      }


      // If home pitcher is enemy and away pitcher is friendly, rank F, away team to win
      if ($game['ranking_parameters']['home']['isHomeEnemyAwayFriendly']) {
        $game['algo_rank'] = 'E';
        $game['to_win'] = 'away';
      }

      // If away pitcher is enemy and home pitcher is friendly, rank F, home team to win
      if ($game['ranking_parameters']['away']['isAwayEnemyHomeFriendly']) {
        $game['algo_rank'] = 'E';
        $game['to_win'] = 'home';
      }

      // Is pitcher a 1 LP and event LP is 9, rank G, away team to win
      if ($game['ranking_parameters']['home']['isPlayerOneDayNine']) {
        $game['algo_rank'] = 'F';
        $game['to_win'] = 'away';
      }

      // Is pitcher a 1 LP and event LP is 9, rank G, home team to win
      if ($game['ranking_parameters']['away']['isPlayerOneDayNine']) {
        $game['algo_rank'] = 'F';
        $game['to_win'] = 'home';
      }

      // Is pitcher a 9 LP and event LP is 1, rank H, away team to win
      if ($game['ranking_parameters']['home']['isPlayerNineDayOne']) {
        $game['algo_rank'] = 'G';
        $game['to_win'] = 'away';
      }

      // Is pitcher a 9 LP and event LP is 1, rank H, home team to win
      if ($game['ranking_parameters']['away']['isPlayerNineDayOne']) {
        $game['algo_rank'] = 'G';
        $game['to_win'] = 'home';
      }

      // Is pitcher bornday last two digits of year, rank I, away team to win
      if ($game['ranking_parameters']['home']['isPlayerBDLastTwo']) {
        $game['algo_rank'] = 'H';
        $game['to_win'] = 'home';
      }

      // Is pitcher bornday last two digits of year, rank I, home team to win
      if ($game['ranking_parameters']['away']['isPlayerBDLastTwo']) {
        $game['algo_rank'] = 'H';
        $game['to_win'] = 'away';
      }

      // If both pitchers in enemy year, rank D, placing lower so that it get overriden by other ranks
      if ($game['ranking_parameters']['home']['bothPlayersEnemyYr'] && $game['ranking_parameters']['away']['bothPlayersEnemyYr']) {
        $game['algo_rank'] = 'D';
        $game['auto_over'] = true;
      }


      $tennisRecord = TennisRecord::find($game['id']);



      if ($tennisRecord) {
        // Assuming you have appropriate columns set up in your BaseballData model and database
        // to store the new ranking parameters and algo rank.
        $tennisRecord->algo_rank = $game['algo_rank'];
        $tennisRecord->to_win = $game['to_win'];
        $tennisRecord->auto_over = $game['auto_over'];
        $tennisRecord->player1 = $game['player1'];
        $tennisRecord->player2 = $game['player2'];
        $tennisRecord->event_data = $game['event_data'];
        $tennisRecord->homeTeam_records_lp = $game['homeTeam_records_lp'];
        $tennisRecord->awayTeam_records_lp = $game['awayTeam_records_lp'];
        $tennisRecord->ranking_parameters = $game['ranking_parameters'];
        // Any other fields you need to update...

        // Save the updated record
        $tennisRecord->save();
      }
    }

    // return $gameData;
  }
}
