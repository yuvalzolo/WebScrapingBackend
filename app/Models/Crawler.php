<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Crawler extends Model
{
    protected $connection = 'mongodb';
    use HasFactory;
}
