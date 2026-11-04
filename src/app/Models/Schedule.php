<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = ['title', 'starts_at', 'opens_at', 'venue', 'address', 'description'];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'opens_at'  => 'datetime',
        ];
    }
}