<?php

namespace App\Services\Mail;

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