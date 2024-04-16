<?php

namespace App\Http\Utils;

use App\Http\Utils\DateUtils;
use App\Http\Utils\ZodiacUtils;
use App\Http\Utils\BirthdaySums;


class CalculationUtils {

  public $birthday;
  public $hasBirthdayPassed;




  public function __construct(string $birthday = '12-25') { // Default birthday can be changed
    $this->birthday = $birthday;
    $this->hasBirthdayPassed = DateUtils::hasBirthdayPassed($this->birthday);
  }

  public static function calculateSum($num) {
    $numMap = [
      28 => [28],
      22 => [22],
      33 => [33],
      11 => [11, 20, 29, 38, 47, 56, 65, 74, 83, 92, 119, 128, 137, 146],
      1 => [1, 10, 19, 28, 37, 46, 55, 64, 73, 82, 91, 100, 109, 118, 127, 136, 145],
      2 => [2],
      3 => [3, 12, 21, 30, 39, 48, 57, 66, 75, 84, 93, 102, 111, 120, 129, 138, 147],
      4 => [4, 13, 31, 40, 49, 58, 67, 76, 85, 94, 103, 112],
      5 => [5, 14, 23, 32, 41, 50, 59, 68, 77, 86, 95, 104, 113],
      6 => [6, 15, 24, 42, 51, 60, 69, 78, 87, 96, 105, 114],
      7 => [7, 16, 25, 34, 43, 52, 61, 70, 79, 88, 97, 106, 115],
      8 => [8, 17, 26, 35, 44, 53, 62, 71, 80, 89, 98, 107, 116],
      9 => [9, 18, 27, 36, 45, 54, 63, 72, 81, 90, 99, 108, 117]
    ];

    foreach ($numMap as $key => $values) {
      if (in_array($num, $values)) {
        return $key;
      }
    }

    return null;
  }


  public static function calculateTotal($num) {
    $numMap = [
      28 => [28],
      22 => [22],
      33 => [33],
      11 => [11, 20, 29, 38, 47, 56, 65, 74, 83, 92, 119, 128, 137, 146],
      1 => [1, 10, 19, 28, 37, 46, 55, 64, 73, 82, 91, 100, 109, 118],
      2 => [2],
      3 => [3, 12, 21, 30, 39, 48, 57, 66, 75, 84, 93, 102, 111, 120, 129, 138, 147],
      4 => [4, 13, 31, 40, 49, 58, 67, 76, 85, 94, 103, 112],
      5 => [5, 14, 23, 32, 41, 50, 59, 68, 77, 86, 95, 104, 113],
      6 => [6, 15, 24, 42, 51, 60, 69, 78, 87, 96, 105, 114],
      7 => [7, 16, 25, 34, 43, 52, 61, 70, 79, 88, 97, 106, 115],
      8 => [8, 17, 26, 35, 44, 53, 62, 71, 80, 89, 98, 107, 116],
      9 => [9, 18, 27, 36, 45, 54, 63, 72, 81, 90, 99, 108, 117]
    ];

    foreach ($numMap as $key => $values) {
      if (in_array($num, $values)) {
        return $key;
      }
    }

    return null;
  }


  public static function calculateSumOfDigits($num) {
    $sum = 0;
    while ($num > 0) {
      $sum += $num % 10;
      $num = intval($num / 10);
    }
    return $sum;
  }

  public static function calculatePersonalYear($sumMonth, $sumDay, $hasBirthdayPassed) {
    $lastYear = intval(date("Y")) - 1;
    $currentYear = intval(date("Y"));
    $nextYear = intval(date("Y")) + 1;

    $sumLastYear = self::calculateSumOfDigits($lastYear);
    $sumCurrYear = self::calculateSumOfDigits($currentYear);
    $sumNextYear = self::calculateSumOfDigits($nextYear);

    $resLastYear = self::calculateSum($sumLastYear);
    $resCurrYear = self::calculateSum($sumCurrYear);
    $resNextYear = self::calculateSum($sumNextYear);

    $resMonth = self::calculateSum($sumMonth);
    $resDay = self::calculateSum($sumDay);

    $currPersonalYear = 0;
    $nextPersonalYear = 0;

    if (!$hasBirthdayPassed) {
      $persYearSum = $resMonth + $resDay + $resLastYear;
      $nextPersYearSum = $resMonth + $resDay + $resCurrYear;
    } else {
      $persYearSum = $resMonth + $resDay + $resCurrYear;
      $nextPersYearSum = $resMonth + $resDay + $resNextYear;
    }

    $currPersonalYear = self::calculateTotal($persYearSum);
    $nextPersonalYear = self::calculateTotal($nextPersYearSum);

    return ['currPersonalYear' => $currPersonalYear, 'nextPersonalYear' => $nextPersonalYear];
  }

  public static function calculateFriendlyEnemyYears($yearStr, $monthStr, $dayStr) {


    $result = ZodiacUtils::getEnemyFriendlyYears($yearStr, $monthStr, $dayStr);

    return $result;
  }

  public static function birthdaySumCalculators($monthStr, $dayStr, $yearStr) {
    $result = BirthdaySums::birthdaySumCalculator($monthStr, $dayStr, $yearStr);

    return $result;
  }
}
