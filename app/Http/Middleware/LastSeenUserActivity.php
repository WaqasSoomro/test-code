<?php

namespace App\Http\Middleware;

use App\User;
use Carbon\Carbon;
use Closure;
use Doctrine\Common\Cache\Cache;
use Illuminate\Support\Facades\Auth;

class LastSeenUserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
//            $expireTime = Carbon::now()->addMinute(1); // keep online for 1 min
//            Cache::put('is_online'.Auth::user()->id, true, $expireTime);

            //Last Seen
            User::where('id', Auth::user()->id)->update(['last_seen' => Carbon::now(), 'online_status' => '1']);
        }
        return $next($request);
    }
}
