<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactGroupItem extends Model
{
    public $timestamps = false;
    protected $fillable = ['contact_id', 'contact_group_id'];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function group()
    {
        return $this->belongsTo(ContactGroup::class, 'contact_group_id');
    }
}