<?php

namespace DownGrade\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * Indicates whether the XSRF-TOKEN cookie should be set on the response.
     *
     * @var bool
     */
    protected $addHttpCookie = true;

    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        '/razorpay',
		'/subscription-razorpay',
		'/coingate',
		'/payhere-success',
		'/mercadopago-success',
		'/mercadopago-success/*',
		'/subscription-mercadopago',
		'/subscription-mercadopago/*',
		'/coinbase',
		'/coinbase/*',
		'/webhooks/coinbase-checkout',
		'/webhooks/coinbase-subscription',
		'/subscription-coinbase',
		'/subscription-coinbase/*',
    ];
}
