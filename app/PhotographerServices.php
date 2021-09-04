<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhotographerServices extends Model
{
    use HasFactory;

    protected $table = 'photographerservices';
    public $timestamps = false;
}
