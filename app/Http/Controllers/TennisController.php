<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ResultsTennisService;
use App\Services\TennisRecordService;
use App\Models\TennisRecord;
use Carbon\Carbon;
use Inertia\Inertia;

class TennisController extends Controller {
    public function create() {
        // $tennisRecord = new TennisRecordService();
        // $tennisGames = $tennisRecord->fetchAndStoreTennisData();

        $startOfDay = Carbon::today('America/New_York')->startOfDay();
        $endOfDay = Carbon::today('America/New_York')->endOfDay();

        // Query to get records matching today's date
        $tennisGames = TennisRecord::whereBetween('date', [$startOfDay, $endOfDay])->get()->toArray();

        // $resultsTennisService = new ResultsTennisService();
        // $resultsTennisService->grabTennisResults();


        return Inertia::render('Tennis', [
            'tennisGames' => $tennisGames,
        ]);
    }
}
