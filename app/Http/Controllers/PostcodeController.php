<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostcodeController extends Controller
{
    // Search postal codes by code or Dutch city name
    public function zoek(Request $request)
    {
        $term = $request->input('term');

        $results = DB::table('postal_codes')
            ->where(function ($query) use ($term) {
                $query->where('Postcode', 'LIKE', "%$term%")
                      ->orWhere('Plaatsnaam_NL', 'LIKE', "%$term%");
            })
            ->limit(15)
            ->get([
                'id',
                'Postcode as postal_code',
                'Plaatsnaam_NL as name_nl',
                'Latitude as latitude',
                'Longitude as longitude'
            ]);

        return response()->json($results);
    }

    // Find nearby postal codes within a radius (km)
    public function radius(Request $request)
    {
        $lat = $request->input('lat');
        $lon = $request->input('lon');
        $km  = $request->input('km', 20); // default 20 km radius

        $results = DB::table('postal_codes')
            ->select(
                'id',
                'Postcode as postal_code',
                'Plaatsnaam_NL as name_nl',
                DB::raw('(6371 * acos(cos(radians(?)) * cos(radians(Latitude)) 
                * cos(radians(Longitude) - radians(?)) + sin(radians(?)) 
                * sin(radians(Latitude)))) AS distance')
            )
            ->addBinding([$lat, $lon, $lat], 'select')
            ->whereNotNull('Latitude')
            ->whereNotNull('Longitude')
            ->having('distance', '<', $km)
            ->orderBy('distance')
            ->limit(20)
            ->get();

        return response()->json($results);
    }
}

