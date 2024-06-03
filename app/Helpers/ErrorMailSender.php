<?php

/**
 *
 */
namespace App\Helpers;
use Illuminate\Support\Facades\Mail;
class ErrorMailSender 
{
    public static function sendErrorMail($error_data)
    {
        Mail::raw($error_data, function($message) {
            $message -> from('admin@soniccv.witsinteractive.in', 'Sonic Radar Team');
            $message -> to('amol.thorat@gophygital.io');
            $message -> subject('Error Log From Sonic Radar');
         });
    }
}