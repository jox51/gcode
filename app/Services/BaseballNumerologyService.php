<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use App\Models\BaseballData;
use App\Models\SoulMate;
use App\Http\Utils\CalculationUtils;
use App\Http\Utils\Constants;
use App\Http\Utils\ZodiacUtils;

class BaseballNumerologyService {

  public function calculateGameData() {


    // Get today's date in EST and format it to match the date portion of the datetime field
    $todayDate = Carbon::now('America/New_York')->format('Y-m-d');

    // Query to get records matching today's date
    // Assuming 'date' is a datetime field, we use whereDate for comparison
    $baseballData = BaseballData::whereDate('date', '=', $todayDate)->get()->toArray();
    $gameNumerologyData = $this->calculateEventNumerologyCalcs($baseballData);
    $gameRecordsNums = $this->calculateTeamRecordsNumerology($gameNumerologyData);

    $gameWithPitchersNumerology = $this->calculatePitchersNumerology($gameRecordsNums);

    $gameDataWithParams = $this->calculateRankings($gameWithPitchersNumerology); // New method to calculate rankings

    $this->determineAlgoRank($gameDataWithParams);
  }

  public function calculateEventNumerologyCalcs($baseballData) {

    foreach ($baseballData as &$game) {

      $gameBirthdayParts = explode('/', $game['start_date']);
      if (count($gameBirthdayParts) == 3) {
        list($eventMonth, $eventDay, $eventYear) = $gameBirthdayParts;

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

    return $baseballData;
  }

  public function calculateTeamRecordsNumerology($gameData) {
    // $homeResLP = CalculationUtils::calculateTotal($homeRawLifepath);

    foreach ($gameData as &$game) {

      $teams = json_decode($game['teams'], true);



      // Away team
      $atAwayRecord = $teams['awayTeam']['records']['away'];
      $atHomeRecord = $teams['awayTeam']['records']['home'];
      $atTotalRecord = $teams['awayTeam']['records']['total'];

      // Home Team
      $htAwayRecord = $teams['homeTeam']['records']['away'];
      $htHomeRecord = $teams['homeTeam']['records']['home'];
      $htTotalRecord = $teams['homeTeam']['records']['total'];

      // Break down the records into individual parts (AWAY TEAM)
      $atAwayRecordParts = explode('-', $atAwayRecord);
      $atHomeRecordParts = explode('-', $atHomeRecord);
      $atTotalRecordParts = explode('-', $atTotalRecord);


      list($atAwayWins, $awayLosses) = $atAwayRecordParts;
      list($atHomeWins, $homeLosses) = $atHomeRecordParts;
      list($atTotalWins, $totalLosses) = $atTotalRecordParts;


      // Break down the records into individual parts (HOME TEAM)
      $htAwayRecordParts = explode('-', $htAwayRecord);
      $htHomeRecordParts = explode('-', $htHomeRecord);
      $htTotalRecordParts = explode('-', $htTotalRecord);


      list($htAwayWins, $awayLosses) = $htAwayRecordParts;
      list($htHomeWins, $homeLosses) = $htHomeRecordParts;
      list($htTotalWins, $totalLosses) = $htTotalRecordParts;

      // Calculate the life path for the records
      $htTotalWinsLP = CalculationUtils::calculateTotal($htTotalWins);
      $htHomeWinsLP = CalculationUtils::calculateTotal($htHomeWins);
      $htAwayWinsLP = CalculationUtils::calculateTotal($htAwayWins);

      $atTotalWinsLP = CalculationUtils::calculateTotal($atTotalWins);
      $atHomeWinsLP = CalculationUtils::calculateTotal($atHomeWins);
      $atAwayWinsLP = CalculationUtils::calculateTotal($atAwayWins);


      $game['homeTeam_records_lp'] = [
        'total' => $htTotalWinsLP,
        'home' => $htHomeWinsLP,
        'away' => $htAwayWinsLP,
      ];

      $game['awayTeam_records_lp'] = [
        'total' => $atTotalWinsLP,
        'home' => $atHomeWinsLP,
        'away' => $atAwayWinsLP,
      ];
    }

    return $gameData;
  }

  public function calculatePitchersNumerology($gameData) {
    foreach ($gameData as &$game) {
      $teams = json_decode($game['teams'], true); // Assuming $game['teams'] is a JSON-encoded string

      // Extract the last two digits of the year from start_date
      $yearLastTwoDigits = substr($game['start_date'], -2, 2);

      foreach (['homeTeam', 'awayTeam'] as $teamType) {
        $team = $teams[$teamType];

        // Check if startingPitcher and dateOfBirthTimestamp exist
        if (isset($team['startingPitcher']['player']['dateOfBirthTimestamp'])) {
          $timestamp = $team['startingPitcher']['player']['dateOfBirthTimestamp'];

          // Convert Unix timestamp to Carbon instance and format to MM/DD/YYYY
          $dateOfBirth = Carbon::createFromTimestamp($timestamp)->format('m/d/Y');
          $birthDay = Carbon::createFromTimestamp($timestamp)->format('d'); // Extract the day

          // Compare the pitcher's birth day with the last two digits of the start_date year
          $isBorndaySameLastTwo = $birthDay == $yearLastTwoDigits;

          // Add the comparison result to the startingPitcher['player'] array
          $teams[$teamType]['startingPitcher']['player']['isBorndaySameLastTwo'] = $isBorndaySameLastTwo;

          $pitcherBirthdayParts = explode('/', $dateOfBirth);
          list($birthMonth, $birthDay, $birthYear) = $pitcherBirthdayParts;

          $calculationUtilsPitcher = new CalculationUtils("$birthMonth-$birthDay");
          // Store only the hasBirthdayPassed value
          $teams[$teamType]['startingPitcher']['player']['hasBirthdayPassed'] = $calculationUtilsPitcher->hasBirthdayPassed;


          //Pitcher chinese zodiac
          $pitcherZodiac = ZodiacUtils::getJustChinZodiac($birthYear, $birthMonth, $birthDay);
          $teams[$teamType]['startingPitcher']['player']['zodiac'] = $pitcherZodiac;

          // Pitcher Enemy/Friendly Years
          $pitcherFriendlyEnemyYears = CalculationUtils::calculateFriendlyEnemyYears($birthYear, $birthMonth, $birthDay);

          // Check enemy signs
          // Determine zodiac relationship
          $teams[$teamType]['startingPitcher']['player']['zodiacRelationship'] = self::getZodiacRelationship($pitcherZodiac, $game['event_data']['event_chin_zodiac'], Constants::chinAstroEnemySigns());

          // Calculate soulmate days
          // Check soulmate day for home participant
          $pitcherSoulmateInfo = self::checkSoulmateDay($game['start_date'], $dateOfBirth);
          $teams[$teamType]['startingPitcher']['player']['isSoulmateDay'] = $pitcherSoulmateInfo['isSoulmateDay'];

          $teams[$teamType]['startingPitcher']['player']['soulmateDate'] = $pitcherSoulmateInfo['soulmateDate'];

          $teams[$teamType]['startingPitcher']['player']['pitcherFriendlyEnemyYears'] = $pitcherFriendlyEnemyYears;

          $teams[$teamType]['startingPitcher']['player']['pitcherBirthdayCalculations'] = CalculationUtils::birthdaySumCalculators($birthMonth, $birthDay, $birthYear);

          $pitcherCalculations = $teams[$teamType]['startingPitcher']['player']['pitcherBirthdayCalculations'];
          $pitcherRawLifepath = $pitcherCalculations['sumMonth'] + $pitcherCalculations['sumDay'] + $pitcherCalculations['sumYear'];


          // Calculate personal year
          $pitcherPersonalYear = CalculationUtils::calculatePersonalYear($pitcherCalculations['sumMonth'], $pitcherCalculations['sumDay'], $calculationUtilsPitcher->hasBirthdayPassed);

          $teams[$teamType]['startingPitcher']['player']['personalYear'] = $pitcherPersonalYear;


          $pitcherResLP = CalculationUtils::calculateTotal($pitcherRawLifepath);
          $pitcherResBday = CalculationUtils::calculateTotal($pitcherCalculations['sumDay']);

          $teams[$teamType]['startingPitcher']['player']['pitcher_lp'] = $pitcherResLP;
          $teams[$teamType]['startingPitcher']['player']['pitcher_bday'] = $pitcherResBday;

          // Update the team's startingPitcher's date of birth
          $teams[$teamType]['startingPitcher']['player']['dateOfBirth'] = $dateOfBirth;
        }
      }

      // Update the game's teams data with the modified teams array
      $game['teams'] = $teams;
    }

    return $gameData;
  }

  protected static function checkSoulmateDay($eventDate, $participantBirthday) {
    $birthday = Carbon::createFromFormat('m/d/Y', $participantBirthday)->format('F j');
    $eventDateFormatted = Carbon::createFromFormat('m/d/Y', $eventDate)->format('F j');



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
    foreach ($gameData as &$game) {
      // Default to false
      $game['ranking_parameters']['home']['totalWinsVsEventDayLP'] = false;
      $game['ranking_parameters']['away']['totalWinsVsEventDayLP'] = false;
      $game['ranking_parameters']['home']['homeWinsVsEventDayLP'] = false;
      $game['ranking_parameters']['away']['awayWinsVsEventDayLP'] = false;
      $game['ranking_parameters']['home']['oneUnderTotalWinsVsEventDayLP'] = false;
      $game['ranking_parameters']['away']['oneUnderTotalWinsVsEventDayLP'] = false;
      $game['ranking_parameters']['home']['oneUnderHomeWinsVsEventDayLP'] = false;
      $game['ranking_parameters']['away']['oneUnderAwayWinsVsEventDayLP'] = false;
      $game['ranking_parameters']['home']['isPitcherEnemyYr'] = false;
      $game['ranking_parameters']['away']['isPitcherEnemyYr'] = false;
      $game['ranking_parameters']['home']['bothPitchersEnemyYr'] = false;
      $game['ranking_parameters']['away']['bothPitchersEnemyYr'] = false;
      $game['ranking_parameters']['home']['isHomeFriendlyAwayEnemy'] = false;
      $game['ranking_parameters']['away']['isAwayEnemyHomeFriendly'] = false;
      $game['ranking_parameters']['home']['isHomeEnemyAwayFriendly'] = false;
      $game['ranking_parameters']['away']['isAwayFriendlyHomeEnemy'] = false;
      $game['ranking_parameters']['home']['isPitcherSevenYear'] = false;
      $game['ranking_parameters']['away']['isPitcherSevenYear'] = false;
      $game['ranking_parameters']['home']['isPitcherOneDayNine'] = false;
      $game['ranking_parameters']['away']['isPitcherOneDayNine'] = false;
      $game['ranking_parameters']['home']['isPitcherNineDayOne'] = false;
      $game['ranking_parameters']['away']['isPitcherNineDayOne'] = false;
      $game['ranking_parameters']['home']['isPitcherBDLastTwo'] = false;
      $game['ranking_parameters']['away']['isPitcherBDLastTwo'] = false;

      if (!isset($game['teams']['homeTeam']['startingPitcher']) || !isset($game['teams']['awayTeam']['startingPitcher']) || !isset($game['teams']['homeTeam']['startingPitcher']['player']) || !isset($game['teams']['awayTeam']['startingPitcher']['player'])) {
        continue;
      }


      // Compare Total Wins vs Event LP values
      if ($game['homeTeam_records_lp']['total'] == $game['event_data']['event_lp']) {
        $game['ranking_parameters']['away']['totalWinsVsEventDayLP'] = true;
      }
      if ($game['awayTeam_records_lp']['total'] == $game['event_data']['event_lp']) {
        $game['ranking_parameters']['home']['totalWinsVsEventDayLP'] = true;
      }

      // Compare Home/Away Wins vs Event LP values
      if ($game['homeTeam_records_lp']['home'] == $game['event_data']['event_lp']) {
        $game['ranking_parameters']['away']['homeWinsVsEventDayLP'] = true;
      }
      if ($game['awayTeam_records_lp']['away'] == $game['event_data']['event_lp']) {
        $game['ranking_parameters']['home']['awayWinsVsEventDayLP'] = true;
      }

      // Total Record One Under vs Event LP values
      if ($game['homeTeam_records_lp']['total'] == ($game['event_data']['event_lp'] - 1)) {
        $game['ranking_parameters']['home']['oneUnderTotalWinsVsEventDayLP'] = true;
      }
      if ($game['awayTeam_records_lp']['total'] == ($game['event_data']['event_lp'] - 1)) {
        $game['ranking_parameters']['away']['oneUnderTotalWinsVsEventDayLP'] = true;
      }

      //  Home/Away Wins One Under vs Event LP values
      if ($game['homeTeam_records_lp']['home'] == ($game['event_data']['event_lp'] - 1)) {
        $game['ranking_parameters']['home']['oneUnderHomeWinsVsEventDayLP'] = true;
      }
      if ($game['awayTeam_records_lp']['away'] == ($game['event_data']['event_lp'] - 1)) {
        $game['ranking_parameters']['away']['oneUnderAwayWinsVsEventDayLP'] = true;
      }

      // Pitcher Enemy Year
      if ($game['teams']['homeTeam']['startingPitcher']['player']['zodiacRelationship'] == 'enemy') {
        $game['ranking_parameters']['home']['isPitcherEnemyYr'] = true;
      }
      if ($game['teams']['awayTeam']['startingPitcher']['player']['zodiacRelationship'] == 'enemy') {
        $game['ranking_parameters']['away']['isPitcherEnemyYr'] = true;
      }

      // Both Pitchers Enemy Year
      if ($game['teams']['homeTeam']['startingPitcher']['player']['zodiacRelationship'] == 'enemy' && $game['teams']['awayTeam']['startingPitcher']['player']['zodiacRelationship'] == 'enemy') {
        $game['ranking_parameters']['home']['bothPitchersEnemyYr'] = true;
        $game['ranking_parameters']['away']['bothPitchersEnemyYr'] = true;
      }

      // Home Pitcher Friendly/Away Pitcher Enemy
      if ($game['teams']['homeTeam']['startingPitcher']['player']['zodiacRelationship'] == 'friendly' && $game['teams']['awayTeam']['startingPitcher']['player']['zodiacRelationship'] == 'enemy') {
        $game['ranking_parameters']['home']['isHomeFriendlyAwayEnemy'] = true;
        $game['ranking_parameters']['away']['isAwayEnemyHomeFriendly'] = true;
      }

      // Home Pitcher Enemy/Away Pitcher Friendly
      if ($game['teams']['homeTeam']['startingPitcher']['player']['zodiacRelationship'] == 'enemy' && $game['teams']['awayTeam']['startingPitcher']['player']['zodiacRelationship'] == 'friendly') {
        $game['ranking_parameters']['home']['isHomeEnemyAwayFriendly'] = true;
        $game['ranking_parameters']['away']['isAwayFriendlyHomeEnemy'] = true;
      }
    }

    // Pitcher 7 year cycle
    if ($game['teams']['homeTeam']['startingPitcher']['player']['personalYear']['currPersonalYear'] == 7) {
      $game['ranking_parameters']['home']['isPitcherSevenYear'] = true;
    }
    if ($game['teams']['awayTeam']['startingPitcher']['player']['personalYear']['currPersonalYear'] == 7) {
      $game['ranking_parameters']['away']['isPitcherSevenYear'] = true;
    }

    // Is it a 9 day and 1 pitcher

    if ($game['teams']['homeTeam']['startingPitcher']['player']['pitcher_lp']  === 1 && ($game['event_data']['event_lp'] === 9)) {
      $game['ranking_parameters']['home']['isPitcherOneDayNine'] = true;
    }
    if ($game['teams']['awayTeam']['startingPitcher']['player']['pitcher_lp']  === 1 && ($game['event_data']['event_lp'] === 9)) {
      $game['ranking_parameters']['away']['isPitcherOneDayNine'] = true;
    }

    // Is it a 1 day and 9 pitcher

    if ($game['teams']['homeTeam']['startingPitcher']['player']['pitcher_lp']  === 9 && ($game['event_data']['event_lp'] === 1)) {
      $game['ranking_parameters']['home']['isPitcherNineDayOne'] = true;
    }
    if ($game['teams']['awayTeam']['startingPitcher']['player']['pitcher_lp']  === 9 && ($game['event_data']['event_lp'] === 1)) {
      $game['ranking_parameters']['away']['isPitcherNineDayOne'] = true;
    }

    // Is bornday same as last two digits of year
    if ($game['teams']['homeTeam']['startingPitcher']['player']['isBorndaySameLastTwo']) {
      $game['ranking_parameters']['home']['isPitcherBDLastTwo'] = true;
    }
    if ($game['teams']['awayTeam']['startingPitcher']['player']['isBorndaySameLastTwo']) {
      $game['ranking_parameters']['away']['isPitcherBDLastTwo'] = true;
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
        $game['algo_rank'] = 'C';
        $game['to_win'] = 'away';
      }

      // Total Wins equal to Event LP, home team to win
      if ($game['ranking_parameters']['away']['totalWinsVsEventDayLP'] && !$game['ranking_parameters']['home']['totalWinsVsEventDayLP']) {
        $game['algo_rank'] = 'C';
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
        $game['algo_rank'] = 'C';
        $game['to_win'] = 'home';
      }

      // Total Wins one under Event LP, same team to win, rank C
      if ($game['ranking_parameters']['away']['oneUnderTotalWinsVsEventDayLP'] && !$game['ranking_parameters']['home']['oneUnderTotalWinsVsEventDayLP']) {
        $game['algo_rank'] = 'C';
        $game['to_win'] = 'away';
      }

      // Is pitcher in 7 year cycle, other team to win, rank D
      if ($game['ranking_parameters']['home']['isPitcherSevenYear'] && !$game['ranking_parameters']['away']['isPitcherSevenYear']) {
        $game['algo_rank'] = 'B';
        $game['to_win'] = 'away';
      }

      // Is pitcher in 7 year cycle, home team to win, rank D
      if ($game['ranking_parameters']['away']['isPitcherSevenYear'] && !$game['ranking_parameters']['home']['isPitcherSevenYear']) {
        $game['algo_rank'] = 'B';
        $game['to_win'] = 'home';
      }

      // If both pitchers in enemy year, rank E, auto over
      if ($game['ranking_parameters']['home']['bothPitchersEnemyYr'] && $game['ranking_parameters']['away']['bothPitchersEnemyYr']) {
        $game['algo_rank'] = 'A';
        $game['auto_over'] = true;
      }

      // If home pitcher is enemy and away pitcher is friendly, rank F, away team to win
      // if ($game['ranking_parameters']['home']['isHomeEnemyAwayFriendly']) {
      //   $game['algo_rank'] = 'F';
      //   $game['to_win'] = 'away';
      // }

      // If away pitcher is enemy and home pitcher is friendly, rank F, home team to win
      // if ($game['ranking_parameters']['away']['isAwayEnemyHomeFriendly']) {
      //   $game['algo_rank'] = 'F';
      //   $game['to_win'] = 'home';
      // }

      // Is pitcher a 1 LP and event LP is 9, rank G, away team to win
      if ($game['ranking_parameters']['home']['isPitcherOneDayNine']) {
        $game['algo_rank'] = 'B';
        $game['to_win'] = 'away';
      }

      // Is pitcher a 1 LP and event LP is 9, rank G, home team to win
      if ($game['ranking_parameters']['away']['isPitcherOneDayNine']) {
        $game['algo_rank'] = 'B';
        $game['to_win'] = 'home';
      }

      // Is pitcher a 9 LP and event LP is 1, rank H, away team to win
      if ($game['ranking_parameters']['home']['isPitcherNineDayOne']) {
        $game['algo_rank'] = 'B';
        $game['to_win'] = 'away';
      }

      // Is pitcher a 9 LP and event LP is 1, rank H, home team to win
      if ($game['ranking_parameters']['away']['isPitcherNineDayOne']) {
        $game['algo_rank'] = 'B';
        $game['to_win'] = 'home';
      }

      // Is pitcher bornday last two digits of year, rank I, away team to win
      if ($game['ranking_parameters']['home']['isPitcherBDLastTwo']) {
        $game['algo_rank'] = 'B';
        $game['to_win'] = 'home';
      }

      // Is pitcher bornday last two digits of year, rank I, home team to win
      if ($game['ranking_parameters']['away']['isPitcherBDLastTwo']) {
        $game['algo_rank'] = 'B';
        $game['to_win'] = 'away';
      }

      $baseballRecord = BaseballData::find($game['id']);

      if ($baseballRecord) {
        // Assuming you have appropriate columns set up in your BaseballData model and database
        // to store the new ranking parameters and algo rank.
        $baseballRecord->algo_rank = $game['algo_rank'];
        $baseballRecord->to_win = $game['to_win'];
        $baseballRecord->auto_over = $game['auto_over'];
        // Any other fields you need to update...

        // Save the updated record
        $baseballRecord->save();
      }
    }

    // return $gameData;
  }
}
