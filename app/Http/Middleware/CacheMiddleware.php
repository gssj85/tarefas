<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CacheMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $page = $request->page ?? 1;
        $userId = auth()->user()->id;
        $url = request()->url();
        $segments = request()->segments();
        $isPaginated = count($segments) === 1;
        $tag = $segments[0];

        $queryString = null;
        if ($isPaginated) {
            $queryParams = request()->query();
            $queryParams['page'] = $page;
            ksort($queryParams);
            $queryString = '?' . http_build_query($queryParams);
        }

        // Exemplo de como ficaria a chave antes do hash:
        // /tasks: url:http://localhost:8000/tasks?page=1&status=done|userId:1
        // /tasks/{task}: url:http://localhost:8000/tasks/3|userId:1
        $fullUrl = "url:$url$queryString|userId:$userId";
        $rememberKey = sha1($fullUrl);

        return Cache::tags($tag)->remember($rememberKey, now()->addHour(), function () use ($request, $next) {
            return $next($request);
        });
    }
}
