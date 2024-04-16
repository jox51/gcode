<?php

namespace App\Http\Utils;

class DateUtils {

  public static function hasBirthdayPassed(string $birthday): bool {
    $currentYear = date('Y');
    $thisYearBirthday = self::thisYearBirthday($birthday, $currentYear);

    return strtotime($thisYearBirthday) < time();
  }


  private static function thisYearBirthday(string $birthday, int $year): string {
    return $year . '-' . $birthday;
  }

  public static function convertUnixToDate($unixTimestamp) {
    $timestamp = is_numeric($unixTimestamp) ? (int)$unixTimestamp : 0;

    return date('m-d-Y', $timestamp);
  }
}
