<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Extension;

class DomainCheckController extends Controller
{
    public function check(Request $request)
    {
        $request->validate([
            'keyword' => 'required|string',
        ]);

        $keyword = strtolower(trim($request->keyword));
        $extensions = Extension::with('categories')->limit(10)->get();

        $results = [];

        foreach ($extensions as $ext) {
            $domain = $keyword . '.' . $ext->extension;

            try {
                $response = Http::withHeaders([
                    'X-WHOIS-AUTH' => config('services.whois.key'),
                    'Content-Type' => 'application/json',
                ])->post('https://dev-whois.jagoanhosting.com/api/v2/whois', [
                    'domain' => $domain,
                ]);

                $data = $response->json();

                $results[] = [
                    'domain' => $domain,
                    'available' => data_get($data, 'data.available', false),
                    'register_price' => $ext->register_price,
                    'gimmick_price' => $ext->gimmick_price,
                    'categories' => $ext->categories,
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'domain' => $domain,
                    'available' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json($results);
    }
}