<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetApplicationLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = ['en', 'fil'];
        $preferred = $request->user()?->locale ?? $request->session()->get('locale');
        $locale = in_array($preferred, $supported, true) ? $preferred : config('app.locale');

        $locale = in_array($locale, $supported, true) ? $locale : 'en';
        app()->setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}
