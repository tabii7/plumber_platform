<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        $clients = User::where('role','client')->orderBy('created_at','desc')->paginate(20);
        return view('admin.clients.index', compact('clients'));
    }

    public function show(User $client)
    {
        abort_unless($client->role === 'client', 404);
        return view('admin.clients.show', compact('client'));
    }

    public function edit(User $client)
    {
        abort_unless($client->role === 'client', 404);
        return view('admin.clients.edit', compact('client'));
    }

    public function update(Request $request, User $client)
    {
        abort_unless($client->role === 'client', 404);
        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone'     => 'nullable|string|max:20',
            'whatsapp_number' => 'nullable|string|max:20',
            'postal_code' => 'nullable|string|max:10',
            'city'        => 'nullable|string|max:255',
        ]);
        $client->update($data);
        return back()->with('success','Client updated.');
    }

    public function destroy(User $client)
    {
        abort_unless($client->role === 'client', 404);
        $client->delete();
        return redirect()->route('clients.index')->with('success','Client deleted.');
    }
}
