<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\PlumberCoverage;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add default municipality coverage for existing plumbers who don't have any coverage areas
        $plumbersWithoutCoverage = User::where('role', 'plumber')
            ->whereNotNull('city')
            ->whereDoesntHave('coverages')
            ->get();

        foreach ($plumbersWithoutCoverage as $plumber) {
            // Find the user's municipality based on their city
            $userMunicipality = DB::table('postal_codes')
                ->where('Plaatsnaam_NL', $plumber->city)
                ->whereNotNull('Hoofdgemeente')
                ->value('Hoofdgemeente');

            if ($userMunicipality) {
                PlumberCoverage::create([
                    'plumber_id' => $plumber->id,
                    'hoofdgemeente' => $userMunicipality,
                    'coverage_type' => 'municipality',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration only adds data, so we don't need to reverse it
        // If you want to remove the added coverage areas, you would need to:
        // 1. Identify which coverage areas were added by this migration
        // 2. Remove them (but this could affect user data, so it's not recommended)
    }
};