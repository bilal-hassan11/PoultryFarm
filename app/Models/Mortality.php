<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DianujHashidsTrait;


class Mortality extends Model
{
    use HasFactory, DianujHashidsTrait;

    protected $table = 'mortality';

    public function shade()
    {
        return $this->belongsTo(Shade::class);
    }
}
