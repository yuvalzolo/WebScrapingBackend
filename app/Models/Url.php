<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Url extends Model
{
    protected $table = 'urls';
    protected $fillable = ['url', 'source_url',  'initial_url'];
}
