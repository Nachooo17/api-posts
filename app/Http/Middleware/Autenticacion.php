<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Autenticacion
{
    public function handle(Request $request, Closure $next): Response
    {
        $tokenHeader = $request->header('Authorization');

        if (!$tokenHeader) {
            return response()->json(["error" => "Not authenticated"], 401);
        }

        $token = str_replace('Bearer ', '', $tokenHeader);

        $validacion = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->get(env("API_AUTH_URL") . '/api/validate');

        if ($validacion->status() != 200) {
            return response()->json(["error" => "Invalid Token"], 401);
        }

        $usuario = $validacion->json();

        if (isset($usuario['id'])) {
            Auth::loginUsingId($usuario['id']);
        }

        $request->merge(['user' => $usuario]);

        return $next($request);
    }
}
