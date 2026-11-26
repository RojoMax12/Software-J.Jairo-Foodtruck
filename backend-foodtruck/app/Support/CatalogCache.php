<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CatalogCache
{
    public const PRODUCTS_KEY = 'catalog:products:all:v2';
    public const PRODUCTS_SUMMARY_KEY = 'catalog:products:summary:v2';
    public const CATEGORIES_KEY = 'catalog:categories:all:v2';
    public const PROMOTIONS_KEY = 'catalog:promotions:all:v2';
    public const TTL_SECONDS = 300;

    public static function rememberProducts(callable $resolver): array
    {
        return self::rememberArray(self::PRODUCTS_KEY, $resolver);
    }

    public static function rememberSummaryProducts(callable $resolver): array
    {
        return self::rememberArray(self::PRODUCTS_SUMMARY_KEY, $resolver);
    }

    public static function rememberCategories(callable $resolver): array
    {
        return self::rememberArray(self::CATEGORIES_KEY, $resolver);
    }

    public static function rememberPromotions(callable $resolver): array
    {
        return self::rememberArray(self::PROMOTIONS_KEY, $resolver);
    }

    /**
     * Almacena arreglos PHP planos en caché para evitar errores de deserialización
     * de modelos/colecciones Eloquent (__PHP_Incomplete_Class).
     */
    protected static function rememberArray(string $key, callable $resolver): array
    {
        $cached = Cache::get($key);
        if (is_array($cached)) {
            return $cached;
        }

        $resolved = $resolver();
        $plain = self::toPlainArray($resolved);
        Cache::put($key, $plain, now()->addSeconds(self::TTL_SECONDS));

        return $plain;
    }

    protected static function toPlainArray(mixed $value): array
    {
        if (is_array($value)) {
            return array_values($value);
        }

        if ($value instanceof \Illuminate\Support\Collection) {
            return array_values($value->toArray());
        }

        $decoded = json_decode(json_encode($value, JSON_UNESCAPED_UNICODE), true);
        return is_array($decoded) ? array_values($decoded) : [];
    }

    public static function invalidate(): void
    {
        Cache::forget('catalog:products:all:v1');
        Cache::forget('catalog:categories:all:v1');
        Cache::forget('catalog:promotions:all:v1');
        Cache::forget(self::PRODUCTS_KEY);
        Cache::forget(self::PRODUCTS_SUMMARY_KEY);
        Cache::forget(self::CATEGORIES_KEY);
        Cache::forget(self::PROMOTIONS_KEY);
    }

    public static function respondWithEtag(Request $request, mixed $data): JsonResponse
    {
        $response = response()->json($data);
        $etag = '"' . md5((string) $response->getContent()) . '"';
        $response->setEtag($etag);
        $response->header('Cache-Control', 'no-cache, must-revalidate');
        $response->isNotModified($request);

        return $response;
    }
}
