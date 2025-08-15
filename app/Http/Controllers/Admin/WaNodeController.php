<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WaFlow;
use App\Models\WaNode;
use Illuminate\Http\Request;

class WaNodeController extends Controller
{
    public function index(WaFlow $flow)
    {
        $nodes = $flow->nodes()->orderBy('sort')->get();
        return view('admin.nodes.index', compact('flow', 'nodes'));
    }

    public function create(WaFlow $flow)
    {
        $node = new WaNode(['type' => 'text', 'sort' => 10]);
        return view('admin.nodes.create', compact('flow', 'node'));
    }

    public function store(Request $request, WaFlow $flow)
    {
        $data = $this->validateData($request);

        // Build options_json from arrays
        $data['options_json'] = $this->buildOptions($request);
        // Build next_map_json (key -> node_code)
        $data['next_map_json'] = $this->buildNextMap($request);

        $data['flow_id'] = $flow->id;

        WaNode::create($data);

        return redirect()->route('admin.flows.nodes.index', $flow)->with('success', 'Node created.');
    }

    public function edit(WaFlow $flow, WaNode $node)
    {
        return view('admin.nodes.edit', compact('flow', 'node'));
    }

    public function update(Request $request, WaFlow $flow, WaNode $node)
    {
        $data = $this->validateData($request, $node->id);

        $data['options_json'] = $this->buildOptions($request);
        $data['next_map_json'] = $this->buildNextMap($request);

        $node->update($data);

        return redirect()->route('admin.flows.nodes.index', $flow)->with('success', 'Node updated.');
    }

    public function destroy(WaFlow $flow, WaNode $node)
    {
        $node->delete();
        return redirect()->route('admin.flows.nodes.index', $flow)->with('success', 'Node deleted.');
    }

    protected function validateData(Request $request, $id = null): array
    {
        $types = ['text', 'buttons', 'list', 'collect_text', 'dispatch'];

        return $request->validate([
            'code'   => 'required|string|max:255' . ($id ? '' : '|unique:wa_nodes,code'),
            'type'   => 'required|in:' . implode(',', $types),
            'title'  => 'nullable|string|max:255',
            'body'   => 'nullable|string',
            'footer' => 'nullable|string|max:255',
            'sort'   => 'nullable|integer|min:0',
        ]);
    }

    protected function buildOptions(Request $request): ?array
    {
        // options[id][], options[label][]
        $ids    = $request->input('options.id', []);
        $labels = $request->input('options.label', []);

        $out = [];
        foreach ($ids as $i => $id) {
            $id = trim((string) $id);
            $label = trim((string) ($labels[$i] ?? ''));
            if ($id !== '' || $label !== '') {
                $out[] = ['id' => $id ?: null, 'label' => $label ?: null];
            }
        }
        return empty($out) ? null : $out;
    }

    protected function buildNextMap(Request $request): ?array
    {
        // next_map[key] = value  (submitted as arrays keys[] / values[])
        $keys   = $request->input('next.keys', []);
        $values = $request->input('next.values', []);

        $map = [];
        foreach ($keys as $i => $k) {
            $k = trim((string) $k);
            $v = trim((string) ($values[$i] ?? ''));
            if ($k !== '' && $v !== '') {
                $map[$k] = $v;
            }
        }

        return empty($map) ? null : $map;
    }
}
