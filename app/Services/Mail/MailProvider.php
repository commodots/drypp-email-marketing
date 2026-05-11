<?php

namespace App\Services\Mail;

interface MailProvider
{
    public function send($data);
}