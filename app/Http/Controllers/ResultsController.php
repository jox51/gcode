<?php

namespace App\Http\Controllers;

use App\Models\Result;
use App\Models\ResultsSummary;
use App\Services\ResultsBaseballService;
use App\Services\ResultsHockeyService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ResultsController extends Controller {
    public function create() {

        // BASEBALL RESULTS
        // $resultsBaseball = new ResultsBaseballService();
        // $yesterdaysGames = $resultsBaseball->grabResults();

        // HOCKEY RESULTS
        // $hockeyRecords = new ResultsHockeyService();
        // $hockeyRecords->grabHockeyResults();

        $last30DaysSummaries = Result::where('date', '>=', Carbon::now()->subDays(30))
            ->get()->toArray();


        return Inertia::render('Results', [
            'last30DaysSummaries' => $last30DaysSummaries,
        ]);
    }
}
