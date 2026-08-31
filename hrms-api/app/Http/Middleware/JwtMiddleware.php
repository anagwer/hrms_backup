<?php
namespace App\Http\Middleware;
use Closure;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;

class JwtMiddleware
{
    public function handle($request, Closure $next, $guard = null)
    {
        // $token = $request->get('token');
        $token = $request->bearerToken();

        if(!$token) {
            $status = array(
                "error" => true,
                "message" =>  'Token not provided.',
                "code" => 701
            );
            $result["status"] = $status;
            return response()->json($result);
        }
        try {
            $credentials = JWT::decode($token, env('JWT_SECRET'), [env('JWT_HASH')]);
            //add token to request
            $request->request->add(['creds' => $credentials]);
        } catch(ExpiredException $e) {
            $status = array(
                "error" => true,
                "message" =>  'Provided token is expired.',
                "code" => 702
            );
            $result["status"] = $status;
            return response()->json($result);
        } catch(Exception $e) {
            $status = array(
                "error" => true,
                "message" =>  'An error while decoding token.',
                "code" => 703
            );
            $result["status"] = $status;
            return response()->json($result);
        }
        return $next($request);
    }
}
