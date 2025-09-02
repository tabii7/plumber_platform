<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */

     // app/Http/Requests/ProfileUpdateRequest.php
public function rules(): array
{
    return [
        'full_name' => ['required','string','max:255'],
        'email' => ['required','email','max:255', Rule::unique('users','email')->ignore($this->user()->id)],
        'whatsapp_number' => ['required','string','max:32'],
        'address' => ['required','string','max:255'],
        'number' => ['nullable','string','max:50'],
        'postal_code' => ['nullable','string','max:20'],
        'city' => ['nullable','string','max:120'],
        'address_json' => ['nullable','string'],
    ];
}

    
}
