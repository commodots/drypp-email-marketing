<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactGroup extends Model
{
    protected $fillable = ['user_id', 'name'];

    public function contacts() {
        return $this->belongsToMany(Contact::class, 'contact_group_items');
    }
}