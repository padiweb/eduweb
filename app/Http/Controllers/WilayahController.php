<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class WilayahController extends Controller
{
    private const API        = 'https://emsifa.github.io/api-wilayah-indonesia/api';
    private const CACHE_DAYS = 30;

    private static array $PROVINCES = [
        ['id'=>'11','name'=>'Aceh'],
        ['id'=>'12','name'=>'Sumatera Utara'],
        ['id'=>'13','name'=>'Sumatera Barat'],
        ['id'=>'14','name'=>'Riau'],
        ['id'=>'15','name'=>'Jambi'],
        ['id'=>'16','name'=>'Sumatera Selatan'],
        ['id'=>'17','name'=>'Bengkulu'],
        ['id'=>'18','name'=>'Lampung'],
        ['id'=>'19','name'=>'Kepulauan Bangka Belitung'],
        ['id'=>'21','name'=>'Kepulauan Riau'],
        ['id'=>'31','name'=>'DKI Jakarta'],
        ['id'=>'32','name'=>'Jawa Barat'],
        ['id'=>'33','name'=>'Jawa Tengah'],
        ['id'=>'34','name'=>'DI Yogyakarta'],
        ['id'=>'35','name'=>'Jawa Timur'],
        ['id'=>'36','name'=>'Banten'],
        ['id'=>'51','name'=>'Bali'],
        ['id'=>'52','name'=>'Nusa Tenggara Barat'],
        ['id'=>'53','name'=>'Nusa Tenggara Timur'],
        ['id'=>'61','name'=>'Kalimantan Barat'],
        ['id'=>'62','name'=>'Kalimantan Tengah'],
        ['id'=>'63','name'=>'Kalimantan Selatan'],
        ['id'=>'64','name'=>'Kalimantan Timur'],
        ['id'=>'65','name'=>'Kalimantan Utara'],
        ['id'=>'71','name'=>'Sulawesi Utara'],
        ['id'=>'72','name'=>'Sulawesi Tengah'],
        ['id'=>'73','name'=>'Sulawesi Selatan'],
        ['id'=>'74','name'=>'Sulawesi Tenggara'],
        ['id'=>'75','name'=>'Gorontalo'],
        ['id'=>'76','name'=>'Sulawesi Barat'],
        ['id'=>'81','name'=>'Maluku'],
        ['id'=>'82','name'=>'Maluku Utara'],
        ['id'=>'91','name'=>'Papua Barat'],
        ['id'=>'92','name'=>'Papua'],
        ['id'=>'93','name'=>'Papua Selatan'],
        ['id'=>'94','name'=>'Papua Tengah'],
        ['id'=>'95','name'=>'Papua Pegunungan'],
        ['id'=>'96','name'=>'Papua Barat Daya'],
    ];

    public function getProvinces(): JsonResponse
    {
        return response()->json(self::$PROVINCES)
            ->header('Cache-Control', 'public, max-age=86400');
    }

    public function getRegencies(string $provinceId): JsonResponse
    {
        if (! preg_match('/^\d{2}$/', $provinceId)) {
            return response()->json([], 400);
        }
        return response()->json(
            $this->fetch("/regencies/{$provinceId}.json", "reg.{$provinceId}")
        )->header('Cache-Control', 'public, max-age=3600');
    }

    public function getDistricts(string $regencyId): JsonResponse
    {
        if (! preg_match('/^\d{4}$/', $regencyId)) {
            return response()->json([], 400);
        }
        return response()->json(
            $this->fetch("/districts/{$regencyId}.json", "dist.{$regencyId}")
        )->header('Cache-Control', 'public, max-age=3600');
    }

    public function getVillages(string $districtId): JsonResponse
    {
        if (! preg_match('/^\d{6}$/', $districtId)) {
            return response()->json([], 400);
        }
        return response()->json(
            $this->fetch("/villages/{$districtId}.json", "vill.{$districtId}")
        )->header('Cache-Control', 'public, max-age=3600');
    }

    private function fetch(string $path, string $cacheKey): array
    {
        return Cache::remember(
            "wilayah.{$cacheKey}",
            now()->addDays(self::CACHE_DAYS),
            function () use ($path) {
                try {
                    $r = Http::timeout(15)->retry(2, 300)->get(self::API . $path);
                    return $r->successful() ? $r->json() : [];
                } catch (\Throwable) {
                    return [];
                }
            }
        );
    }
}
