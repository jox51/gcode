<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\HockeyRecordService;
use App\Models\HockeyData;
use Carbon\Carbon;

class HockeyController extends Controller {
    public function create() {

        // $hockeyRecords = new HockeyRecordService();
        // $hockeyRecords->fetchAndStoreHockeyData();

        $todayDate = Carbon::now('America/New_York')->format('Y-m-d');
        $hockeyData = HockeyData::whereDate('date', '=', $todayDate)->get()->toArray();


        return Inertia::render('Hockey', [
            'hockeyGames' => $hockeyData,
        ]);
    }
}
