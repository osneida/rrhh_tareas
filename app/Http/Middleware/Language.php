<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class Language
{
    public function handle(Request $request, Closure $next): Response
    {
        if (session('locale') and array_key_exists(session()->get('locale'), config('languages'))) {
            App::setLocale(session('locale'));
        }else{
            App::setLocale(Config('app.fallback_locale'));
        }
        return $next($request);
    }
}
