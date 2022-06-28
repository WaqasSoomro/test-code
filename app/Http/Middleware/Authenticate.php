<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param Request $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
//        if (! $request->expectsJson()) {
//            return route('login');
//        }

        $middleware = $request->route()->gatherMiddleware();

        $guard = config('auth.defaults.guard');


        foreach ($middleware as $m) {

            if (preg_match("/auth:/", $m)) {
                list($mid, $guard) = explode(":", $m);
            }
        }

        switch ($guard) {

            case 'web':
                $login = 'frontend.getSignIn';
                break;

            case 'admin':
                $login = 'backend.admin.login';
                break;

//            default:
//                $login = '/';
//                break;

            case 'api':
                return;

        }

        return route($login);
    }
}
