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
        // Client flow (any message triggers this for clients)
        $client = WaFlow::updateOrCreate(
            ['code' => 'client_flow'],
            ['name' => 'Client Flow', 'entry_keyword' => 'any', 'target_role' => 'client', 'is_active' => true]
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
                    'no'  => 'no_help_needed',
                ],
                'sort' => 10,
            ]
        );

        // 2) Services selection -> text menu
        WaNode::updateOrCreate(
            ['flow_id' => $client->id, 'code' => 'service_list'],
            [
                'type' => 'text',
                'body' => "What do you need help with? Please reply with the number:\n\n1. Urgent plumber\n2. Emergency plumber\n3. 24/7 plumber\n4. Sanitary repair\n5. Toilet blocked\n6. Toilet overflowing\n7. Drain blocked\n8. Sink not draining\n9. Bathroom tap leaking\n10. Kitchen drain blocked\n11. Water leak\n12. Leaking pipe repair\n13. Low water pressure",
                'sort' => 20,
                'next_map_json' => ['default' => 'service_selection'],
            ]
        );

        // 2b) Service selection handler
        WaNode::updateOrCreate(
            ['flow_id' => $client->id, 'code' => 'service_selection'],
            [
                'type' => 'text',
                'body' => 'Please type a short message for the plumber (e.g., "Water pressure is low").',
                'sort' => 21,
                'next_map_json' => ['default' => 'confirm_broadcast'],
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
                'body' => 'Do you want to send your request to all available plumbers in your area?',
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

        // 5) Dispatch job
        WaNode::updateOrCreate(
            ['flow_id' => $client->id, 'code' => 'dispatch'],
            [
                'type' => 'dispatch',
                'body' => '✅ Request sent. Plumbers will reply soon.',
                'sort' => 50,
                'next_map_json' => ['default' => 'goodbye'],
            ]
        );

        // 6) No help needed
        WaNode::updateOrCreate(
            ['flow_id' => $client->id, 'code' => 'no_help_needed'],
            [
                'type' => 'buttons',
                'body' => 'No problem! If you change your mind or need help later, just send us a message.',
                'options_json' => [
                    ['id' => 'change_mind', 'text' => 'Actually, I do need help'],
                    ['id' => 'goodbye', 'text' => 'Thanks, goodbye'],
                ],
                'next_map_json' => [
                    'change_mind' => 'service_list',
                    'goodbye' => 'goodbye',
                ],
                'sort' => 55,
            ]
        );

        // 7) Goodbye
        WaNode::updateOrCreate(
            ['flow_id' => $client->id, 'code' => 'goodbye'],
            [
                'type' => 'text',
                'body' => 'Thanks for using our service! 👋',
                'sort' => 60,
            ]
        );

        // Plumber flow
        $plumber = WaFlow::updateOrCreate(
            ['code' => 'plumber_flow'],
            ['name' => 'Plumber Flow', 'entry_keyword' => 'any', 'target_role' => 'plumber', 'is_active' => true]
        );

        // P0 (NO numeric prefixes in labels -> avoids “1) 1.”)
        WaNode::updateOrCreate(
            ['flow_id' => $plumber->id, 'code' => 'P0'],
            [
                'type' => 'buttons',
                'body' => "🆕 New job near you!\n\nCustomer: {{customer_name}}\nArea: {{postal_code}} {{city}}\nProblem: {{problem}}\nUrgency: {{urgency_label}}\nDescription: {{description}}\n\nDistance: {{distance_km}} km • ETA: {{eta_min}} min\n\nDo you want to send an offer?",
                'options_json' => [
                    ['id' => 'yes', 'text' => 'Yes, send offer'],
                    ['id' => 'no',  'text' => 'No, skip'],
                ],
                'next_map_json' => [
                    'yes' => 'P1',
                    '1'   => 'P1',
                    'no'  => 'P_END',
                    '2'   => 'P_END',
                ],
                'sort' => 10,
            ]
        );

        WaNode::updateOrCreate(
            ['flow_id' => $plumber->id, 'code' => 'P1'],
            [
                'type' => 'collect_text',
                'body' => "Great! Add a short message for the client (e.g., \"I'm 20 mins away\").",
                'next_map_json' => ['default' => 'P_END'],
                'sort' => 20,
            ]
        );

        WaNode::updateOrCreate(
            ['flow_id' => $plumber->id, 'code' => 'P_END'],
            [
                'type' => 'text',
                'body' => "Thanks! Your offer has been sent. We'll let you know if the client selects you.",
                'sort' => 30,
            ]
        );

        // Unregistered flow
        $unregistered = WaFlow::updateOrCreate(
            ['code' => 'unregistered_flow'],
            ['name' => 'Unregistered Flow', 'entry_keyword' => 'any', 'target_role' => 'any', 'is_active' => true]
        );

        WaNode::updateOrCreate(
            ['flow_id' => $unregistered->id, 'code' => 'register_prompt'],
            [
                'type' => 'text',
                'body' => 'This WhatsApp number is not registered. Please create your account at loodgieter.app',
                'sort' => 10,
            ]
        );
    }
}
