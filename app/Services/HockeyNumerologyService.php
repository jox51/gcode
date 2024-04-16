<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use App\Models\HockeyData;
use App\Models\SoulMate;
use App\Http\Utils\CalculationUtils;
use App\Http\Utils\Constants;
use App\Http\Utils\ZodiacUtils;

class HockeyNumerologyService {

  public function calculateGameData() {


    // Get today's date in EST and format it to match the date portion of the datetime field
    $todayDate = Carbon::now('America/New_York')->format('Y-m-d');

    // Query to get records matching today's date
    // Assuming 'date' is a datetime field, we use whereDate for comparison
    $hockeyData = HockeyData::whereDate('date', '=', $todayDate)->get()->toArray();
    $gameNumerologyData = $this->calculateEventNumerologyCalcs($hockeyData);
    $gameRecordsNums = $this->calculateTeamRecordsNumerology($gameNumerologyData);

    $gameWithGoaliesNumerology = $this->calculateGoaliesNumerology($gameRecordsNums);

    $gameDataWithParams = $this->calculateRankings($gameWithGoaliesNumerology); // New method to calculate rankings

    $this->determineAlgoRank($gameDataWithParams);
  }

  public function calculateEventNumerologyCalcs($hockeyData) {

    foreach ($hockeyData as &$game) {

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

    return $hockeyData;
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

  public function calculateGoaliesNumerology($gameData) {
    foreach ($gameData as &$game) {
      $teams = json_decode($game['teams'], true); // Assuming $game['teams'] is a JSON-encoded string

      // Extract the last two digits of the year from start_date
      $yearLastTwoDigits = substr($game['start_date'], -2, 2);

      foreach (['homeTeam', 'awayTeam'] as $teamType) {
        $team = $teams[$teamType];


        // Check if startingGoalie and dateOfBirthTimestamp exist
        if (isset($team['startingGoalie']['bDay'])) {
          $bDay = $team['startingGoalie']['bDay'];

          // Convert Unix timestamp to Carbon instance and format to MM/DD/YYYY
          $dateOfBirth = Carbon::createFromFormat('m/d/Y', $bDay)->format('m/d/Y');
          $birthDay = Carbon::createFromFormat('m/d/Y', $bDay)->format('d'); // Extract the day


          // Compare the 's birth day with the last two digits of the start_date year
          $isBorndaySameLastTwo = $birthDay == $yearLastTwoDigits;

          // Add the comparison result to the startingGoalie['player'] array
          $teams[$teamType]['startingGoalie']['player']['isBorndaySameLastTwo'] = $isBorndaySameLastTwo;

          $goalieBirthdayParts = explode('/', $dateOfBirth);
          list($birthMonth, $birthDay, $birthYear) = $goalieBirthdayParts;

          $calculationUtilsGoalie = new CalculationUtils("$birthMonth-$birthDay");
          // Store only the hasBirthdayPassed value
          $teams[$teamType]['startingGoalie']['player']['hasBirthdayPassed'] = $calculationUtilsGoalie->hasBirthdayPassed;


          //Goalie chinese zodiac
          $goalieZodiac = ZodiacUtils::getJustChinZodiac($birthYear, $birthMonth, $birthDay);
          $teams[$teamType]['startingGoalie']['player']['zodiac'] = $goalieZodiac;

          // Goalie Enemy/Friendly Years
          $goalieFriendlyEnemyYears = CalculationUtils::calculateFriendlyEnemyYears($birthYear, $birthMonth, $birthDay);

          // Check enemy signs
          // Determine zodiac relationship
          $teams[$teamType]['startingGoalie']['player']['zodiacRelationship'] = self::getZodiacRelationship($goalieZodiac, $game['event_data']['event_chin_zodiac'], Constants::chinAstroEnemySigns());

          // Calculate soulmate days
          // Check soulmate day for home participant
          $goalieSoulmateInfo = self::checkSoulmateDay($game['start_date'], $dateOfBirth);
          $teams[$teamType]['startingGoalie']['player']['isSoulmateDay'] = $goalieSoulmateInfo['isSoulmateDay'];

          $teams[$teamType]['startingGoalie']['player']['soulmateDate'] = $goalieSoulmateInfo['soulmateDate'];

          $teams[$teamType]['startingGoalie']['player']['goalieFriendlyEnemyYears'] = $goalieFriendlyEnemyYears;

          $teams[$teamType]['startingGoalie']['player']['goalieBirthdayCalculations'] = CalculationUtils::birthdaySumCalculators($birthMonth, $birthDay, $birthYear);

          $goalieCalculations = $teams[$teamType]['startingGoalie']['player']['goalieBirthdayCalculations'];
          $goalieRawLifepath = $goalieCalculations['sumMonth'] + $goalieCalculations['sumDay'] + $goalieCalculations['sumYear'];


          // Calculate personal year
          $goaliePersonalYear = CalculationUtils::calculatePersonalYear($goalieCalculations['sumMonth'], $goalieCalculations['sumDay'], $calculationUtilsGoalie->hasBirthdayPassed);

          $teams[$teamType]['startingGoalie']['player']['personalYear'] = $goaliePersonalYear;


          $goalieResLP = CalculationUtils::calculateTotal($goalieRawLifepath);
          $goalieResBday = CalculationUtils::calculateTotal($goalieCalculations['sumDay']);

          $teams[$teamType]['startingGoalie']['player']['goalie_lp'] = $goalieResLP;
          $teams[$teamType]['startingGoalie']['player']['goalie_bday'] = $goalieResBday;

          // Update the team's startingGoalie's date of birth
          $teams[$teamType]['startingGoalie']['player']['dateOfBirth'] = $dateOfBirth;
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
      $game['ranking_parameters']['home']['isGoalieEnemyYr'] = false;
      $game['ranking_parameters']['away']['isGoalieEnemyYr'] = false;
      $game['ranking_parameters']['home']['bothGoaliesEnemyYr'] = false;
      $game['ranking_parameters']['away']['bothGoaliesEnemyYr'] = false;
      $game['ranking_parameters']['home']['isHomeFriendlyAwayEnemy'] = false;
      $game['ranking_parameters']['away']['isAwayEnemyHomeFriendly'] = false;
      $game['ranking_parameters']['home']['isHomeEnemyAwayFriendly'] = false;
      $game['ranking_parameters']['away']['isAwayFriendlyHomeEnemy'] = false;
      $game['ranking_parameters']['home']['isGoalieSevenYear'] = false;
      $game['ranking_parameters']['away']['isGoalieSevenYear'] = false;
      $game['ranking_parameters']['home']['isGoalieOneDayNine'] = false;
      $game['ranking_parameters']['away']['isGoalieOneDayNine'] = false;
      $game['ranking_parameters']['home']['isGoalieNineDayOne'] = false;
      $game['ranking_parameters']['away']['isGoalieNineDayOne'] = false;
      $game['ranking_parameters']['home']['isGoalieBDLastTwo'] = false;
      $game['ranking_parameters']['away']['isGoalieBDLastTwo'] = false;


      // Compare Total Wins vs Event LP values
      if ($game['homeTeam_records_lp']['total'] == $game['event_data']['event_lp']) {
        $game['ranking_parameters']['home']['totalWinsVsEventDayLP'] = true;
      }
      if ($game['awayTeam_records_lp']['total'] == $game['event_data']['event_lp']) {
        $game['ranking_parameters']['away']['totalWinsVsEventDayLP'] = true;
      }

      // Compare Home/Away Wins vs Event LP values
      if ($game['homeTeam_records_lp']['home'] == $game['event_data']['event_lp']) {
        $game['ranking_parameters']['home']['homeWinsVsEventDayLP'] = true;
      }
      if ($game['awayTeam_records_lp']['away'] == $game['event_data']['event_lp']) {
        $game['ranking_parameters']['away']['awayWinsVsEventDayLP'] = true;
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

      // Goalie Enemy Year
      if ($game['teams']['homeTeam']['startingGoalie']['player']['zodiacRelationship'] == 'enemy') {
        $game['ranking_parameters']['home']['isGoalieEnemyYr'] = true;
      }
      if ($game['teams']['awayTeam']['startingGoalie']['player']['zodiacRelationship'] == 'enemy') {
        $game['ranking_parameters']['away']['isGoalieEnemyYr'] = true;
      }

      // Both Goalies Enemy Year
      if ($game['teams']['homeTeam']['startingGoalie']['player']['zodiacRelationship'] == 'enemy' && $game['teams']['awayTeam']['startingGoalie']['player']['zodiacRelationship'] == 'enemy') {
        $game['ranking_parameters']['home']['bothGoaliesEnemyYr'] = true;
        $game['ranking_parameters']['away']['bothGoaliesEnemyYr'] = true;
      }

      // Home Goalie Friendly/Away Goalie Enemy
      if ($game['teams']['homeTeam']['startingGoalie']['player']['zodiacRelationship'] == 'friendly' && $game['teams']['awayTeam']['startingGoalie']['player']['zodiacRelationship'] == 'enemy') {
        $game['ranking_parameters']['home']['isHomeFriendlyAwayEnemy'] = true;
        $game['ranking_parameters']['away']['isAwayEnemyHomeFriendly'] = true;
      }

      // Home Goalie Enemy/Away Goalie Friendly
      if ($game['teams']['homeTeam']['startingGoalie']['player']['zodiacRelationship'] == 'enemy' && $game['teams']['awayTeam']['startingGoalie']['player']['zodiacRelationship'] == 'friendly') {
        $game['ranking_parameters']['home']['isHomeEnemyAwayFriendly'] = true;
        $game['ranking_parameters']['away']['isAwayFriendlyHomeEnemy'] = true;
      }
    }

    // Goalie 7 year cycle
    if ($game['teams']['homeTeam']['startingGoalie']['player']['personalYear']['currPersonalYear'] == 7) {
      $game['ranking_parameters']['home']['isGoalieSevenYear'] = true;
    }
    if ($game['teams']['awayTeam']['startingGoalie']['player']['personalYear']['currPersonalYear'] == 7) {
      $game['ranking_parameters']['away']['isGoalieSevenYear'] = true;
    }

    // Is it a 9 day and 1 

    if ($game['teams']['homeTeam']['startingGoalie']['player']['goalie_lp']  === 1 && ($game['event_data']['event_lp'] === 9)) {
      $game['ranking_parameters']['home']['isGoalieOneDayNine'] = true;
    }
    if ($game['teams']['awayTeam']['startingGoalie']['player']['goalie_lp']  === 1 && ($game['event_data']['event_lp'] === 9)) {
      $game['ranking_parameters']['away']['isGoalieOneDayNine'] = true;
    }

    // Is it a 1 day and 9 

    if ($game['teams']['homeTeam']['startingGoalie']['player']['goalie_lp']  === 9 && ($game['event_data']['event_lp'] === 1)) {
      $game['ranking_parameters']['home']['isGoalieNineDayOne'] = true;
    }
    if ($game['teams']['awayTeam']['startingGoalie']['player']['goalie_lp']  === 9 && ($game['event_data']['event_lp'] === 1)) {
      $game['ranking_parameters']['away']['isGoalieNineDayOne'] = true;
    }

    // Is bornday same as last two digits of year
    if ($game['teams']['homeTeam']['startingGoalie']['player']['isBorndaySameLastTwo']) {
      $game['ranking_parameters']['home']['isGoalieBDLastTwo'] = true;
    }
    if ($game['teams']['awayTeam']['startingGoalie']['player']['isBorndaySameLastTwo']) {
      $game['ranking_parameters']['away']['isGoalieBDLastTwo'] = true;
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
      if ($game['ranking_parameters']['home']['homeWinsVsEventDayLP'] && !$game['ranking_parameters']['away']['awayWinsVsEventDayLP']) {
        $game['algo_rank'] = 'B';
        $game['to_win'] = 'away';
      }

      // Away Wins equal to Event LP, home team to win, rank B
      if ($game['ranking_parameters']['away']['awayWinsVsEventDayLP'] && !$game['ranking_parameters']['home']['homeWinsVsEventDayLP']) {
        $game['algo_rank'] = 'B';
        $game['to_win'] = 'home';
      }

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

      // Is  in 7 year cycle, other team to win, rank D
      if ($game['ranking_parameters']['home']['isGoalieSevenYear'] && !$game['ranking_parameters']['away']['isGoalieSevenYear']) {
        $game['algo_rank'] = 'D';
        $game['to_win'] = 'away';
      }

      // Is  in 7 year cycle, home team to win, rank D
      if ($game['ranking_parameters']['away']['isGoalieSevenYear'] && !$game['ranking_parameters']['home']['isGoalieSevenYear']) {
        $game['algo_rank'] = 'D';
        $game['to_win'] = 'home';
      }

      // If both s in enemy year, rank E, auto over
      if ($game['ranking_parameters']['home']['bothGoaliesEnemyYr'] && $game['ranking_parameters']['away']['bothGoaliesEnemyYr']) {
        $game['algo_rank'] = 'E';
        $game['auto_over'] = true;
      }

      // If home  is enemy and away  is friendly, rank F, away team to win
      if ($game['ranking_parameters']['home']['isHomeEnemyAwayFriendly']) {
        $game['algo_rank'] = 'F';
        $game['to_win'] = 'away';
      }

      // If away  is enemy and home  is friendly, rank F, home team to win
      if ($game['ranking_parameters']['away']['isAwayEnemyHomeFriendly']) {
        $game['algo_rank'] = 'F';
        $game['to_win'] = 'home';
      }

      // Is  a 1 LP and event LP is 9, rank G, away team to win
      if ($game['ranking_parameters']['home']['isGoalieOneDayNine']) {
        $game['algo_rank'] = 'G';
        $game['to_win'] = 'away';
      }

      // Is  a 1 LP and event LP is 9, rank G, home team to win
      if ($game['ranking_parameters']['away']['isGoalieOneDayNine']) {
        $game['algo_rank'] = 'G';
        $game['to_win'] = 'home';
      }

      // Is  a 9 LP and event LP is 1, rank H, away team to win
      if ($game['ranking_parameters']['home']['isGoalieNineDayOne']) {
        $game['algo_rank'] = 'H';
        $game['to_win'] = 'away';
      }

      // Is  a 9 LP and event LP is 1, rank H, home team to win
      if ($game['ranking_parameters']['away']['isGoalieNineDayOne']) {
        $game['algo_rank'] = 'H';
        $game['to_win'] = 'home';
      }

      // Is  bornday last two digits of year, rank I, away team to win
      if ($game['ranking_parameters']['home']['isGoalieBDLastTwo']) {
        $game['algo_rank'] = 'I';
        $game['to_win'] = 'away';
      }

      // Is  bornday last two digits of year, rank I, home team to win
      if ($game['ranking_parameters']['away']['isGoalieBDLastTwo']) {
        $game['algo_rank'] = 'I';
        $game['to_win'] = 'home';
      }

      $baseballRecord = HockeyData::find($game['id']);

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
