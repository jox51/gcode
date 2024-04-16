<?php

namespace App\Http\Utils;

class Constants
{


  public static function getApiData()
  {
    $json = '{
      "DATA": [
        {
          "ID": 1,
          "NAME": "SOCCER"
        },
        {
          "ID": 2,
          "NAME": "TENNIS"
        },
        {
          "ID": 3,
          "NAME": "BASKETBALL"
        },
        {
          "ID": 4,
          "NAME": "HOCKEY"
        },
        {
          "ID": 5,
          "NAME": "AMERICAN_FOOTBALL"
        },
        {
          "ID": 6,
          "NAME": "BASEBALL"
        },
        {
          "ID": 7,
          "NAME": "HANDBALL"
        },
        {
          "ID": 8,
          "NAME": "RUGBY_UNION"
        },
        {
          "ID": 9,
          "NAME": "FLOORBALL"
        },
        {
          "ID": 10,
          "NAME": "BANDY"
        },
        {
          "ID": 11,
          "NAME": "FUTSAL"
        },
        {
          "ID": 12,
          "NAME": "VOLLEYBALL"
        },
        {
          "ID": 13,
          "NAME": "CRICKET"
        },
        {
          "ID": 14,
          "NAME": "DARTS"
        },
        {
          "ID": 15,
          "NAME": "SNOOKER"
        },
        {
          "ID": 16,
          "NAME": "BOXING"
        },
        {
          "ID": 17,
          "NAME": "BEACH_VOLLEYBALL"
        },
        {
          "ID": 18,
          "NAME": "AUSSIE_RULES"
        },
        {
          "ID": 19,
          "NAME": "RUGBY_LEAGUE"
        },
        {
          "ID": 21,
          "NAME": "BADMINTON"
        },
        {
          "ID": 22,
          "NAME": "WATER_POLO"
        },
        {
          "ID": 23,
          "NAME": "GOLF"
        },
        {
          "ID": 24,
          "NAME": "FIELD_HOCKEY"
        },
        {
          "ID": 25,
          "NAME": "TABLE_TENNIS"
        },
        {
          "ID": 26,
          "NAME": "BEACH_SOCCER"
        },
        {
          "ID": 28,
          "NAME": "MMA"
        },
        {
          "ID": 29,
          "NAME": "NETBALL"
        },
        {
          "ID": 30,
          "NAME": "PESAPALLO"
        },
        {
          "ID": 31,
          "NAME": "MOTORSPORT"
        },
        {
          "ID": 32,
          "NAME": "AUTORACING"
        },
        {
          "ID": 33,
          "NAME": "MOTORACING"
        },
        {
          "ID": 34,
          "NAME": "CYCLING"
        },
        {
          "ID": 35,
          "NAME": "HORSE_RACING"
        },
        {
          "ID": 36,
          "NAME": "ESPORTS"
        },
        {
          "ID": 37,
          "NAME": "WINTER_SPORTS"
        },
        {
          "ID": 38,
          "NAME": "SKI_JUMPING"
        },
        {
          "ID": 39,
          "NAME": "ALPINE_SKIING"
        },
        {
          "ID": 40,
          "NAME": "CROSS_COUNTRY"
        },
        {
          "ID": 41,
          "NAME": "BIATHLON"
        },
        {
          "ID": 42,
          "NAME": "KABADDI"
        }
      ]
    }';
    // Decode the JSON data to an associative array
    $data = json_decode($json, true);

    if ($data === null) {
      // Handle JSON decode error
      return [];
    }

    // Initialize an empty associative array
    $associativeArray = [];

    // Loop through each element in the DATA array
    foreach ($data['DATA'] as $element) {
      $associativeArray[$element['ID']] = $element['NAME'];
    }


    return $associativeArray;
  }

  public static function chinAstroEnemySigns()
  {

    return $enemySigns = [

      'Rat' => 'Horse',
      'Ox' => 'Goat',
      'Tiger' => 'Monkey',
      'Cat' => 'Rooster',
      'Dragon' => 'Dog',
      'Snake' => 'Pig',
      'Horse' => 'Rat',
      'Goat' => 'Ox',
      'Monkey' => 'Tiger',
      'Rooster' => 'Cat',
      'Dog' => 'Dragon',
      'Pig' => 'Snake'

    ];
  }

  public static function bettingMarkets()
  {
    return $markets = [
      "player_assists_over_under",
      "player_assists_points_over_under",
      "player_assists_points_rebounds_over_under",
      "player_assists_rebounds_over_under",
      "player_blocks_over_under",
      "player_blocks_steals_over_under",
      "player_points_over_under",
      "player_points_rebounds_over_under",
      "player_rebounds_over_under",
      "player_steals_over_under",
      "player_threes_over_under",
    ];
  }

  public static function marketsTranslater()
  {
    return [
      'ast' => "player_assists_over_under",
      'blk' =>  "player_blocks_over_under",
      'pts' => "player_points_over_under",
      'reb' => "player_rebounds_over_under",
      'stl' => "player_steals_over_under",
    ];
  }

  public static function nflBettingMarkets()
  {
    return $markets = [
      "player_anytime_td",
      "player_interceptions_over_under",
      "player_passing_and_rushing_yds_over_under",
      "player_passing_attempts_over_under",
      "player_passing_completions_over_under",
      "player_passing_tds_over_under",
      "player_passing_yds_over_under",
      "player_receiving_yds_over_under",
      "player_receptions_over_under",
      "player_record_sack",
      "player_rushing_and_receiving_yards_over_under",
      "player_rushing_attempts_over_under",
      "player_rushing_yds_over_under",
      "player_sacks_over_under",
      "player_tackles_and_assists_over_under",
      "player_tackles_over_under",
      "player_td_over_under",
    ];
  }
  public static function nflComboBettingMarkets()
  {
    return $markets = [

      "player_passing_and_rushing_yds_over_under",
      "player_rushing_and_receiving_yards_over_under",

    ];
  }

  // Need to write logic for rushingTouchdowns/receivingTouchdowns equal to anytimeTd
  public static function nflMarketsTranslater()
  {

    return [
      'passingAttempts' => "player_passing_attempts_over_under",
      'passingCompletions' => "player_passing_completions_over_under",
      'passingYards' => "player_passing_yds_over_under",
      'passingRushingYards' => "player_passing_and_rushing_yds_over_under",
      'passingTouchdowns' => "player_passing_tds_over_under",
      'rushingAttempts' => "player_rushing_attempts_over_under",
      'rushingYards' => "player_rushing_yds_over_under",
      "receivingYards" => "player_receiving_yds_over_under",
      "rushingReceivingYards" =>  "player_rushing_and_receiving_yards_over_under",
      "receivingReceptions" => "player_receptions_over_under",
      "defensiveInterceptions" => "player_interceptions_over_under",
      "defensiveAssistTackles" => "player_tackles_and_assists_over_under",
      "defensiveCombineTackles" => "player_tackles_over_under",
      "defensiveSacks" => "player_sacks_over_under",
    ];
  }
}
