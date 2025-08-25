<?php
return [
    'origin' => env('RECAPTCHAV3_ORIGIN', 'https://www.google.com/recaptcha'),
    'sitekey' => config('recaptchav3.sitekey'),
    'secret' => config('recaptchav3.secret'),
    'locale' => env('RECAPTCHAV3_LOCALE', '')
];
