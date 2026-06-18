<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesVolume extends Model
{
    protected $table = 'sales_volumes';
    protected $fillable = ['year', 'month', 'quantity', 'is_forecast'];
}
