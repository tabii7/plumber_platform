<?php

namespace App\Services;

use App\Models\User;
use App\Models\WaFlow;
use App\Models\WaNode;
use App\Models\WaSession;
use App\Models\WaLog;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class WaFlowEngine
{
    const SESSION_TTL_MIN = 240; // 4 hours of inactivity

    public function startOrResume(string $waNumber, ?string $role, string $message): string
    {
        // Try to resume
        $session = $this->getActiveSession($waNumber);

        if ($session) {
            return $this->progress($session, $message);
        }

        // No session: try to start by entry_keyword
        $keyword = trim(strtolower($message));
        $flow = WaFlow::query()
            ->where('is_active', true)
            ->when($role, fn($q) => $q->where('target_role', $role))
            ->where('entry_keyword', $keyword)
            ->first();

        if (!$flow) {
            // Not a start keyword → soft help
            return "Type 'info' if you're a client 📋 or 'plumber' if you're a plumber 🔧";
        }

        // Create session at the flow's first node (lowest sort) or a node with code 'start'
        $node = $flow->nodes()->orderBy('sort')->first();
        if (!$node) {
            return "⚠️ Flow has no nodes yet.";
        }

        $session = WaSession::create([
            'wa_number'      => $waNumber,
            'user_id'        => optional($this->findUserByWa($waNumber))->id,
            'flow_code'      => $flow->code,
            'node_code'      => $node->code,
            'context_json'   => ['started_at' => now()->toISOString()],
            'last_message_at'=> now(),
        ]);

        $out = $this->renderNode($node, $session);
        $this->log($waNumber, 'out', ['text' => $out], 'ok');

        return $out;
    }

    public function progress(WaSession $session, string $incomingText): string
    {
        $incomingText = trim($incomingText);
        $session->last_message_at = now();
        $session->save();

        $flow = WaFlow::where('code', $session->flow_code)->first();
        if (!$flow) {
            $this->endSession($session);
            return "Session expired. Type 'info' (client) or 'plumber' to start again.";
        }

        $node = $flow->nodes()->where('code', $session->node_code)->first();
        if (!$node) {
            $this->endSession($session);
            return "Session expired. Type 'info' (client) or 'plumber' to start again.";
        }

        // Decide next node based on current node type + user reply
        $nextCode = $this->resolveNextNodeCode($node, $incomingText, $session);

        // If node collects text, stash in context
        if ($node->type === 'collect_text') {
            $ctx = $session->context_json ?? [];
            $ctxKey = $node->code; // store by node code
            $ctx['collected'][$ctxKey] = $incomingText;
            $session->context_json = $ctx;
        }

        if (!$nextCode) {
            // Re-render same node with a gentle hint
            $hint = $this->hintForNode($node);
            $out = ($hint ?: "Please reply with a valid option.");
            $this->log($session->wa_number, 'out', ['text' => $out], 'ok');
            return $out;
        }

        $nextNode = $flow->nodes()->where('code', $nextCode)->first();
        if (!$nextNode) {
            $this->endSession($session);
            return "Thanks! Flow ended.";
        }

        // move session
        $session->node_code = $nextNode->code;
        $session->save();

        $out = $this->renderNode($nextNode, $session);
        $this->log($session->wa_number, 'out', ['text' => $out], 'ok');
        return $out;
    }

    protected function resolveNextNodeCode(WaNode $node, string $incomingText, WaSession $session): ?string
    {
        $map = $node->next_map_json ?: [];

        // normalize
        $t = strtolower(trim($incomingText));

        // direct match
        if (isset($map[$t])) {
            return $map[$t];
        }

        // common shorthands
        if (in_array($t, ['y','yes']) && isset($map['yes'])) return $map['yes'];
        if (in_array($t, ['n','no']) && isset($map['no']))   return $map['no'];

        // If node has options (buttons/list), map numbers (1..n) to options' ids/keys
        if (in_array($node->type, ['buttons','list'])) {
            $options = $node->options_json ?: [];
            if (ctype_digit($t)) {
                $idx = (int)$t - 1;
                if (isset($options[$idx])) {
                    $opt = $options[$idx];
                    // option id or label can map to next_map_json
                    $key = strtolower((string)($opt['id'] ?? $opt['label'] ?? ''));
                    if (isset($map[$key])) return $map[$key];
                    // fallback: if map uses index as key
                    if (isset($map[(string)$t])) return $map[(string)$t];
                }
            }

            // also allow matching by label/id typed back
            foreach ($options as $opt) {
                $id  = strtolower((string)($opt['id'] ?? ''));
                $lbl = strtolower((string)($opt['label'] ?? ''));
                if ($id && $id === $t && isset($map[$id])) return $map[$id];
                if ($lbl && $lbl === $t && isset($map[$lbl])) return $map[$lbl];
            }
        }

        // collect_text: if a 'next' is defined, use it
        if ($node->type === 'collect_text' && isset($map['next'])) {
            return $map['next'];
        }

        return null;
    }

    protected function renderNode(WaNode $node, WaSession $session): string
    {
        $lines = [];

        if ($node->title)  $lines[] = $node->title;
        if ($node->body)   $lines[] = $node->body;

        if (in_array($node->type, ['buttons','list'])) {
            $opts = $node->options_json ?: [];
            if (!empty($opts)) {
                $lines[] = ''; // spacer
                foreach ($opts as $i => $opt) {
                    $label = $opt['label'] ?? $opt['title'] ?? $opt['id'] ?? ('Option '.($i+1));
                    // Strip any existing numbers from the beginning of the label to prevent double numbering
                    $cleanLabel = preg_replace('/^\d+[\.\)]\s*/', '', $label);
                    $lines[] = ($i+1).". ".$cleanLabel;
                }
                $lines[] = '';
                $lines[] = "Reply with the number.";
            }
        }

        if ($node->footer) $lines[] = $node->footer;

        return implode("\n", array_filter($lines, fn($l) => $l !== null));
    }

    protected function hintForNode(WaNode $node): ?string
    {
        return match ($node->type) {
            'buttons','list'   => 'Please reply with the number of your choice.',
            'collect_text'     => 'Please type a short message.',
            default            => null,
        };
    }

    protected function getActiveSession(string $waNumber): ?WaSession
    {
        $cutoff = Carbon::now()->subMinutes(self::SESSION_TTL_MIN);
        return WaSession::where('wa_number', $waNumber)
            ->where('last_message_at', '>=', $cutoff)
            ->orderByDesc('last_message_at')
            ->first();
    }

    protected function endSession(WaSession $session): void
    {
        // simplest end is to just bump TTL and clear node
        $session->last_message_at = now()->subMinutes(self::SESSION_TTL_MIN + 1);
        $session->save();
    }

    protected function findUserByWa(string $waNumber): ?User
    {
        $digits = preg_replace('/\D+/', '', $waNumber);
        return User::where('whatsapp_number', $digits)
            ->orWhere('phone', $digits)
            ->first();
    }

    protected function log(string $wa, string $dir, array $payload, string $status = 'ok'): void
    {
        WaLog::create([
            'wa_number'    => preg_replace('/\D+/', '', $wa),
            'direction'    => $dir, // 'in' or 'out'
            'payload_json' => $payload,
            'status'       => $status,
        ]);
    }
}
