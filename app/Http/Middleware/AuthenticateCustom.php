<?php

namespace App\Http\Middleware;

use App\Models\API\UserAccessToken;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;

class AuthenticateCustom
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
        $user_access_token = UserAccessToken::where('token', $token)
                                                ->where('expires_at', '>', Carbon::now()->toDateTimeString())
                                                ->first();

        if (!$user_access_token){
            return response()->json([
                'data' => ['msg' => 'Unauthentificated'],
            ], 401);
        }

        return $next($request);
    }
}
