<?php

namespace App\Services\Mail;

use App\Services\Mail\SendGridProvider;
use App\Services\Mail\SesProvider;
use App\Services\Mail\SmtpProvider;

class MailManager
{
    public static function resolve($smtp)
    {
        if ($smtp->type === 'sendgrid') {
            return new SendGridProvider();
        }

        if ($smtp->type === 'ses') {
            return new SesProvider();
        }

        return new SmtpProvider();
    }
}
