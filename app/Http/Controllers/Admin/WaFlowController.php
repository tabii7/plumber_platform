<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WaFlow;
use Illuminate\Http\Request;

class WaFlowController extends Controller
{
    public function index()
    {
        $flows = WaFlow::orderBy('name')->paginate(15);
        return view('admin.flows.index', compact('flows'));
    }

    public function create()
    {
        $flow = new WaFlow();
        return view('admin.flows.create', compact('flow'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'          => 'required|string|max:255|unique:wa_flows,code',
            'name'          => 'required|string|max:255',
            'entry_keyword' => 'nullable|string|max:255',
            'target_role'   => 'nullable|in:client,plumber,admin',
            'is_active'     => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        WaFlow::create($data);

        return redirect()->route('admin.flows.index')->with('success', 'Flow created.');
    }

    public function edit(WaFlow $flow)
    {
        return view('admin.flows.edit', compact('flow'));
    }

    public function update(Request $request, WaFlow $flow)
    {
        $data = $request->validate([
            'code'          => 'required|string|max:255|unique:wa_flows,code,' . $flow->id,
            'name'          => 'required|string|max:255',
            'entry_keyword' => 'nullable|string|max:255',
            'target_role'   => 'nullable|in:client,plumber,admin',
            'is_active'     => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $flow->update($data);

        return redirect()->route('admin.flows.index')->with('success', 'Flow updated.');
    }

    public function destroy(WaFlow $flow)
    {
        $flow->delete();
        return redirect()->route('admin.flows.index')->with('success', 'Flow deleted.');
    }
}
