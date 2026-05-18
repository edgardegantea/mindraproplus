<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        $planId = $this->route('plan')?->id;

        return [
            'name' => 'required|string|max:255',
            'slug' => [
                'required',
                'string',
                'max:100',
                Rule::unique('plans', 'slug')->ignore($planId),
            ],
            'description' => 'nullable|string|max:1000',
            'price_cents' => 'required|integer|min:0',
            'currency' => 'required|string|size:3',
            'features' => 'nullable|array',
            'trial_days' => 'nullable|integer|min:0|max:365',
        ];
    }
}
