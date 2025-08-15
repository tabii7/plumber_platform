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
            ->get();

        // show how many towns each municipality contains (for info)
        $counts = DB::table('postal_codes')
            ->select('Hoofdgemeente', DB::raw('COUNT(DISTINCT CONCAT(Postcode,"|",Plaatsnaam_NL)) as towns_count'))
            ->whereNotNull('Hoofdgemeente')
            ->groupBy('Hoofdgemeente')
            ->pluck('towns_count', 'Hoofdgemeente');

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
}
