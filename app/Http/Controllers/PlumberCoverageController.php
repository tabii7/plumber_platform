<?php

// app/Http/Controllers/PlumberCoverageController.php
namespace App\Http\Controllers;

use App\Models\PlumberCoverage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PlumberCoverageController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        abort_unless($user && $user->role === 'plumber', 403);

        // current selections
        $coverages = PlumberCoverage::where('plumber_id', $user->id)
            ->orderBy('hoofdgemeente')
            ->orderBy('city')
            ->get();

        // Calculate coverage counts based on type
        $counts = [];
        foreach ($coverages as $coverage) {
            if ($coverage->coverage_type === 'municipality') {
                // For municipality coverage, count all towns in that municipality
                $counts[$coverage->hoofdgemeente] = DB::table('postal_codes')
                    ->select(DB::raw('COUNT(DISTINCT CONCAT(Postcode,"|",Plaatsnaam_NL)) as towns_count'))
                    ->where('Hoofdgemeente', $coverage->hoofdgemeente)
                    ->whereNotNull('Hoofdgemeente')
                    ->value('towns_count');
            } else {
                // For city coverage, count only 1 (the specific city)
                $displayKey = $coverage->hoofdgemeente . ' - ' . $coverage->city;
                $counts[$displayKey] = 1;
            }
        }

        return view('plumber.coverage.index', compact('coverages', 'counts'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        abort_unless($user && $user->role === 'plumber', 403);

        $data = $request->validate([
            'hoofdgemeente' => 'required|string|max:255',
        ]);

        // validate it exists in postal_codes
        $exists = DB::table('postal_codes')
            ->where('Hoofdgemeente', $data['hoofdgemeente'])
            ->exists();

        if (! $exists) {
            return back()->with('error', 'Municipality not found.');
        }

        PlumberCoverage::firstOrCreate([
            'plumber_id'    => $user->id,
            'hoofdgemeente' => $data['hoofdgemeente'],
        ]);

        return back()->with('success', 'Coverage added.');
    }

    public function destroy($id)
    {
        $user = Auth::user();
        abort_unless($user && $user->role === 'plumber', 403);

        $coverage = PlumberCoverage::where('plumber_id', $user->id)->findOrFail($id);
        $coverage->delete();

        return back()->with('success', 'Coverage removed.');
    }

    // AJAX: search distinct municipalities
    public function searchMunicipalities(Request $request)
    {
        $term = trim($request->query('term', ''));
        $q = DB::table('postal_codes')
            ->select('Hoofdgemeente')
            ->whereNotNull('Hoofdgemeente');

        if ($term !== '') {
            $q->where('Hoofdgemeente', 'LIKE', "%{$term}%");
        }

        $items = $q->groupBy('Hoofdgemeente')
            ->orderBy('Hoofdgemeente')
            ->limit(20)
            ->pluck('Hoofdgemeente');

        return response()->json($items);
    }

    // AJAX: list towns/postcodes under a municipality (preview)
    public function municipalityTowns($name)
    {
        $rows = DB::table('postal_codes')
            ->select('Postcode', 'Plaatsnaam_NL')
            ->where('Hoofdgemeente', $name)
            ->whereNotNull('Postcode')
            ->whereNotNull('Plaatsnaam_NL')
            ->groupBy('Postcode', 'Plaatsnaam_NL')
            ->orderBy('Postcode')
            ->get();

        return response()->json($rows);
    }

    // AJAX: find nearby municipalities within specified radius
    public function nearbyMunicipalities(Request $request)
    {
        $municipality = $request->query('municipality');
        $radius = $request->query('radius', 20); // Default 20km radius

        if (!$municipality) {
            return response()->json([]);
        }

        // Get the center coordinates of the selected municipality
        $center = DB::table('postal_codes')
            ->select('Latitude', 'Longitude')
            ->where('Hoofdgemeente', $municipality)
            ->whereNotNull('Latitude')
            ->whereNotNull('Longitude')
            ->first();

        if (!$center) {
            return response()->json([]);
        }

        // Find nearby municipalities using Haversine formula
        $nearby = DB::table('postal_codes')
            ->select('Hoofdgemeente', 'Latitude', 'Longitude')
            ->selectRaw('
                (6371 * acos(cos(radians(?)) * cos(radians(Latitude)) * 
                cos(radians(Longitude) - radians(?)) + sin(radians(?)) * 
                sin(radians(Latitude)))) AS distance
            ', [$center->Latitude, $center->Longitude, $center->Latitude])
            ->whereNotNull('Latitude')
            ->whereNotNull('Longitude')
            ->where('Hoofdgemeente', '!=', $municipality)
            ->having('distance', '<=', $radius)
            ->groupBy('Hoofdgemeente', 'Latitude', 'Longitude')
            ->orderBy('distance')
            ->limit(10)
            ->get();

        return response()->json($nearby);
    }

    // AJAX: bulk add multiple municipalities and cities
    public function bulkStore(Request $request)
    {
        $user = Auth::user();
        abort_unless($user && $user->role === 'plumber', 403);

        $data = $request->validate([
            'municipalities' => 'required|array',
            'municipalities.*' => 'string|max:255',
        ]);

        $added = 0;
        $errors = [];
        $processedMunicipalities = []; // Track to avoid duplicates

        foreach ($data['municipalities'] as $item) {
            // First, check if this is a municipality (Hoofdgemeente)
            $municipalityExists = DB::table('postal_codes')
                ->where('Hoofdgemeente', $item)
                ->exists();

            if ($municipalityExists) {
                // This is a municipality, add entire municipality
                $municipality = $item;
                $coverageType = 'municipality';
                $city = null;
                
                // Check if municipality already exists in coverage
                $alreadyExists = PlumberCoverage::where('plumber_id', $user->id)
                    ->where('hoofdgemeente', $municipality)
                    ->where('coverage_type', 'municipality')
                    ->exists();

                if ($alreadyExists) {
                    $errors[] = "Municipality '{$municipality}' already added.";
                    continue;
                }

                // Skip if we already processed this municipality
                if (in_array($municipality, $processedMunicipalities)) {
                    continue;
                }

                PlumberCoverage::create([
                    'plumber_id' => $user->id,
                    'hoofdgemeente' => $municipality,
                    'city' => null,
                    'coverage_type' => 'municipality',
                ]);

                $processedMunicipalities[] = $municipality;
                $added++;
                
            } else {
                // This might be a city, find its parent municipality
                $cityData = DB::table('postal_codes')
                    ->select('Hoofdgemeente', 'Plaatsnaam_NL')
                    ->where('Plaatsnaam_NL', $item)
                    ->first();

                if (!$cityData) {
                    $errors[] = "Item '{$item}' not found as municipality or city.";
                    continue;
                }

                $municipality = $cityData->Hoofdgemeente;
                $city = $cityData->Plaatsnaam_NL;
                
                // Check if this specific city already exists in coverage
                $alreadyExists = PlumberCoverage::where('plumber_id', $user->id)
                    ->where('hoofdgemeente', $municipality)
                    ->where('city', $city)
                    ->where('coverage_type', 'city')
                    ->exists();

                if ($alreadyExists) {
                    $errors[] = "City '{$city}' already added.";
                    continue;
                }
                
                // Check if the entire municipality is already covered
                $municipalityCovered = PlumberCoverage::where('plumber_id', $user->id)
                    ->where('hoofdgemeente', $municipality)
                    ->where('coverage_type', 'municipality')
                    ->exists();

                if ($municipalityCovered) {
                    $errors[] = "Municipality '{$municipality}' already fully covered.";
                    continue;
                }

                PlumberCoverage::create([
                    'plumber_id' => $user->id,
                    'hoofdgemeente' => $municipality,
                    'city' => $city,
                    'coverage_type' => 'city',
                ]);

                $added++;
            }
        }

        $response = [
            'success' => true,
            'added' => $added,
            'errors' => $errors,
        ];

        if ($added > 0) {
            $response['message'] = "Successfully added {$added} coverage area(s).";
        }

        if (!empty($errors)) {
            $response['message'] .= ' ' . implode(' ', $errors);
        }

        return response()->json($response);
    }
}
