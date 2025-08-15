<?php

namespace App\Http\Controllers;

use App\Models\Plumber;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class PlumberController extends Controller
{
    public function index()
    {
        // include user for name/email in the table
        $plumbers = Plumber::with('user')->latest()->paginate(20);
        return view('admin.plumbers.index', compact('plumbers'));
    }

    public function create()
    {
        // Categories for checkboxes
        $categories = Category::orderBy('id')->get();

        // Distinct Hoofdgemeente list from postal_codes
        $municipalities = DB::table('postal_codes')
            ->whereNotNull('Hoofdgemeente')
            ->distinct()
            ->orderBy('Hoofdgemeente')
            ->pluck('Hoofdgemeente')
            ->toArray();

        return view('admin.plumbers.create', compact('categories', 'municipalities'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            // User fields
            'full_name'        => ['required','string','max:255'],
            'email'            => ['required','email','max:255','unique:users,email'],
            'phone'            => ['nullable','string','max:20'],
            'whatsapp_number'  => ['nullable','string','max:20'],
            'postal_code'      => ['nullable','string','max:10'],
            'city'             => ['nullable','string','max:255'],
            'country'          => ['nullable','string','max:255'],

            // Plumber fields
            'tariff'              => ['required','numeric','min:0'],
            'availability_status' => ['nullable', Rule::in(['available','busy','holiday'])],
            'municipalities'      => ['sometimes','array'],
            'municipalities.*'    => ['string','max:255'],
            'municipalities_csv'  => ['sometimes','nullable','string'],
            'categories'          => ['required','array','min:1'],
            'categories.*'        => ['integer'], // IDs of Category
        ]);

        // Build municipalities array from multi-select or CSV fallback
        $munis = collect($data['municipalities'] ?? [])
            ->when(!empty($data['municipalities_csv'] ?? null), function ($c) use ($data) {
                $extra = collect(explode(',', $data['municipalities_csv']))
                    ->map(fn($v) => trim($v))
                    ->filter();
                return $c->merge($extra);
            })
            ->unique()
            ->values()
            ->all();

         
        // Create the linked user with role=plumber.
        // No password was in the form, so give a random one (admin can reset/send later).
        $user = User::create([
            'full_name'       => $data['full_name'],
            'email'           => $data['email'],
            'phone'           => $data['phone'] ?? null,
            'whatsapp_number' => $data['whatsapp_number'] ?? null,
            'postal_code'     => $data['postal_code'] ?? null,
            'city'            => $data['city'] ?? null,
            'country'         => $data['country'] ?? null,
            'role'            => 'plumber',
            'password'        => Hash::make(Str::random(16)),
        ]);

        Plumber::create([
            'user_id'             => $user->id,
            'tariff'              => $data['tariff'],
            'availability_status' => $data['availability_status'] ?? 'available',
            // keep compatibility with your schema: store JSON
            'service_categories'  => json_encode($data['categories']),
            'municipalities'      => json_encode($munis),
            // optional: keep postal_codes empty when using municipalities logic
            'postal_codes'        => null,
        ]);

        return redirect()->route('plumbers.index')->with('success', 'Plumber created successfully.');
    }

    public function edit(Plumber $plumber)
    {
        $plumber->load('user');

        $categories = Category::orderBy('name')->get();
        $municipalities = DB::table('postal_codes')
            ->whereNotNull('Hoofdgemeente')
            ->distinct()
            ->orderBy('Hoofdgemeente')
            ->pluck('Hoofdgemeente')
            ->toArray();

        // Prepare selected data for the form
        $selectedMunicipalities = is_string($plumber->municipalities)
            ? (json_decode($plumber->municipalities, true) ?: [])
            : ($plumber->municipalities ?? []);

        $selectedCategories = is_string($plumber->service_categories)
            ? (json_decode($plumber->service_categories, true) ?: [])
            : ($plumber->service_categories ?? []);

        return view('admin.plumbers.edit', compact(
            'plumber', 'categories', 'municipalities', 'selectedMunicipalities', 'selectedCategories'
        ));
    }

    public function update(Request $request, Plumber $plumber)
    {
        $plumber->load('user');

        $data = $request->validate([
            // User fields
            'full_name'        => ['required','string','max:255'],
            'email'            => ['required','email','max:255', Rule::unique('users','email')->ignore($plumber->user_id)],
            'phone'            => ['nullable','string','max:20'],
            'whatsapp_number'  => ['nullable','string','max:20'],
            'postal_code'      => ['nullable','string','max:10'],
            'city'             => ['nullable','string','max:255'],
            'country'          => ['nullable','string','max:255'],

            // Plumber fields
            'tariff'              => ['required','numeric','min:0'],
            'availability_status' => ['nullable', Rule::in(['available','busy','holiday'])],
            'municipalities'      => ['sometimes','array'],
            'municipalities.*'    => ['string','max:255'],
            'municipalities_csv'  => ['sometimes','nullable','string'],
            'categories'          => ['required','array','min:1'],
            'categories.*'        => ['integer'],
        ]);

        $munis = collect($data['municipalities'] ?? [])
            ->when(!empty($data['municipalities_csv'] ?? null), function ($c) use ($data) {
                $extra = collect(explode(',', $data['municipalities_csv']))
                    ->map(fn($v) => trim($v))
                    ->filter();
                return $c->merge($extra);
            })
            ->unique()
            ->values()
            ->all();

        // Update user
        $plumber->user->update([
            'full_name'       => $data['full_name'],
            'email'           => $data['email'],
            'phone'           => $data['phone'] ?? null,
            'whatsapp_number' => $data['whatsapp_number'] ?? null,
            'postal_code'     => $data['postal_code'] ?? null,
            'city'            => $data['city'] ?? null,
            'country'         => $data['country'] ?? null,
        ]);

        // Update plumber
        $plumber->update([
            'tariff'              => $data['tariff'],
            'availability_status' => $data['availability_status'] ?? $plumber->availability_status,
            'service_categories'  => json_encode($data['categories']),
            'municipalities'      => json_encode($munis),
        ]);

        return redirect()->route('plumbers.index')->with('success', 'Plumber updated successfully.');
    }

    public function destroy(Plumber $plumber)
    {
        // Optional: do NOT delete the linked user to keep history,
        // or delete both if that's your policy.
        $plumber->delete();

        return redirect()->route('plumbers.index')->with('success', 'Plumber removed.');
    }
}
