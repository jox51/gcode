<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResultsSummary extends Model {
    use HasFactory;
    protected $table = 'results_summaries';

    protected $guarded = [];

    protected $casts = [
        'date' => 'date',
    ];

    public function details() {
        return $this->hasMany(ResultsDetails::class, 'results_summary_id');
    }
}
