<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResultsDetails extends Model {
    use HasFactory;

    protected $table = 'results_details';

    protected $guarded = [];

    public function summary() {
        return $this->belongsTo(ResultsSummary::class);
    }
}
