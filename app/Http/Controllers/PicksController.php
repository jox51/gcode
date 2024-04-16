<?php

namespace App\Http\Controllers;

use App\Models\BaseballData;
use App\Services\BaseballRecordService;
use App\Services\BaseballNumerologyService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;



class PicksController extends Controller {
    public function create() {

        // Fetches data from both APIs and stores it in the database
        // $baseballRecords = new BaseballRecordService();
        // $baseballRecords->getBaseballData();
        // $baseballRecords->fetchAndStoreBaseballData();


        // Calculate numerology for today's games and store it in the database
        // $baseballNumerology = new BaseballNumerologyService();
        // $baseballNumerology->calculateGameData();

        // Get today's date in EST and format it to match the date portion of the datetime field
        $todayDate = Carbon::now('America/New_York')->format('Y-m-d');
        $baseballData = BaseballData::whereDate('date', '=', $todayDate)->get()->toArray();





        return Inertia::render('Picks', [
            'baseballGames' => $baseballData,
        ]);
    }
}
