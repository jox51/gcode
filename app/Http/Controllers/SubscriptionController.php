<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Http\Controllers\PicksController;

class SubscriptionController extends Controller {

    public function picks(Request $request) {
        // dd($request->user()->subscription_status);
        if ($this->canAccessPicks()) {
            $picks = new PicksController();

            return $picks->create();
        } else return Inertia::render('Welcome');
    }
    private function canAccessPicks() {


        if (Auth::check() && Auth::user()->subscription_status) {

            return true;
        } else {
            return false;
        }
    }
}
