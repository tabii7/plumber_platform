@extends('layouts.app')

@section('content')
<div class="container mx-auto mt-10">
    <div class="bg-white p-6 rounded shadow">
        <h1 class="text-2xl font-bold text-gray-800">Client Dashboard</h1>
        <p class="mt-3 text-gray-600">Welkom, {{ Auth::user()->full_name }}! Hier kun je loodgieters aanvragen en je profiel beheren.</p>
        
        @if(session('success'))
            <div class="mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                {{ session('success') }}
            </div>
        @endif
        
        <!-- Subscription Status Card -->
        <div class="mt-6 p-6 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Subscription Status</h3>
                    <p class="text-sm text-gray-600 mb-3">
                        @if(Auth::user()->subscription_status === 'active' && Auth::user()->subscription_ends_at)
                            <span class="text-green-600 font-medium">✅ Active</span> - {{ Auth::user()->subscription_plan }}
                        @else
                            <span class="text-red-600 font-medium">❌ Inactive</span> - No active subscription
                        @endif
                    </p>
                    
                    @if(Auth::user()->subscription_status === 'active' && Auth::user()->subscription_ends_at)
                        <div class="bg-white p-4 rounded-lg border border-green-200 mb-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-700">Package Expiry Date</p>
                                    <p class="text-lg font-bold text-green-600">
                                        {{ Auth::user()->subscription_ends_at->format('F j, Y') }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        @php
                                            $daysLeft = Auth::user()->subscription_ends_at->diffInDays(now());
                                        @endphp
                                        @if($daysLeft == 0)
                                            <span class="text-orange-600 font-medium">⚠️ Expires today!</span>
                                        @elseif($daysLeft <= 7)
                                            <span class="text-orange-600 font-medium">⚠️ Expires in {{ $daysLeft }} days</span>
                                        @elseif($daysLeft <= 30)
                                            <span class="text-blue-600 font-medium">ℹ️ Expires in {{ $daysLeft }} days</span>
                                        @else
                                            <span class="text-green-600 font-medium">✓ Valid for {{ $daysLeft }} more days</span>
                                        @endif
                                    </p>
                                </div>
                                <div class="text-right">
                                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
                                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <p class="text-xs text-gray-500">Get unlimited access to plumber services and priority support</p>
                </div>
                <div class="text-center ml-6">
                    @if(Auth::user()->subscription_status === 'active' && Auth::user()->subscription_ends_at)
                        <a href="{{ route('welcome') }}#pricing" 
                           class="inline-flex items-center bg-gradient-to-r from-green-600 to-emerald-600 text-white px-6 py-2 rounded-lg hover:from-green-700 hover:to-emerald-700 transition-all duration-200 font-semibold text-sm shadow-lg hover:shadow-xl">
                            <i class="fas fa-sync-alt mr-2"></i>
                            Renew Subscription
                        </a>
                        <p class="text-xs text-gray-500 mt-2">Extend your current plan</p>
                    @else
                        <a href="{{ route('welcome') }}#pricing" 
                           class="inline-flex items-center bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-8 py-3 rounded-lg hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 font-semibold text-lg shadow-lg hover:shadow-xl">
                            <i class="fas fa-crown mr-2"></i>
                            Subscribe Now
                        </a>
                        <p class="text-xs text-gray-500 mt-2">Starting from €19.99/month</p>
                    @endif
                </div>
            </div>
        </div>
        
        <ul class="mt-5 space-y-3">
            <li><a href="{{ route('requests.create') }}" class="text-blue-600 hover:underline">Nieuwe aanvraag indienen</a></li>
            <li><a href="{{ route('requests.index') }}" class="text-blue-600 hover:underline">Bekijk je aanvragen</a></li>
        </ul>
    </div>
</div>
@endsection
