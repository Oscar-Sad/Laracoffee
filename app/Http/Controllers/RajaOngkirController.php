<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RajaOngkirController extends Controller
{
    private $baseUrl = "https://rajaongkir.komerce.id/api/v1";

    private function authHeaders(): array
    {
        return [
            "key" => env("API_RAJAONGKIR"),
        ];
    }

    // 📍 Province
    public function province()
    {
        $response = Http::withHeaders($this->authHeaders())
            ->get($this->baseUrl . "/destination/province");

        if ($response->successful()) {
            return response()->json($response->json()['data']);
        }

        return response()->json([
            'error' => 'Failed to get province',
            'detail' => $response->json()
        ], $response->status());
    }

    // 🏙️ City
    public function city($province_id)
    {
        $response = Http::withHeaders($this->authHeaders())
            ->get($this->baseUrl . "/destination/city/" . $province_id);

        if ($response->successful()) {
            return response()->json($response->json()['data']);
        }

        return response()->json([
            'error' => 'Failed to get city',
            'detail' => $response->json()
        ], $response->status());
    }

    // 🚚 Cost
    public function cost($origin, $destination, $quantity, $courier)
    {
        $response = Http::withHeaders($this->authHeaders())
            ->asForm()
            ->post($this->baseUrl . "/calculate/domestic-cost", [
                "origin" => $origin,
                "destination" => $destination,
                "weight" => $quantity * 1000, // ✅ convert to grams
                "courier" => $courier,
            ]);

        if ($response->successful()) {
            return response()->json($response->json()['data']);
        }

        return response()->json([
            'error' => 'Failed to calculate cost',
            'detail' => $response->json()
        ], $response->status());
    }
}
