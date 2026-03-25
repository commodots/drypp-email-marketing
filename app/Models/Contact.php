<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email',
        'name',
        'meta',
        'country'
    ];


    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'meta' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function groups()
    {
        return $this->belongsToMany(ContactGroup::class, 'contact_group_items', 'contact_id', 'contact_group_id');
    }
}
