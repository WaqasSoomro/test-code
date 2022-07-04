<?php

namespace App\Http\Middleware;

use App\Language;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $locales = Language::get()->pluck('code')->toArray();

        $locale = 'en';
        if($request->hasHeader('locale')){
            if (in_array($request->header('locale'), $locales)) {
                $locale = $request->header('locale');
            }
        }elseif($request->hasHeader('Accept-Language')){
            if (in_array($request->header('Accept-Language'), $locales)) {
                $locale = $request->header('Accept-Language');
            }
        }

        App::setLocale($locale);

        return $next($request);
    }
}
