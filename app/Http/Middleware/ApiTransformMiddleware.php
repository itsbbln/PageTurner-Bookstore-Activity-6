<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;

class ApiTransformMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if (! $response instanceof JsonResponse) {
            return $response;
        }

        $data = $response->getData(true);

        // Field filtering: ?fields=id,title,price
        $fields = $this->parseFields($request->query('fields'));
        if ($fields) {
            $data = $this->filterFields($data, $fields);
        }

        // Snake_case -> camelCase for JSON output
        $data = $this->keysToCamel($data);

        return $response->setData($data);
    }

    private function parseFields(mixed $value): ?array
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $parts = array_values(array_filter(array_map('trim', explode(',', $value))));
        return count($parts) ? $parts : null;
    }

    private function filterFields(mixed $data, array $fields): mixed
    {
        if (is_array($data)) {
            // Laravel paginator shape: ['data' => [...], ...]
            if (array_key_exists('data', $data) && is_array($data['data'])) {
                $data['data'] = array_map(function ($row) use ($fields) {
                    return is_array($row) ? Arr::only($row, $fields) : $row;
                }, $data['data']);

                return $data;
            }

            // Simple list
            if (array_is_list($data)) {
                return array_map(function ($row) use ($fields) {
                    return is_array($row) ? Arr::only($row, $fields) : $row;
                }, $data);
            }

            // Single object
            return Arr::only($data, $fields);
        }

        return $data;
    }

    private function keysToCamel(mixed $data): mixed
    {
        if (! is_array($data)) {
            return $data;
        }

        $out = [];
        foreach ($data as $k => $v) {
            $nk = is_string($k) ? $this->snakeToCamel($k) : $k;
            $out[$nk] = $this->keysToCamel($v);
        }
        return $out;
    }

    private function snakeToCamel(string $key): string
    {
        if (! str_contains($key, '_')) {
            return $key;
        }

        $parts = explode('_', $key);
        $first = array_shift($parts);
        $parts = array_map(fn ($p) => ucfirst($p), $parts);
        return $first . implode('', $parts);
    }
}

