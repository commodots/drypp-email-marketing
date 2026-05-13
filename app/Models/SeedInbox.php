<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeedInbox extends Model
{
    protected $fillable = ['email', 'provider', 'imap_host', 'imap_port', 'imap_username', 'imap_password'];
}
