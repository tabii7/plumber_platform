<?php

// database/seeders/WaDefaultSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WaFlow;
use App\Models\WaNode;

class WaDefaultSeeder extends Seeder
{
    public function run(): void
    {
        // Client flow (entry keyword 'info')
        $client = WaFlow::updateOrCreate(
            ['code' => 'client_flow'],
            ['name' => 'Client Flow', 'entry_keyword' => 'info', 'target_role' => 'client', 'is_active' => true]
        );

        // 1) Welcome/confirm city -> buttons YES/NO
        WaNode::updateOrCreate(
            ['flow_id' => $client->id, 'code' => 'confirm_city'],
            [
                'type' => 'buttons',
                'body' => 'Hello {{first_name}}, do you need a plumber in {{postal_code}} - {{city}}?',
                'footer' => 'Reply with buttons',
                'options_json' => [
                    ['id' => 'yes', 'text' => 'YES'],
                    ['id' => 'no',  'text' => 'NO'],
                ],
                'next_map_json' => [
                    'yes' => 'service_list',
                    'no'  => 'goodbye',
                ],
                'sort' => 10,
            ]
        );

        // 2) Services list -> list message
        WaNode::updateOrCreate(
            ['flow_id' => $client->id, 'code' => 'service_list'],
            [
                'type' => 'list',
                'title' => 'Choose a service',
                'body'  => 'Select the issue you need help with:',
                'footer'=> 'Tap to choose',
                'options_json' => [
                    [
                        'title' => 'Algemeen',
                        'rows' => [
                            ['id' => 'cat:1', 'title' => 'Loodgieter dringend'],
                            ['id' => 'cat:2', 'title' => 'Spoed loodgieter'],
                            ['id' => 'cat:3', 'title' => 'Loodgieter 24/7'],
                            ['id' => 'cat:4', 'title' => 'Sanitair herstellen'],
                        ],
                    ],
                    [
                        'title' => 'Probleemgericht',
                        'rows' => [
                            ['id' => 'cat:5',  'title' => 'WC verstopt'],
                            ['id' => 'cat:6',  'title' => 'Toilet loopt over'],
                            ['id' => 'cat:7',  'title' => 'Afvoer verstopt'],
                            ['id' => 'cat:8',  'title' => 'Lavabo loopt niet door'],
                            ['id' => 'cat:9',  'title' => 'Badkamer kraan lekt'],
                            ['id' => 'cat:10', 'title' => 'Keukenafvoer verstopt'],
                            ['id' => 'cat:11', 'title' => 'Waterlek in keuken/badkamer'],
                            ['id' => 'cat:12', 'title' => 'Lekkende buis herstellen'],
                            ['id' => 'cat:13', 'title' => 'Waterdruk te laag'],
                        ],
                    ],
                ],
                'next_map_json' => [
                    // any rowId starting with 'cat:' -> go to ask_description
                    'default' => 'ask_description'
                ],
                'sort' => 20,
            ]
        );

        // 3) Ask description -> collect_text
        WaNode::updateOrCreate(
            ['flow_id' => $client->id, 'code' => 'ask_description'],
            [
                'type' => 'collect_text',
                'body' => 'Please type a short message for the plumber (e.g., "Water pressure is low").',
                'sort' => 30,
                'next_map_json' => ['default' => 'confirm_broadcast'],
            ]
        );

        // 4) Confirm broadcast -> buttons YES/NO
        WaNode::updateOrCreate(
            ['flow_id' => $client->id, 'code' => 'confirm_broadcast'],
            [
                'type' => 'buttons',
                'body' => 'Send your request to all available plumbers in your coverage area?',
                'options_json' => [
                    ['id' => 'yes', 'text' => 'YES, send'],
                    ['id' => 'no',  'text' => 'NO, cancel'],
                ],
                'next_map_json' => [
                    'yes' => 'dispatch',
                    'no'  => 'goodbye',
                ],
                'sort' => 40,
            ]
        );

        // 5) Dispatch job -> backend sends to plumbers based on municipality+category selection
        WaNode::updateOrCreate(
            ['flow_id' => $client->id, 'code' => 'dispatch'],
            [
                'type' => 'dispatch',
                'body' => '✅ Request sent. Plumbers will reply soon.',
                'sort' => 50,
                'next_map_json' => ['default' => 'goodbye'],
            ]
        );

        // 6) Goodbye
        WaNode::updateOrCreate(
            ['flow_id' => $client->id, 'code' => 'goodbye'],
            [
                'type' => 'text',
                'body' => 'Thanks for using our service! 👋',
                'sort' => 60,
            ]
        );

        // Plumber flow (entry keyword 'plumber')
        $plumber = WaFlow::updateOrCreate(
            ['code' => 'plumber_flow'],
            ['name' => 'Plumber Flow', 'entry_keyword' => 'plumber', 'target_role' => 'plumber', 'is_active' => true]
        );

        WaNode::updateOrCreate(
            ['flow_id' => $plumber->id, 'code' => 'status_prompt'],
            [
                'type' => 'buttons',
                'body' => 'Set your status:',
                'options_json' => [
                    ['id' => 'available', 'text' => 'Available'],
                    ['id' => 'busy',      'text' => 'Busy'],
                    ['id' => 'holiday',   'text' => 'On holiday'],
                ],
                'next_map_json' => [
                    'available' => 'status_saved',
                    'busy'      => 'status_saved',
                    'holiday'   => 'status_saved',
                ],
                'sort' => 10,
            ]
        );

        WaNode::updateOrCreate(
            ['flow_id' => $plumber->id, 'code' => 'status_saved'],
            [
                'type' => 'text',
                'body' => '✅ Status saved: {{status}}',
                'sort' => 20,
            ]
        );
    }
}
