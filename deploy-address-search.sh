#!/bin/bash

# Deploy Address Search Functionality
# This script helps deploy the enhanced address search to your server

echo "🚀 Deploying Enhanced Address Search Functionality..."
echo "=================================================="

# 1. Update API routes
echo "📝 Updating API routes..."
cat > routes/api.php << 'EOF'
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WaRuntimeController;
use App\Http\Controllers\AddressController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// WhatsApp runtime
Route::post('/wa/incoming', [WaRuntimeController::class, 'incoming']);

// Address search routes
Route::get('/address/search-vlaanderen', function (Request $request) {
    $q = $request->get('q', '');
    if (empty($q)) {
        return response()->json([]);
    }

    $c = $request->get('c', 10);
    $url = 'https://geo.api.vlaanderen.be/geolocation/v4/Suggestion?q=' . urlencode($q) . '&c=' . $c;
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'User-Agent: PlumberPlatform/1.0 (+https://plumberplatform.com/)'
        ],
    ]);
    
    $res = curl_exec($ch);
    $err = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($err || $code >= 400 || $res === false) {
        return response()->json(['error' => $err ?: "HTTP $code"], $code ?: 502);
    }
    
    return response()->json(json_decode($res, true));
});

Route::get('/address/search-vlaanderen-location', function (Request $request) {
    $q = $request->get('q', '');
    if (empty($q)) {
        return response()->json([]);
    }

    $url = 'https://geo.api.vlaanderen.be/geolocation/v4/Location?q=' . urlencode($q);
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'User-Agent: PlumberPlatform/1.0 (+https://plumberplatform.com/)'
        ],
    ]);
    
    $res = curl_exec($ch);
    $err = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($err || $code >= 400 || $res === false) {
        return response()->json(['error' => $err ?: "HTTP $code"], $code ?: 502);
    }
    
    return response()->json(json_decode($res, true));
});

Route::get('/address/search-osm', function (Request $request) {
    $q = $request->get('q', '');
    if (empty($q)) {
        return response()->json([]);
    }

    $url = 'https://nominatim.openstreetmap.org/search?format=jsonv2&limit=10&countrycodes=be&addressdetails=1&accept-language=nl&q=' . urlencode($q);
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'User-Agent: PlumberPlatform/1.0 (+https://plumberplatform.com/)'
        ],
    ]);
    
    $res = curl_exec($ch);
    $err = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($err || $code >= 400 || $res === false) {
        return response()->json(['error' => $err ?: "HTTP $code"], $code ?: 502);
    }
    
    return response()->json(json_decode($res, true));
});

