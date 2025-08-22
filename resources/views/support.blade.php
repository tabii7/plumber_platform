@extends('layouts.app')

@section('content')
<div class="container mx-auto mt-10">
    <div class="bg-white p-6 rounded shadow">
        <h1 class="text-2xl font-bold text-gray-800 mb-4">Support</h1>
        
        <div class="space-y-4">
            <div class="border-l-4 border-blue-500 pl-4">
                <h2 class="text-lg font-semibold text-gray-700">Need Help?</h2>
                <p class="text-gray-600 mt-2">
                    If you're experiencing issues with our WhatsApp service, please contact us:
                </p>
            </div>
            
            <div class="bg-gray-50 p-4 rounded">
                <h3 class="font-semibold text-gray-700 mb-2">Contact Information</h3>
                <ul class="space-y-2 text-gray-600">
                    <li>📧 Email: support@loodgieter.app</li>
                    <li>📱 WhatsApp: +32 123 456 789</li>
                    <li>🕒 Hours: Monday - Friday, 9:00 AM - 6:00 PM</li>
                </ul>
            </div>
            
            <div class="bg-blue-50 p-4 rounded">
                <h3 class="font-semibold text-blue-700 mb-2">Common Issues</h3>
                <ul class="space-y-2 text-blue-600">
                    <li>• Can't send messages? Try restarting WhatsApp</li>
                    <li>• Not receiving responses? Check your internet connection</li>
                    <li>• Wrong address? Update your profile in the dashboard</li>
                    <li>• Payment issues? Contact our billing team</li>
                </ul>
            </div>
            
            <div class="text-center">
                <a href="{{ route('dashboard') }}" 
                   class="inline-flex items-center bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    ← Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
