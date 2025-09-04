@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">Dark Mode Test Page</h1>
    
    <div class="bg-white dark:bg-slate-800 p-6 rounded-lg shadow-lg border border-gray-200 dark:border-slate-700">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Test the Dark Mode Toggle</h2>
        
        <div class="space-y-4">
            <p class="text-gray-600 dark:text-gray-400">
                This is a test page to verify the dark mode toggle is working correctly.
            </p>
            
            <div class="flex items-center space-x-4">
                <span class="text-gray-700 dark:text-gray-300">Toggle should be in the navigation bar above:</span>
                <x-dark-mode-toggle />
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                <div class="bg-blue-100 dark:bg-blue-900/30 p-4 rounded-lg">
                    <h3 class="font-semibold text-blue-800 dark:text-blue-200">Light Mode Card</h3>
                    <p class="text-blue-600 dark:text-blue-300">This card should look different in dark mode.</p>
                </div>
                
                <div class="bg-green-100 dark:bg-green-900/30 p-4 rounded-lg">
                    <h3 class="font-semibold text-green-800 dark:text-green-200">Another Card</h3>
                    <p class="text-green-600 dark:text-green-300">Colors should adapt to the current theme.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
