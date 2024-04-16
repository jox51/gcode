<?php

namespace App\Http\Utils;

use Illuminate\Support\Facades\Http;

class ZodiacUtils {

  public static function calculateEnemyYears($mainZodiac, $testZodiac, $birthYear) {
    $currentYear = intval(date('Y'));
    $endYear = $currentYear + 14;
    $convBirthYear = intval($birthYear);
    $altEndYear = $convBirthYear + 42;
    $finalEndYear = $convBirthYear > $currentYear ? $altEndYear : $endYear;

    $startYear = $mainZodiac === $testZodiac ? $convBirthYear + 6 : $convBirthYear + 5;

    $enemyYears = [];

    for ($year = $startYear; $year <= $finalEndYear; $year += 12) {
      $enemyYears[] = $year;
    }

    return array_slice($enemyYears, -3);
  }

  public static function calculateFriendlyYears($mainZodiac, $testZodiac, $birthYear) {
    $currentYear = intval(date('Y'));
    $endYear = $currentYear + 12;
    $convBirthYear = intval($birthYear);
    $altEndYear = $convBirthYear + 42;
    $finalEndYear = $convBirthYear >= $currentYear ? $altEndYear : $endYear;

    $startYear = $mainZodiac === $testZodiac ? $convBirthYear : $convBirthYear - 1;

    $friendlyYears = [];

    for ($year = $startYear; $year <= $finalEndYear; $year += 12) {
      $friendlyYears[] = $year;
    }

    return array_slice($friendlyYears, -3);
  }

  public static function getJustChinZodiac($yearStr, $monthStr, $dayStr) {
    $url = getenv('LUNARSOLAR_API') . 'getZodiac'; // Ensure this is the correct API endpoint

    try {
      // Make the HTTP GET request
      // trying below with options, received curl error for invalid cert
      $response = Http::withOptions(['verify' => false])->get($url, [
        'year' => $yearStr,
        'month' => $monthStr,
        'day' => $dayStr
      ]);
      // $response = Http::get($url, [
      //   'year' => $yearStr,
      //   'month' => $monthStr,
      //   'day' => $dayStr
      // ]);


      // Check if the response was successful
      if ($response->successful()) {
        $zodiac = $response->json()['result'];

        // Conversion logic
        if ($zodiac === "rabbit") {
          $zodiac = "Cat";
        } elseif ($zodiac === "sheep") {
          $zodiac = "Goat";
        } else {
          // Capitalize the first letter if it's not 'rabbit' or 'sheep'
          $zodiac = ucfirst($zodiac);
        }

        return $zodiac;
      } else {
        // Handle non-successful response
        return "Error fetching data: " . $response->status();
      }
    } catch (\Exception $e) {
      // Handle exceptions
      return "Exception occurred: " . $e->getMessage();
    }
  }

  public static function getEnemyFriendlyYears($yearStr, $monthStr, $dayStr) {
    $url = getenv('LUNARSOLAR_API') . 'getZodiac';

    try {
      if ($monthStr === "01" || $monthStr === "02") {
        $newMonthStr = str_pad((intval($monthStr) + 6), 2, "0", STR_PAD_LEFT);

        $mainResponse = Http::get($url, [
          'year' => $yearStr,
          'month' => $monthStr,
          'day' => $dayStr
        ]);
        $altResponse = Http::get($url, [
          'year' => $yearStr,
          'month' => $newMonthStr,
          'day' => $dayStr
        ]);


        $zodiacResp = $mainResponse->json()['result'];
        $altZodiacResp = $altResponse->json()['result'];
      } else {
        $response = Http::get($url, [
          'year' => $yearStr,
          'month' => $monthStr,
          'day' => $dayStr
        ]);

        $zodiacResp = $response->json()['result'];
        $altZodiacResp = $zodiacResp; // Assuming this is intentional
      }

      $enemyYears = self::calculateEnemyYears($zodiacResp, $altZodiacResp, $yearStr);
      $friendlyYears = self::calculateFriendlyYears($zodiacResp, $altZodiacResp, $yearStr);

      return ['enemyYears' => $enemyYears, 'friendlyYears' => $friendlyYears];
    } catch (\Exception $e) {
      return $e->getMessage();
    }
  }
}
