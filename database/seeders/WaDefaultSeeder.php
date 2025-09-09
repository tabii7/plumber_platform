<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WaFlow;
use App\Models\WaNode;

class WaDefaultSeeder extends Seeder
{
    public function run(): void
    {
        /**
         * ===========================
         * Client Flow
         * ===========================
         */
        $client = WaFlow::updateOrCreate(
            ['code' => 'client_flow'],
            ['name' => 'Client Flow', 'entry_keyword' => 'any', 'target_role' => 'client', 'is_active' => true]
        );

        // C0 — greet/confirm area
        WaNode::updateOrCreate(
            ['flow_id' => $client->id, 'code' => 'C0'],
            [
                'type' => 'buttons',
                'body' => 'Hello {{first_name}}, do you need a plumber in {{postal_code}} - {{city}}?',
                'footer' => 'Reply with buttons',
                'options_json' => [
                    ['id' => 'yes', 'text' => 'Yes'],
                    ['id' => 'no',  'text' => 'No'],
                ],
                'next_map_json' => [
                    'yes' => 'C1',
                    '1'   => 'C1',
                    'no'  => 'goodbye',
                    '2'   => 'goodbye',
                    'default' => 'C1',
                ],
                'sort' => 10,
            ]
        );

        // C1 — problem
        WaNode::updateOrCreate(
            ['flow_id' => $client->id, 'code' => 'C1'],
            [
                'type' => 'buttons',
                'body' => "What do you need help with?",
                'options_json' => [
                    ['id' => 'leak',         'text' => 'Leak'],
                    ['id' => 'blockage',     'text' => 'Blockage / Drain'],
                    ['id' => 'heating',      'text' => 'Heating / Boiler'],
                    ['id' => 'installation', 'text' => 'Installation / Replacement'],
                    ['id' => 'other',        'text' => 'Other'],
                ],
                'next_map_json' => [
                    'leak'=>'C2','blockage'=>'C2','heating'=>'C2','installation'=>'C2','other'=>'C2',
                    '1'=>'C2','2'=>'C2','3'=>'C2','4'=>'C2','5'=>'C2','default'=>'C2',
                ],
                'sort' => 20,
            ]
        );

        // C2 — urgency
        WaNode::updateOrCreate(
            ['flow_id' => $client->id, 'code' => 'C2'],
            [
                'type' => 'buttons',
                'body' => "How urgent is it?",
                'options_json' => [
                    ['id' => 'high',   'text' => 'High — max 60 min'],
                    ['id' => 'normal', 'text' => 'Normal — max 2 hours'],
                    ['id' => 'later',  'text' => 'Later today / schedule'],
                ],
                'next_map_json' => [
                    'high'=>'C3','normal'=>'C3','later'=>'C3',
                    '1'=>'C3','2'=>'C3','3'=>'C3','default'=>'C3',
                ],
                'sort' => 30,
            ]
        );

        // C3 — description
        WaNode::updateOrCreate(
            ['flow_id' => $client->id, 'code' => 'C3'],
            [
                'type' => 'collect_text',
                'body' => 'Please type a short message for the plumber (e.g., "Water pressure is low").',
                'next_map_json' => ['default' => 'C4'],
                'sort' => 40,
            ]
        );

        // C4 — consent to broadcast
        WaNode::updateOrCreate(
            ['flow_id' => $client->id, 'code' => 'C4'],
            [
                'type' => 'buttons',
                'body' => 'Do you want to send your request to all available plumbers in your area?',
                'options_json' => [
                    ['id' => 'yes', 'text' => 'Yes, send'],
                    ['id' => 'no',  'text' => 'No, cancel'],
                ],
                'next_map_json' => [
                    'yes'=>'C5','1'=>'C5','no'=>'goodbye','2'=>'goodbye','default'=>'C5',
                ],
                'sort' => 50,
            ]
        );

        // C5 — waiting
        WaNode::updateOrCreate(
            ['flow_id' => $client->id, 'code' => 'C5'],
            [
                'type' => 'text',
                'body' => "✅ Request sent. Plumbers will reply soon.\nType 'offers' anytime to view responses.",
                'sort' => 60,
            ]
        );

        // C6 — offers list placeholder
        WaNode::updateOrCreate(
            ['flow_id' => $client->id, 'code' => 'C6'],
            [
                'type' => 'text',
                'body' => "Plumbers who accepted your job will appear here.\nSend a number like 1, 2, 3 to view details.",
                'sort' => 70,
            ]
        );

        // C7 — offer details placeholder
        WaNode::updateOrCreate(
            ['flow_id' => $client->id, 'code' => 'C7'],
            [
                'type' => 'text',
                'body' => "Confirm selection?\nReply 'yes' to select, 'no' to go back, or '3' to choose again.",
                'sort' => 80,
            ]
        );

        // goodbye
        WaNode::updateOrCreate(
            ['flow_id' => $client->id, 'code' => 'goodbye'],
            [
                'type' => 'text',
                'body' => 'Thanks for using our service! 👋',
                'sort' => 90,
            ]
        );

        /**
         * ===========================
         * Plumber Flow
         * ===========================
         */
        $plumber = WaFlow::updateOrCreate(
            ['code' => 'plumber_flow'],
            ['name' => 'Plumber Flow', 'entry_keyword' => 'any', 'target_role' => 'plumber', 'is_active' => true]
        );

        // P0 — new job broadcast
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
                    'yes'=>'P1','1'=>'P1','no'=>'P_END','2'=>'P_END',
                ],
                'sort' => 10,
            ]
        );

        // P1 — plumber message
        WaNode::updateOrCreate(
            ['flow_id' => $plumber->id, 'code' => 'P1'],
            [
                'type' => 'collect_text',
                'body' => "Great! Add a short message for the client (e.g., \"I'm 20 mins away\").",
                'next_map_json' => ['default' => 'P_END'],
                'sort' => 20,
            ]
        );

        // P_END — ack
        WaNode::updateOrCreate(
            ['flow_id' => $plumber->id, 'code' => 'P_END'],
            [
                'type' => 'text',
                'body' => "Thanks! Your offer has been sent. We'll let you know if the client selects you.",
                'sort' => 30,
            ]
        );

        /**
         * ===========================
         * Unregistered
         * ===========================
         */
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

        /**
         * ===========================
         * Rating Flow
         * ===========================
         */
        $rating = WaFlow::updateOrCreate(
            ['code' => 'rating_flow'],
            ['name' => 'Rating Flow', 'entry_keyword' => 'any', 'target_role' => 'client', 'is_active' => true]
        );

        WaNode::updateOrCreate(
            ['flow_id' => $rating->id, 'code' => 'R1'],
            [
                'type' => 'text',
                'body' => 'Please reply with a number from 1 to 5.',
                'sort' => 10,
            ]
        );
    }
}
