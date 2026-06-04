<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UpdateMonitor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'url',
        'last_version',
    ];
}
