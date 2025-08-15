<?php

// app/Http/Controllers/PlumberCategoryController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Category;

class PlumberCategoryController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        abort_unless($user && $user->role === 'plumber', 403);

        $categories = Category::orderBy('group')->orderBy('sort')->get()
            ->groupBy('group'); // ['Algemeen' => [...], 'Probleemgericht' => [...]]

        $selected = $user->categories()->pluck('categories.id')->toArray();

        return view('plumber.categories.edit', compact('categories', 'selected'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        abort_unless($user && $user->role === 'plumber', 403);

        $data = $request->validate([
            'categories'   => 'array',
            'categories.*' => 'integer|exists:categories,id',
        ]);

        // sync selections (empty array = remove all)
        $user->categories()->sync($data['categories'] ?? []);

        return redirect()
            ->route('plumber.categories.edit')
            ->with('success', 'Categories updated successfully.');
    }
}

