<?php

namespace App\Http\Utils;

class BirthdaySums {

  public static function birthdaySumCalculator($monthStr, $dayStr, $yearStr) {
    $rawMonth = intval($monthStr);
    $rawDay = intval($dayStr);
    $rawYear = intval($yearStr);

    // Calculate sumMonth
    $sumMonth = self::calculateSum($rawMonth);

    // Calculate sumDay
    $sumDay = self::calculateSum($rawDay);

    // Calculate sumYear
    $sumYear = self::calculateSum($rawYear);

    // Additional logic based on hasBirthdayPassed or other calculations can be added here

    return [
      'sumMonth' => $sumMonth,
      'sumDay' => $sumDay,
      'sumYear' => $sumYear
    ];
  }

  private static function calculateSum($number) {
    if (in_array($number, [11, 22, 28])) {
      return $number;
    } else {
      return array_sum(str_split($number));
    }
  }
}