Route::get('/address/search', function (Request $request) {
    $q = $request->get('q', '');
    if (empty($q)) {
        return response()->json([]);
    }

    // Normalize query for Vlaanderen API
    function normalizeForVL($qRaw) {
        $q = trim($qRaw);
        $q = preg_replace('/\s+/', ' ', $q);
        
        // Move number to end of street name
        if (preg_match('/^(\d+)\s+(.+)/i', $q, $m)) {
            $q = $m[2] . ' ' . $m[1];
        }

        // Add comma before city names
        $cities = ['brugge','brussel','antwerpen','gent','leuven','mechelen','kortrijk','hasselt','oostende','roeselare','aalst','genk','turnhout','lier','waregem','dilbeek','asse','zaventem','knokke','deinze','oudenaarde','eeklo','blankenberge','tienen','wetteren','dendermonde'];
        foreach ($cities as $c) {
            $idx = stripos($q, ' ' . $c);
            if ($idx > 0 && !strpos($q, ',')) {
                $q = substr($q, 0, $idx) . ', ' . substr($q, $idx + 1);
                break;
            }
        }

        // Capitalize first letter of each word
        $q = preg_replace_callback('/\b([a-zà-ÿ])/u', function($m) {
            return mb_strtoupper($m[1]);
        }, $q);
        
        return $q;
    }

    $normalizedQ = normalizeForVL($q);
    
    // Try Vlaanderen Suggestion first
    try {
        $vlUrl = 'https://geo.api.vlaanderen.be/geolocation/v4/Suggestion?q=' . urlencode($normalizedQ) . '&c=10';
        $ch = curl_init($vlUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'User-Agent: PlumberPlatform/1.0 (+https://plumberplatform.com/)'
            ],
        ]);
        
        $res = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($res !== false && $code < 400) {
            $vlResults = json_decode($res, true);
            if (is_array($vlResults) && !empty($vlResults)) {
                return response()->json(['data' => $vlResults, 'source' => 'vl']);
            }
        }
    } catch (Exception $e) {
        // Continue to fallback
    }

    // Try Vlaanderen Location as fallback
    try {
        $vlLocUrl = 'https://geo.api.vlaanderen.be/geolocation/v4/Location?q=' . urlencode($normalizedQ);
        $ch = curl_init($vlLocUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'User-Agent: PlumberPlatform/1.0 (+https://plumberplatform.com/)'
            ],
        ]);
        
        $res = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($res !== false && $code < 400) {
            $vlLocResults = json_decode($res, true);
            if (is_array($vlLocResults) && !empty($vlLocResults)) {
                // Convert to suggestion format
                $mapped = array_map(function($x) {
                    $location = $x['Location'] ?? [];
                    $label = '';
                    if (!empty($location)) {
                        $parts = [];
                        $line1 = trim(($location['Thoroughfarename'] ?? '') . ' ' . ($location['Housenumber'] ?? ''));
                        $line2 = trim(($location['Postalcode'] ?? '') . ' ' . ($location['Municipality'] ?? ''));
                        if ($line1) $parts[] = $line1;
                        if ($line2) $parts[] = $line2;
                        $label = implode(', ', $parts);
                    }
                    return [
                        'Suggestion' => ['Label' => $label],
                        '_vlLoc' => $x
                    ];
                }, $vlLocResults);
                return response()->json(['data' => $mapped, 'source' => 'vl']);
            }
        }
    } catch (Exception $e) {
        // Continue to OSM fallback
    }

    // Final fallback to OSM
    try {
        $osmUrl = 'https://nominatim.openstreetmap.org/search?format=jsonv2&limit=10&countrycodes=be&addressdetails=1&accept-language=nl&q=' . urlencode($q);
        $ch = curl_init($osmUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'User-Agent: PlumberPlatform/1.0 (+https://plumberplatform.com/)'
            ],
        ]);
        
        $res = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($res !== false && $code < 400) {
            $osmResults = json_decode($res, true);
            return response()->json(['data' => $osmResults, 'source' => 'osm']);
        }
    } catch (Exception $e) {
        // Return empty array if all fail
    }

    return response()->json(['data' => [], 'source' => 'none']);
});
EOF

echo "✅ API routes updated!"

# 2. Update WhatsApp Runtime Controller
echo "📝 Updating WhatsApp Runtime Controller..."
# Note: This is a large file, you'll need to manually update it
echo "⚠️  Please manually update app/Http/Controllers/Api/WaRuntimeController.php"
echo "   - Add exit command handling (lines ~70-80)"
echo "   - Add menu option handling (lines ~150-200)"
echo "   - Add helper methods (lines ~1100-1200)"

# 3. Update register view
echo "📝 Updating register view..."
# Note: This is a large file, you'll need to manually update it
echo "⚠️  Please manually update resources/views/auth/register.blade.php"
echo "   - Replace the address search JavaScript section"

# 4. Add test route
echo "📝 Adding test route..."
echo "Route::get('/test-address', function () { return view('test-address'); });" >> routes/web.php

# 5. Clear caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

echo "✅ Deployment script completed!"
echo ""
echo "📋 Manual steps required:"
echo "1. Update app/Http/Controllers/Api/WaRuntimeController.php"
echo "2. Update resources/views/auth/register.blade.php"
echo "3. Test the functionality at /test-address"
echo ""
echo "🎯 Test URLs:"
echo "- Register: /register"
echo "- Address Test: /test-address"
