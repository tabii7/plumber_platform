<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class RegistrationChoiceController extends Controller
{
    /**
     * Display the registration choice view.
     */
    public function show(): View
    {
        return view('auth.register-choice');
    }
}
