<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyResult extends Model {
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'baseball' => 'array',
        'hockey' => 'array',
        'tennis' => 'array',
        // Cast other sports to array as needed
    ];
}
