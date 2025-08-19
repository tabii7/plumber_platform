<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AddressController extends Controller
{
    /**
     * Search addresses using OpenStreetMap Nominatim API
     */
    public function search(Request $request): JsonResponse
    {
        $query = trim($request->get('q', ''));
        
        if (empty($query) || strlen($query) < 2) {
            return response()->json([]);
        }

        try {
            $url = 'https://nominatim.openstreetmap.org/search?' . http_build_query([
                'format' => 'jsonv2',
                'limit' => 10,
                'countrycodes' => 'be', // Belgium
                'addressdetails' => 1,
                'accept-language' => 'nl',
                'q' => $query
            ]);

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_HTTPHEADER => [
                    'Accept: application/json',
                    'User-Agent: PlumberPlatform/1.0 (+https://plumber.app/)'
                ],
            ]);
            
            $response = curl_exec($ch);
            $error = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($error || $httpCode >= 400 || $response === false) {
                return response()->json(['error' => 'Address search service unavailable'], 503);
            }

            $data = json_decode($response, true);
            
            if (!is_array($data)) {
                return response()->json([]);
            }

            return response()->json($data);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Address search failed'], 500);
        }
    }

    /**
     * Search addresses using Vlaanderen API (for Belgian addresses)
     */
    public function searchVlaanderen(Request $request): JsonResponse
    {
        $query = trim($request->get('q', ''));
        
        if (empty($query) || strlen($query) < 2) {
            return response()->json([]);
        }

        try {
            // Try Vlaanderen Suggestion API first
            $url = 'https://geo.api.vlaanderen.be/geolocation/v4/Suggestion?' . http_build_query([
                'q' => $query,
                'c' => 10
            ]);

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_HTTPHEADER => [
                    'Accept: application/json',
                    'User-Agent: PlumberPlatform/1.0 (+https://plumber.app/)'
                ],
            ]);
            
            $response = curl_exec($ch);
            $error = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($error || $httpCode >= 400 || $response === false) {
                return response()->json([]);
            }

            $data = json_decode($response, true);
            
            if (!is_array($data)) {
                return response()->json([]);
            }

            return response()->json($data);

        } catch (\Exception $e) {
            return response()->json([]);
        }
    }
}
