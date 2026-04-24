<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Extension;
use App\Models\Config;

class DomainCheckController extends Controller
{
    public function check(Request $request)
    {
        $request->validate([
            'keyword' => 'required|string|max:100',
        ]);

        $katakunci = strtolower(trim($request->keyword));

        $batas = (int) (
            Config::where('config_key', 'SEARCH_DOMAIN_LIMIT_SHOW_DOMAIN')->value('config_value') ?? 10
        );

        $extensions = Extension::with('categories')
            ->limit($batas)
            ->get();

        $jawaban = [];

        foreach ($extensions as $yanto) {
            $domain = $katakunci . '.' . ltrim($yanto->extension, '.');

            try {
                $respon = Http::withHeaders([
                    'X-WHOIS-AUTH' => config('services.whois.key'),
                    'Content-Type' => 'application/json',
                ])->post('https://dev-whois.jagoanhosting.com/api/v2/whois', [
                    'domain' => $domain,
                ]);

                $data = $respon->json();

                $isAvailable = data_get($data, 'data.is_available', false);
                $isPremium = data_get($data, 'data.is_premium', false);

                $status = 'unavailable';

                if ($isPremium) {
                    $status = 'premium';
                } elseif ($isAvailable) {
                    $status = 'available';
                }

                $jawaban[] = [
                    'domain' => $domain,
                    'available' => $isAvailable,
                    'is_premium' => $isPremium,
                    'status' => $status,
                    'register_price' => $yanto->register_price,
                    'gimmick_price' => $yanto->gimmick_price,
                    'categories' => $yanto->categories,
                    'raw' => $data,
                ];
            } catch (\Throwable $e) {
                $jawaban[] = [
                    'domain' => $domain,
                    'available' => false,
                    'is_premium' => false,
                    'status' => 'error',
                    'register_price' => $yanto->register_price,
                    'gimmick_price' => $yanto->gimmick_price,
                    'categories' => $yanto->categories,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'keyword' => $katakunci,
            'results' => $jawaban,
        ]);
    }
}