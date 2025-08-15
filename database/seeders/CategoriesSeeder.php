<?php

// database/seeders/CategoriesSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            // Algemeen
            ['label' => 'Loodgieter dringend',   'group' => 'Algemeen',      'sort' => 1],
            ['label' => 'Spoed loodgieter',      'group' => 'Algemeen',      'sort' => 2],
            ['label' => 'Loodgieter 24/7',       'group' => 'Algemeen',      'sort' => 3],
            ['label' => 'Sanitair herstellen',   'group' => 'Algemeen',      'sort' => 4],
            // Probleemgericht
            ['label' => 'WC verstopt',                 'group' => 'Probleemgericht', 'sort' => 5],
            ['label' => 'Toilet loopt over',           'group' => 'Probleemgericht', 'sort' => 6],
            ['label' => 'Afvoer verstopt',             'group' => 'Probleemgericht', 'sort' => 7],
            ['label' => 'Lavabo loopt niet door',      'group' => 'Probleemgericht', 'sort' => 8],
            ['label' => 'Badkamer kraan lekt',         'group' => 'Probleemgericht', 'sort' => 9],
            ['label' => 'Keukenafvoer verstopt',       'group' => 'Probleemgericht', 'sort' => 10],
            ['label' => 'Waterlek in keuken/badkamer', 'group' => 'Probleemgericht', 'sort' => 11],
            ['label' => 'Lekkende buis herstellen',    'group' => 'Probleemgericht', 'sort' => 12],
            ['label' => 'Waterdruk te laag',           'group' => 'Probleemgericht', 'sort' => 13],
        ];

        foreach ($items as $i) {
            Category::updateOrCreate(
                ['label' => $i['label']],
                ['group' => $i['group'], 'sort' => $i['sort']]
            );
        }
    }
}
