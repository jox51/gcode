<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoulMate extends Model {
    use HasFactory;
    protected $table = 'soul_mates';
    protected $fillable = ['date', 'soul_mates'];
}
