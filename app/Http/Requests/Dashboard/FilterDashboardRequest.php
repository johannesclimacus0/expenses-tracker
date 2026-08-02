<?php

namespace App\Http\Requests\Dashboard;

use App\Enums\DashboardPeriod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterDashboardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'period' => ['nullable', Rule::enum(DashboardPeriod::class)],
            'month' => 'nullable|date_format:Y-m',
        ];
    }
}
