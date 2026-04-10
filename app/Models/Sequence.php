<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sequence extends Model
{
    protected $fillable = 
    [
        'name',
        'user_id'
    ];

    public function steps()
    {
        return $this->hasMany(SequenceStep::class);
    }
}
