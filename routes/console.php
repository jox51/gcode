<?php

use App\Services\BaseballRecordService;
use App\Services\HockeyRecordService;
use App\Services\ResultsBaseballService;
use App\Services\ResultsHockeyService;
use App\Services\ResultsMonthlyService;
use App\Services\ResultsTennisService;
use App\Services\TennisRecordService;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();


Schedule::command('mlb:fetch-games')->timezone('America/New_York')->dailyAt('05:00');
Schedule::command('nhl:fetch-games')->timezone('America/New_York')->dailyAt('06:00');
Schedule::command('tennis:fetch-games')->timezone('America/New_York')->twiceDailyAt(4, 6, 30);
Schedule::command('games:fetch-results')->timezone('America/New_York')->dailyAt('7:00');


// Registering a custom console command
Artisan::command('mlb:fetch-games', function (BaseballRecordService $baseballRecordService) {

    $baseballRecordService->fetchAndStoreBaseballData();

    $this->info('MLB games data fetched and saved successfully.');
})->describe('Fetch and process MLB games data');

Artisan::command('nhl:fetch-games', function (HockeyRecordService $hockeyRecordService) {

    $hockeyRecordService->fetchAndStoreHockeyData();

    $this->info('NHL games data fetched and saved successfully.');
})->describe('Fetch and process NHL games data');

Artisan::command('tennis:fetch-games', function (TennisRecordService $tennisRecordService,) {

    $tennisRecordService->fetchAndStoreTennisData();

    $this->info('Tennis games fetched and saved successfully.');
})->describe('Fetch and tennis games');

Artisan::command('games:fetch-results', function (ResultsBaseballService $resultsBaseballService, ResultsHockeyService $resultsHockeyService, ResultsTennisService $resultsTennisService, ResultsMonthlyService $resultsMonthlyService) {

    $resultsBaseballService->grabResults();
    $resultsHockeyService->grabHockeyResults();
    $resultsTennisService->grabTennisResults();
    $resultsMonthlyService->grabResults();

    $this->info('Game results fetched and saved successfully.');
})->describe('Fetch and process game results data');
