<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AddressController extends Controller
{
    /**
     * Get address suggestions from various APIs
     */
    public function suggest(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $count = (int) $request->get('c', 10);
        $detailed = $request->get('detailed', false);
        $forceOsm = $request->get('osm', false);
        
        if (empty(trim($query))) {
            return response()->json([]);
        }

        try {
            // If forceOsm=1, use OSM directly
            if ($forceOsm) {
                $osmResults = $this->getOSMResults($query);
                return response()->json($osmResults);
            }

            // If detailed=1, try to get detailed location data first
            if ($detailed) {
                $vlLocations = $this->getVlaanderenLocations($query);
                if (!empty($vlLocations)) {
                    return response()->json($vlLocations);
                }
            }

            // Try Vlaanderen API first (faster, covers Flanders + Brussels)
            $vlSuggestions = $this->getVlaanderenSuggestions($query, $count);
            if (!empty($vlSuggestions)) {
                // Check if we can get better city information from OSM
                $osmResults = $this->getOSMResults($query);
                if (!empty($osmResults)) {
                    // Merge VL suggestions with OSM city information
                    $enhancedSuggestions = $this->enhanceVLSuggestionsWithOSM($vlSuggestions, $osmResults);
                    if (!empty($enhancedSuggestions)) {
                        return response()->json($enhancedSuggestions);
                    }
                }
                return response()->json($vlSuggestions);
            }

            // Try Vlaanderen Location API as fallback
            $vlLocations = $this->getVlaanderenLocations($query);
            if (!empty($vlLocations)) {
                return response()->json($vlLocations);
            }

            // Final fallback to OSM for all of Belgium
            $osmResults = $this->getOSMResults($query);
            return response()->json($osmResults);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get Vlaanderen API suggestions
     */
    private function getVlaanderenSuggestions(string $query, int $count): array
    {
        $normalizedQuery = $this->normalizeForVL($query);
        $url = "https://geo.api.vlaanderen.be/geolocation/v4/Suggestion?q=" . urlencode($normalizedQuery) . "&c=" . $count;
        
        $results = $this->fetchJSON($url);
        
        // Convert to the format expected by the frontend
        if (isset($results['SuggestionResult']) && is_array($results['SuggestionResult'])) {
            return array_map(function($suggestion) {
                return [
                    'Suggestion' => [
                        'Label' => $suggestion
                    ]
                ];
            }, $results['SuggestionResult']);
        }
        
        return [];
    }

    /**
     * Get Vlaanderen API locations
     */
    private function getVlaanderenLocations(string $query): array
    {
        $normalizedQuery = $this->normalizeForVL($query);
        $url = "https://geo.api.vlaanderen.be/geolocation/v4/Location?q=" . urlencode($normalizedQuery);
        
        $results = $this->fetchJSON($url);
        
        // Convert to suggestion format
        if (isset($results['LocationResult']) && is_array($results['LocationResult'])) {
            return array_map(function($item) {
                // The Vlaanderen Location API returns data directly in the item, not in a nested Location object
                $location = [
                    'Thoroughfarename' => $item['Thoroughfarename'] ?? '',
                    'Housenumber' => $item['Housenumber'] ?? '',
                    'Postalcode' => $item['Zipcode'] ?? '',
                    'Municipality' => $item['Municipality'] ?? '',
                    'Lat_WGS84' => $item['Location']['Lat_WGS84'] ?? '',
                    'Lon_WGS84' => $item['Location']['Lon_WGS84'] ?? ''
                ];
                
                return [
                    'Suggestion' => [
                        'Label' => $this->formatVLLabelFromLocation($location)
                    ],
                    '_vlLoc' => [
                        'Location' => $location
                    ]
                ];
            }, $results['LocationResult']);
        }
        
        return [];
    }

    /**
     * Get OSM results for all of Belgium
     */
    private function getOSMResults(string $query): array
    {
        $url = "https://nominatim.openstreetmap.org/search?format=jsonv2&limit=10&countrycodes=be&addressdetails=1&accept-language=nl&q=" . urlencode($query);
        
        return $this->fetchJSON($url);
    }

    /**
     * Enhance VL suggestions with OSM city information
     */
    private function enhanceVLSuggestionsWithOSM(array $vlSuggestions, array $osmResults): array
    {
        $enhanced = [];
        
        foreach ($vlSuggestions as $vlSuggestion) {
            $vlLabel = $vlSuggestion['Suggestion']['Label'] ?? '';
            
            // Try to find matching OSM result
            $matchingOSM = null;
            foreach ($osmResults as $osmResult) {
                $osmDisplayName = $osmResult['display_name'] ?? '';
                $osmAddress = $osmResult['address'] ?? [];
                
                // Check if this OSM result matches the VL suggestion
                if ($this->addressesMatch($vlLabel, $osmDisplayName, $osmAddress)) {
                    $matchingOSM = $osmResult;
                    break;
                }
            }
            
            if ($matchingOSM) {
                // Create enhanced suggestion with village name
                $osmAddress = $matchingOSM['address'] ?? [];
                $village = $osmAddress['village'] ?? '';
                $town = $osmAddress['town'] ?? '';
                $postalCode = $osmAddress['postcode'] ?? '';
                
                // Replace town with village in the label if village exists
                if ($village && $town) {
                    $enhancedLabel = str_replace($town, $village, $vlLabel);
                } else {
                    $enhancedLabel = $vlLabel;
                }
                
                $enhanced[] = [
                    'Suggestion' => [
                        'Label' => $enhancedLabel
                    ],
                    '_osmData' => $matchingOSM
                ];
            } else {
                // Keep original VL suggestion if no OSM match
                $enhanced[] = $vlSuggestion;
            }
        }
        
        return $enhanced;
    }

    /**
     * Check if VL suggestion matches OSM result
     */
    private function addressesMatch(string $vlLabel, string $osmDisplayName, array $osmAddress): bool
    {
        // Extract street name and number from VL label
        $vlParts = explode(', ', $vlLabel);
        $vlStreetPart = $vlParts[0] ?? '';
        
        // Extract street name and number from OSM
        $osmRoad = $osmAddress['road'] ?? '';
        $osmHouseNumber = $osmAddress['house_number'] ?? '';
        $osmStreetPart = trim($osmRoad . ' ' . $osmHouseNumber);
        
        // Check if street parts match (case insensitive)
        return strtolower(trim($vlStreetPart)) === strtolower(trim($osmStreetPart));
    }

    /**
     * Normalize query for Vlaanderen API
     */
    private function normalizeForVL(string $query): string
    {
        $q = trim($query);
        $q = preg_replace('/\s+/', ' ', $q);
        
        // Move number to end of street name: "26 karel de stoutelaan brugge" -> "karel de stoutelaan 26, brugge"
        if (preg_match('/^(\d+)\s+(.+)/i', $q, $matches)) {
            $q = $matches[2] . ' ' . $matches[1];
        }

        // Add comma before common city names
        $cities = [
            'brugge', 'brussel', 'antwerpen', 'gent', 'leuven', 'mechelen', 'kortrijk', 
            'hasselt', 'oostende', 'roeselare', 'aalst', 'genk', 'turnhout', 'lier', 
            'waregem', 'dilbeek', 'asse', 'zaventem', 'knokke', 'deinze', 'oudenaarde', 
            'eeklo', 'blankenberge', 'tienen', 'wetteren', 'dendermonde', 'beerse'
        ];
        
        foreach ($cities as $city) {
            $pos = strripos($q, ' ' . $city);
            if ($pos > 0 && !strpos($q, ',')) {
                $q = substr($q, 0, $pos) . ', ' . substr($q, $pos + 1);
                break;
            }
        }

        // Basic capitalization
        $q = preg_replace_callback('/\b([a-zà-ÿ])/', function($matches) {
            return strtoupper($matches[1]);
        }, $q);
        
        return $q;
    }

    /**
     * Format Vlaanderen location data into a readable label
     */
    private function formatVLLabelFromLocation(array $location): string
    {
        $parts = [];
        $line1 = trim(($location['Thoroughfarename'] ?? '') . ' ' . ($location['Housenumber'] ?? ''));
        $line2 = trim(($location['Postalcode'] ?? '') . ' ' . ($location['Municipality'] ?? ''));
        
        if ($line1) $parts[] = $line1;
        if ($line2) $parts[] = $line2;
        
        return implode(', ', $parts);
    }

    /**
     * Fetch JSON from external API
     */
    private function fetchJSON(string $url): array
    {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_HTTPHEADER => [
                    'Accept: application/json',
                'User-Agent: PlumberPlatform/1.0 (+https://plumber-platform.com/)'
                ],
            ]);
            
            $response = curl_exec($ch);
            $error = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($error || $httpCode >= 400 || $response === false) {
            throw new \Exception("API request failed: " . ($error ?: "HTTP $httpCode"));
            }

            $data = json_decode($response, true);
        return is_array($data) ? $data : [];
    }
}